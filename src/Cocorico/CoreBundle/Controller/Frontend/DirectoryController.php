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

/**
 * Directory controller.
 *
 * @Route("/directory")
 */
class DirectoryController extends Controller
{

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

        return $this->render(
            'CocoricoCoreBundle:Frontend/Directory:show.html.twig',
            array(
                'structure' => $structure,
            )
        );
    }


}
