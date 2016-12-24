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
use Cocorico\CoreBundle\Repository\ListingRepository;
use Cocorico\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller managing the user profile
 *
 * @Route("/user")
 */
class ProfileController extends ContainerAware
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
     */

    public function showAction(Request $request, User $user)
    {
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $em = $this->container->get('doctrine')->getManager();
        /** @var ListingRepository $listingRepository */
        $listingRepository = $em->getRepository('CocoricoCoreBundle:Listing');
        $userListings = $listingRepository->findByOwner(
            $user->getId(),
            $this->container->get('request')->getLocale(),
            array(Listing::STATUS_PUBLISHED)
        );

        //Breadcrumbs
        $breadcrumbs = $this->container->get('cocorico.breadcrumbs_manager');
        $breadcrumbs->addProfileShowItems($request, $user);

        return $this->container->get('templating')->renderResponse(
            'CocoricoUserBundle:Frontend/Profile:show.html.twig',
            array(
                'user' => $user,
                'user_listings' => $userListings
            )
        );
    }


}
