<?php

namespace Cocorico\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Utils\SIAE;
use Cocorico\CoreBundle\Entity\DirectorySort;
use Cocorico\CoreBundle\Entity\Directory;
use Cocorico\CoreBundle\Form\Type\Frontend\DirectoryFilterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Cocorico\CoreBundle\Utils\Tracker;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Ods;
use Cocorico\CoreBundle\Model\DirectorySearchRequest;
use Cocorico\CoreBundle\Entity\ListingImage;

/**
 * Directory controller
 *
 *  @Route("/directory/siae")
 */
class CompanyListController extends Controller
{
    private $tracker;

    private function fix()
    {
        // FIXME: Find a symfonian way to do this
        if ($this->tracker === null) {
            $this->tracker = new Tracker($_SERVER['ITOU_ENV'], "test");
        }
    }

    /**
     * List companies in Database
     *
     * @Route("/{page}", name="cocorico_itou_siae_directory", defaults={"page"=1})
     * @Method("GET")
     *
     * @param Request $request
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request, $page)
    {
        $this->fix();
        $tracker_payload = ['dir' => 'siae'];

        /** @var ListingSearchRequest $listingSearchRequest */
        $directorySearchRequest = $this->get('cocorico.directory_search_request');

        $form = $this->sortCompaniesForm($directorySearchRequest);
        $form->handleRequest($request);

        $dlform = $this->csvCompaniesForm($directorySearchRequest);

        $directoryManager = $this->get('cocorico.directory.manager');
        $withAntenna = false;
        $withRange = false;

        $markers = array('directoryIds' => array(), 'markers' => array());

