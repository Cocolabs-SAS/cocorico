<?php

namespace Cocorico\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Entity\DirectorySort;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyListController extends Controller
{
    /**
     * List companies in Database
     *
     * @Route("/directory/siae", name="cocorico_itou_siae_directory")
     * @Method({"POST", "GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        $sort = new DirectorySort();
        $form = $this->sortCompaniesForm($sort);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sort = $form->getData();

        }
        return $this->render(
            'CocoricoCoreBundle:Frontend\Directory:dir_siae.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function sortCompaniesForm($sort)
    {
        //$form = $this->get('form.factory')->createNamed(
        //    'dictory_siae',
        //    $sort,
        //    array(
        //        'method' => 'GET',
        //        'action' => $this->generateUrl(
        //            'cocorico_itou_siae_directory'
        //        ))
        //);

        $form = $this->createFormBuilder($sort)
            ->add('sector', TextType::class)
            ->add('postalCode', TextType::class)
            ->add('structureType', TextType::class)
            ->add('prestaType', TextType::class)
            // ->add('save', SubmitType::class, ['label' => 'Filtrer'])
            ->getForm();

        return $form;
    }

}
?>
