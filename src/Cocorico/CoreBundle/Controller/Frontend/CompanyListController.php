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
use Cocorico\CoreBundle\Utils\Deps;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Ods;
use Cocorico\CoreBundle\Model\DirectorySearchRequest;

/**
 * Directory controller
 *
 *  @Route("/directory/siae")
 */
class CompanyListController extends Controller
{
    private $tracker;
    private $deps;

    private function fix()
    {
        // FIXME: Find a symfonian way to do this
        if ($this->tracker === null) {
            $this->tracker = new Tracker($_SERVER['ITOU_ENV'], "test");
            $this->deps = new Deps();
        }
    }

    /**
     * List companies in Database
     *
     * @Route("/{page}", name="cocorico_itou_siae_directory", defaults={"page"=1})
     * @Security("has_role('ROLE_USER')")
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
        $form = $this->sortCompaniesForm();
        $form->handleRequest($request);

        /** @var ListingSearchRequest $listingSearchRequest */
        $directorySearchRequest = $this->get('cocorico.directory_search_request');

        $dlform = $this->csvCompaniesForm($directorySearchRequest);

        $directoryManager = $this->get('cocorico.directory.manager');
        $withAntenna = true;

        if ($form->isSubmitted() && $form->isValid()) {
            $sort = $form->getData();
            // Hack, see model ListingSearchRequest for more
            $sectors = $request->query->get('sector');
            $sort['sector'] = $sectors;

            $params = [
                'type' => $sort['structureType'],
                'sector' => $sort['sector'],
                'prestaType' => $sort['prestaType'],
                'withAntenna' => $sort['withAntenna'],
                'postalCode' => null,
                'region' => null,
            ];
            $withAntenna = $sort['withAntenna'];
            $params = $this->fixParams($sort, $params);
            $entries = $directoryManager->findByForm($page, $params);
            $this->tracker->track('backend', 'directory_search', array_merge($params, $tracker_payload), $request->getSession());

            // Set download form data
            foreach (['structureType', 'withAntenna', 'postalCode', 'prestaType', 'area', 'city', 'department', 'zip'] as $key) {
                $dlform->get($key)->setData($sort[$key]);
            }
            // Hack
            $dlform->get('serialSectors')->setData(implode('|', $sectors));
            $dlform->get('postalCode')->setData($params['postalCode']);
            $dlform->get('region')->setData($params['region']);

        } else {
            $entries = $directoryManager->listSome($page);
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
            // 'csv_route' => 'cocorico_itou_siae_directory_csv',
            // 'csv_params' => $request->query->all(),
        ]);
    }

    private function fixParams($data, $params)
    {
        $isZip = $data['zip'] != null;
        $isCity = $data['city'] != null && $data['postalCode'] != null;
        $isDep = $data['department'] != null;
        $isReg = $data['area'] != null;

        switch(true) {
            case $isZip:
                $params['postalCode'] = $data['zip'];
                break;
            case $isCity:
                $needle = intval($data['postalCode']);
                switch (true) {
                    // Lyon
                    case $needle >= 69001 and $needle <= 69009:
                        $params['postalCode'] = '6900';
                        break;
                    // Marseille
                    case $needle >= 13001 and $needle <= 13016:
                        $params['postalCode'] = '130';
                        break;
                    // Marseille
                    case $needle >= 75000 and $needle <= 75680:
                        $params['postalCode'] = '75';
                        break;
                    default:
                        $params['postalCode'] = substr($data['postalCode'], 0, 4);
                }
                break;
            case $isDep:
                $depNum = $this->deps->byName($data['department']);
                if ($depNum) {
                    $params['postalCode'] = $depNum;
                } else {
                    $params['postalCode'] = substr($data['postalCode'], 0, 2);
                }
                break;
            case $isReg:
                $region_idx = array_search($data['area'], Directory::$regions); 
                $region_idx = $region_idx ? $region_idx : 0;
                $params['region'] = $region_idx;
                break;
            default:
                break;
        }
        //dump($data);
        //dump($params);

        // Special Rules
        if ($data['zip'] == '84800') {
            // Google maps mistakes Fontaine-de-vaucluse for Vaucluse (84)
            // or Vaucluse (Doubs)
            $params['postalCode'] = '84';
        }

        return $params;
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
            $params = [
                'type' => $sort->getStructureType(),
                'sector' => $sort->getSectors(),
                'prestaType' => $sort->getPrestaType(),
                'withAntenna' => $sort->getWithAntenna(),
                'postalCode' => $sort->getPostalCode(),
                'region' => $sort->getRegion(),
                'format' => $sort->getFormat(),
            ];
            // $params = $this->fixParams($sort, $params);
            $this->tracker->track('backend', 'directory_csv', array_merge($params, $tracker_payload), $request->getSession());

            $entries = $directoryManager->listByForm($params);
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

    private function sortCompaniesForm()
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            DirectoryFilterType::class,
            null,
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

}
?>