        if ($form->isSubmitted() && $form->isValid()) {
            $sort = $form->getData();
            $sort->prepareData();

            $withAntenna = $sort->getWithAntenna();
            $withRange = $sort->getWithRange();
            $entries = $directoryManager->findByForm($sort, $page, $sort->getLegacyParams());
            // dump($sort->getLegacyParams());

            $this->tracker->track('backend', 'directory_search', array_merge($sort->getLegacyParams(), $tracker_payload), $request->getSession());

            // Set download form data
            foreach (['serialSectors', 'structureType', 'withAntenna', 'postalCode', 'prestaType', 'area', 'city', 'department', 'zip', 'region'] as $key) {
                $dlform->get($key)->setData($sort->getKeyValue($key));
            }
            // Hack, weird PHP behaviour
            $dlform->get('serialSectors')->setData(implode('|', $sort->getSectors()));

            // Markers
            $structures = $entries->getIterator();
            $markers = $this->getMarkers($request, $entries, $structures);

        } else {
            $entries = $directoryManager->listSome($page);
            $structures = $entries->getIterator();
            $markers = $this->getMarkers($request, $entries, $structures);
            $this->tracker->track('backend', 'directory_list', $tracker_payload, $request->getSession());
        }
        return $this->render(
            'CocoricoCoreBundle:Frontend\Directory:dir_siae.html.twig', [
            'form' => $form->createView(),
            'dlform' => $dlform->createView(),
            'entries' => $entries,
            'pagination' => array(
                'page'  => $page,
                'pages_count' => ceil($entries->count() / $directoryManager->maxPerPage),
                'route' => $request->get('_route'),
                'route_params' => $request->query->all()
            ),
            'columns' => $directoryManager->listColumns(),
            'withAntenna' => $withAntenna,
            'withRange' => $withRange,
            'markers' => $markers['markers'],
            'request' => $directorySearchRequest,
            // 'csv_route' => 'cocorico_itou_siae_directory_csv',
            // 'csv_params' => $request->query->all(),
        ]);
    }

    /**
     * List companies in Database
     *
     * @Route("/export/csv", name="cocorico_itou_siae_directory_csv")
     * @Security("has_role('ROLE_USER')")
     * @Method("GET")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listCsv(Request $request)
    {
        $this->fix();
        $tracker_payload = ['dir' => 'siae'];

        /** @var ListingSearchRequest $listingSearchRequest */
        $directorySearchRequest = $this->get('cocorico.directory_search_request');
        $form = $this->csvCompaniesForm($directorySearchRequest);

        $form->handleRequest($request);

        $directoryManager = $this->get('cocorico.directory.manager');

        if ($form->isSubmitted() && $form->isValid()) {
            $sort = $form->getData();
            $sort->prepareData();
            $this->tracker->track('backend', 'directory_csv', array_merge($sort->getLegacyParams(), $tracker_payload), $request->getSession());
            $entries = $directoryManager->listByForm($sort->getLegacyParams());
            //dump($sort->getLegacyParams());
        } else {
            $entries = $directoryManager->listbyForm();
        }
        $format = $form['format']->getData();

        // Write to csv
        $tmp_csv = tempnam("/tmp", "SIAE_CSV");
        $fp = fopen($tmp_csv, 'w');
        fputcsv($fp, array_values(Directory::$exportColumns));
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($entries as $fields) {
            $el = [];
            foreach (Directory::$exportColumns as $key => $value) {
                $el[$value] = $accessor->getValue($fields, $key);
            }
            fputcsv($fp, $el);
        }
        fclose($fp);


        // Respond according to preferred format
        $date = strftime("%Y%b%d");
        $fname = "liste_prestataires_$date";
        switch($format) {
            case 'xlsx':
                $tmpf = tempnam("/tmp", "SIAE_XLSX");
                $spreadsheet = new Spreadsheet();
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setSheetIndex(0);
                $spreadsheet = $reader->load($tmp_csv);
                $writer = new Xlsx($spreadsheet);
                $writer->save($tmpf);
                $spreadsheet->disconnectWorksheets();

                $response = new Response(file_get_contents($tmpf));
                $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                $response->headers->set('Content-Disposition', 'attachment; filename="'.$fname.'.xlsx"');

                unset($spreadsheet);
                unset($tmpf);
                break;
            case 'ods':
                $tmpf = tempnam("/tmp", "SIAE_ODS");
                $spreadsheet = new Spreadsheet();
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setSheetIndex(0);
                $spreadsheet = $reader->load($tmp_csv);
                $writer = new Ods($spreadsheet);
                $writer->save($tmpf);
                $spreadsheet->disconnectWorksheets();

                $response = new Response(file_get_contents($tmpf));
                $response->headers->set('Content-Type', 'application/vnd.oasis.opendocument.spreadsheet');
                $response->headers->set('Content-Disposition', 'attachment; filename="'.$fname.'.ods"');

                unset($spreadsheet);
                unset($tmpf);
                break;
            case 'csv':
            default:
                $response = new Response(file_get_contents($tmp_csv));
                $response->headers->set('Content-Type', 'text/csv');
                $response->headers->set('Content-Disposition', 'attachment; filename="'.$fname.'.csv"');
                break;
        }
        unlink($tmp_csv);
        return $response;
    }

    private function sortCompaniesForm(DirectorySearchRequest $directorySearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            DirectoryFilterType::class,
            $directorySearchRequest,
            array(
                'action' => $this->generateUrl(
                    'cocorico_itou_siae_directory',
                    array('page' => 1)
                ),
                'method' => 'GET',
            )
        );

        //$form = $this->createFormBuilder($sort)
        //    ->add('sector', TextType::class)
        //    ->add('postalCode', TextType::class)
        //    ->add('structureType', TextType::class)
        //    ->add('prestaType', TextType::class)
        //    // ->add('save', SubmitType::class, ['label' => 'Filtrer'])
        //    ->getForm();

        return $form;
    }

    private function csvCompaniesForm(DirectorySearchRequest $directorySearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            DirectoryFilterType::class,
            $directorySearchRequest,
            array(
                'action' => $this->generateUrl(
                    'cocorico_itou_siae_directory_csv',
                    array('page' => 1)
                ),
                'method' => 'GET',
            )
        );

        //$form = $this->createFormBuilder($sort)
        //    ->add('sector', TextType::class)
        //    ->add('postalCode', TextType::class)
        //    ->add('structureType', TextType::class)
        //    ->add('prestaType', TextType::class)
        //    // ->add('save', SubmitType::class, ['label' => 'Filtrer'])
        //    ->getForm();

        return $form;
    }

    /**
     * Get Markers
     *
     * @param  Request        $request
     * @param  Paginator      $results
     * @param  \ArrayIterator $resultsIterator
     *
     * @return array
     *          array['markers'] markers data
     *          array['listingsIds'] listings ids
     */
    protected function getMarkers(Request $request, $results, $resultsIterator)
    {
        //We get directory id of current page to change their marker aspect on the map
        $resultsInPage = array();
        foreach ($resultsIterator as $i => $result) {
            dump($result);
            $resultsInPage[] = $result['id'];
        }

        //We need to display all directories (without pagination) of the current search on the map
        // $results->getQuery()->setFirstResult(null);
        // //$results->getQuery()->setMaxResults(null);
        // $results->getQuery()->setMaxResults(12);
        // $nbResults = $results->count();

        $imagePath = ListingImage::IMAGE_FOLDER;
        $locale = $request->getLocale();
        $liipCacheManager = $this->get('liip_imagine.cache.manager');
        $markers = $structuresIds = array();

        foreach ($resultsIterator as $i => $result) {
            $structure = $result;
            if ($structure['latitude'] == null) { continue; }
            $structuresIds[] = $structure['id'];

            $imageName = count($structure['images']) ? $structure['images'][0]['name'] : ListingImage::IMAGE_DEFAULT;

            $image = $liipCacheManager->getBrowserPath($imagePath . $imageName, 'listing_xsmall', array());


            $categories = count($structure['directoryListingCategories']) ?
                $structure['directoryListingCategories'][0]['category']['translations'][$locale]['name'] : '';

            $isInCurrentPage = in_array($structure['id'], $resultsInPage);


            //Allow to group markers with same location
            $locIndex = $structure['latitude'] . "-" . $structure['longitude'];
            $title = $structure['brand'] ? $structure['brand'] : $structure['name'];
            $markers[$locIndex][] = array(
                'id' => $structure['id'],
                'lat' => $structure['latitude'],
                'lng' => $structure['longitude'],
                'title' => $title,
                'category' => $categories,
                'image' => $image,
                'url' => $url = $this->generateUrl(
                    'cocorico_directory_show',
                    array('id' => $structure['id'])
                ),
                // 'zindex' => $isInCurrentPage ? 2 * $nbResults - $i : $i,
                'opacity' => $isInCurrentPage ? 1 : 0.4,

            );
        }

        return array(
            'markers' => $markers,
            'directoryIds' => $structuresIds
        );
    }



}
?>
