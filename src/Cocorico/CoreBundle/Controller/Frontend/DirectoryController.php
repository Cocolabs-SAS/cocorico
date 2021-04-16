<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Entity\Directory;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Model\DirectoryCheckRequest;
use Cocorico\CoreBundle\Form\Type\Frontend\DirectoryCheckType;
use Cocorico\CoreBundle\Form\Type\Frontend\DirectoryType;
# use Cocorico\CoreBundle\Form\Type\Dashboard\ListingEditDurationType;
# use Cocorico\CoreBundle\Form\Type\Dashboard\ListingEditPriceType;
# use Cocorico\CoreBundle\Form\Type\Dashboard\ListingEditStatusType;
use Cocorico\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Cocorico\CoreBundle\Utils\Tracker;

/**
 * Directory controller.
 *
 * @Route("/directory")
 */
class DirectoryController extends Controller
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
     * Search a directory entity to adopt
     *
     * @Route("/adopt/search", name="cocorico_directory_adopt_search")
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_USER')")
     *
     * @Method({"GET", "POST"})
     *
     * @param Request   $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction( Request $request) {
        $formHandler = $this->get('cocorico.form.handler.directory');
        $directoryCheckRequest = $this->get('cocorico.directory_check_request');


        $checkForm = $this->createCheckForm($directoryCheckRequest);
        $checkForm->handleRequest($request);
        $results = [];
        $searched = false;
        if ($checkForm->isSubmitted()) {
            $directoryCheckRequest = $checkForm->getData();
            $results = $this->get("cocorico.directory.manager")->findBySiretn(
                $directoryCheckRequest->getSiret()
            );
            $searched = true;
            $this->tracker->track('backend', 'adopt_search', ['q' => $directoryCheckRequest->getSiret()], $request->getSession());
        }
    
        return $this->render(
            'CocoricoCoreBundle:Frontend/Directory:search.html.twig',
            array(
                'directoryCheckRequest' => $directoryCheckRequest,
                'cform' => $checkForm->createView(),
                'results' => $results,
                'searched' => $searched,
                # 'editForm' => $editForm->createView(),
            )
        );
    
    }

    /**
     * Creates a form to check for a directory structure
     *
     * @param Directory $directory The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCheckForm(DirectoryCheckRequest $CRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            'directory',
            DirectoryCheckType::class,
            $CRequest,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_directory_adopt_search'),
            )
        );

        return $form;
    }

    /**
     * Adopt a directory entity.
     *
     * @Route("/adopt/{id}", name="cocorico_directory_adopt")
     *
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_USER')")
     *
     * @Method({"GET", "POST"})
     *
     * @param Request   $request
     * @param Directory $directory
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function adoptAction( Request $request, Directory $directory) {
        $formHandler = $this->get('cocorico.form.handler.directory');
        $form = $this->createDirectoryForm($directory);
        $directory = $formHandler->init($directory);
        $success = $formHandler->process($form);

        if ($success) {
            $url = $this->generateUrl('cocorico_directory_show', ['id' => $directory->getId()]);

            $this->get('session')->getFlashBag()->add(
                'success',
                'Structure attachéé avec succes'
            );

            $this->tracker->track('backend', 'adopt', ['dir' => $directory->getId()], $request->getSession());
            return $this->redirect($url);
        }

        // if ($checkForm->isSubmitted()) {
        //     $directoryCheckRequest = $checkForm->getData();
        //     $results = $this->get("cocorico.directory.manager")->findBySiretn(
        //         $directoryCheckRequest->getSiret()
        //     );
        // }
        //$success = $formHandler->process($form);
    
        return $this->render(
            'CocoricoCoreBundle:Frontend/Directory:adopt.html.twig',
            array(
                'directory' => $directory,
                'form' => $form->createView(),
                # 'editForm' => $editForm->createView(),
            )
        );
    }


    /**
     * Creates a form for a directory structure
     *
     * @param Directory $directory The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDirectoryForm(Directory $directory)
    {
        $form = $this->get('form.factory')->createNamed(
            'directory',
            DirectoryType::class,
            $directory,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_directory_adopt', ['id' => $directory->getId()]),
            )
        );

        return $form;
    }


    /**
     * Finds and displays a Directory entity.
     *
     * @Route("/{id}/show", name="cocorico_directory_show")
     * @Method("GET")
     *
     * @param Request $request
     * @param Directory $structure
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request, Directory $structure = null)
    {
        //Breadcrumbs
        // $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
        // $breadcrumbs->addListingShowItems($request, $listing);

        $listings = $this->getListings($structure, $request);
        return $this->render(
            'CocoricoCoreBundle:Frontend/Directory:show.html.twig',
            array(
                'structure' => $structure,
                'listings' => $listings,
                'user' => null,
            )
        );
    }

    private function getListings($structure, $request)
    {
        $listings = [];
        foreach($structure->getUsers() as $user)
        {
            $userListings = $this->get('doctrine')->getManager()->getRepository('CocoricoCoreBundle:Listing')->findByOwner(
                $user->getId(),
                $request->getLocale(),
                array(Listing::STATUS_PUBLISHED)
            );
            $listings = array_merge($listings, $userListings);
        
        }
        return $listings;
    }
}
