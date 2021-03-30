<?php

namespace Cocorico\CoreBundle\Controller\Dashboard\Offerer;

use Cocorico\CoreBundle\Entity\Directory;
use Cocorico\CoreBundle\Form\Type\Dashboard\DirectoryEditCategoriesAjaxType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Directory Dashboard category controller.
 *
 * @Route("/directory")
 */
class DirectoryCategoriesAjaxController extends Controller
{
    /**
     * @param  Directory $structure
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoriesAjaxFormAction($structure)
    {
        $form = $this->createCategoriesAjaxForm($structure);

        return $this->render(
            '@CocoricoCore/Dashboard/Directory/form_categories_ajax.html.twig',
            array(
                'form' => $form->createView(),
                'directory' => $structure
            )
        );
    }

    /**
     * @param Directory $directory
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createCategoriesAjaxForm(Directory $directory)
    {
        $form = $this->get('form.factory')->createNamed(
            'directory_categories',
            DirectoryEditCategoriesAjaxType::class,
            $directory,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_directory_edit_categories_ajax',
                    array('id' => $directory->getId())
                ),
            )
        );

        return $form;
    }

    /**
     * Edit Directory categories.
     *
     * @Route("/{id}/edit_categories_ajax", name="cocorico_dashboard_directory_edit_categories_ajax", requirements={"id" = "\d+"})
     * @Security("is_granted('edit', structure)")
     * @ParamConverter("structure", class="CocoricoCoreBundle:Directory")
     *
     * @Method({"POST", "GET"})
     *
     * @param Request $request
     * @param         $structure
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editCategoriesAjaxAction(Request $request, Directory $structure)
    {
        $form = $this->createCategoriesAjaxForm($structure);
        $form->handleRequest($request);

        $formIsValid = $form->isSubmitted() && $form->isValid();
        if ($formIsValid) {
            $structure = $this->get("cocorico.directory.manager")->save($structure);

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('directory.edit.success', array(), 'cocorico_directory')
            );

            return $this->redirectToRoute(
                'cocorico_dashboard_directory_edit_categories_ajax',
                array('id' => $structure->getId())
            );
        }

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Directory:form_categories_ajax.html.twig',
            array(
                'structure' => $structure,
                'form' => $form->createView()
            )
        );
    }

}
