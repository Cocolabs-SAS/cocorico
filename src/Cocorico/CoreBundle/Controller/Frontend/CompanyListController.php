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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Cocorico\CoreBundle\Utils\Tracker;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Ods;

/**
 * Directory controller
 *
 *  @Route("/directory/siae")
 */
class CompanyListController extends Controller
{
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
        $tracker = new Tracker($_SERVER['ITOU_ENV'], "test");
        $tracker_payload = ['dir' => 'siae'];
        $form = $this->sortCompaniesForm();
        $form->handleRequest($request);
        $dlform = $this->csvCompaniesForm();

        $directoryManager = $this->get('cocorico.directory.manager');

        if ($form->isSubmitted() && $form->isValid()) {
            $sort = $form->getData();
            $params = [
                'type' => $sort['structureType'],
                'sector' => $sort['sector'],
                'prestaType' => $sort['prestaType'],
                'postalCode' => null,
                'region' => null,
            ];
            $params = $this->fixParams($sort, $params);
            $entries = $directoryManager->findByForm($page, $params);
            $tracker->track('backend', 'directory_search', array_merge($params, $tracker_payload), $request->getSession());

            // Set download form data
            foreach (['structureType', 'sector', 'postalCode', 'prestaType', 'area', 'city', 'department'] as $key) {
                $dlform->get($key)->setData($sort[$key]);
            }
        } else {
            $entries = $directoryManager->listSome($page);
            $tracker->track('backend', 'directory_list', $tracker_payload, $request->getSession());
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
            // 'csv_route' => 'cocorico_itou_siae_directory_csv',
            // 'csv_params' => $request->query->all(),
        ]);
    }

    private function fixParams($data, $params)
    {
        $isCity = $data['city'] != null;
        $isDep = $data['department'] != null;
        $isReg = $data['area'] != null;

        switch(true) {
            case $isCity:
                $params['postalCode'] = $data['postalCode'];
                break;
            case $isDep:
                $params['postalCode'] = substr($data['postalCode'], 0, 2);
                break;
            case $isReg:
                $region_idx = array_search($data['area'], Directory::$regions); 
                $region_idx = $region_idx ? $region_idx : 0;
                $params['region'] = $region_idx;
                break;
            default:
                break;
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
        $tracker = new Tracker($_SERVER['ITOU_ENV'], "test");
        $tracker_payload = ['dir' => 'siae'];
        $form = $this->csvCompaniesForm();
        $form->handleRequest($request);

        $directoryManager = $this->get('cocorico.directory.manager');

        if ($form->isSubmitted() && $form->isValid()) {
            $sort = $form->getData();
            $params = [
                'type' => $sort['structureType'],
                'sector' => $sort['sector'],
                'prestaType' => $sort['prestaType'],
                'postalCode' => null,
                'region' => null,
                'format' => $form['format']->getData(),
            ];
            $params = $this->fixParams($sort, $params);
            $tracker->track('backend', 'directory_csv', array_merge($params, $tracker_payload), $request->getSession());

            $entries = $directoryManager->listByForm($params);
        } else {
            $entries = $directoryManager->listbyForm();
        }
        $format = $form['format']->getData();

        // Write to csv
        $tmp_csv = tempnam("/tmp", "SIAE_CSV");
        $fp = fopen($tmp_csv, 'w');
        fputcsv($fp, array_values(Directory::$exportColumns));
        foreach ($entries as $fields) {
            $el = [];
            foreach (Directory::$exportColumns as $key => $value) {
                $el[$value] = $fields[$key];
            }
            fputcsv($fp, $el);
        }
        fclose($fp);


        // Respond according to preferred format
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
                $response->headers->set('Content-Disposition', 'attachment; filename="liste_prestataires.xlsx"');

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
                $response->headers->set('Content-Disposition', 'attachment; filename="liste_prestataires.ods"');

                unset($spreadsheet);
                unset($tmpf);
                break;
            case 'csv':
            default:
                $response = new Response(file_get_contents($tmp_csv));
                $response->headers->set('Content-Type', 'text/csv');
                $response->headers->set('Content-Disposition', 'attachment; filename="liste_prestataires.csv"');
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

    private function csvCompaniesForm()
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            DirectoryFilterType::class,
            null,
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
