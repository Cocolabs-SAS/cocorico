<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Controller\Frontend;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Entity\Directory;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller managing the user profile
 *
 * @Route("/user")
 */
class ProfileController extends Controller
{
    /**
     * Show user profile
     *
     * @Route("/{id}/show", name="cocorico_user_profile_show", requirements={
     *      "id" = "\d+"
     * })
     * @Method("GET")
     * @ParamConverter("user", class="CocoricoUserBundle:User")
     *
     * @param  Request $request
     * @param  User    $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */

    public function showAction(Request $request, User $user)
    {
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $userListings = $this->get('doctrine')->getManager()->getRepository('CocoricoCoreBundle:Listing')->findByOwner(
            $user->getId(),
            $request->getLocale(),
            array(Listing::STATUS_PUBLISHED)
        );

        $listingsData = $this->getListingsData($userListings);
        dump($listingsData);

        $C1Company = $this->get('doctrine')->getManager()->getRepository('CocoricoCoreBundle:Directory')->findByUser(
            $user->getId()
        );

        //Breadcrumbs
        $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
        $breadcrumbs->addProfileShowItems($request, $user);

        return $this->render(
            'CocoricoUserBundle:Frontend/Profile:show.html.twig',
            array(
                'user' => $user,
                'user_listings' => $userListings,
                'company' => $C1Company,
                'listings_data' => $listingsData,
            )
        );
    }

    private Function getListingsData($listings)
    {
        $data = [
            'prestaTypes' => [],
            'clientImages' => [],
            'website' => False,
        ];
        foreach ($listings as $l) {
            if (! in_array($l->getPrestaTypeText(), $data['prestaTypes'])) {
                $data['prestaTypes'][] = $l->getPrestaTypeText();
            }
            foreach ($l->getClientImages() as $img)
            {
                $data['clientImages'][] = $img; 
            }
            // if ($l->getWebsite())
            // {
            //     $data['website'] = $l->getWebsite();
            // }
        }
        return $data;
    
    }
}
