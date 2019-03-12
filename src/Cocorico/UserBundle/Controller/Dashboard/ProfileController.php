<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\UserBundle\Controller\Dashboard;

use Cocorico\UserBundle\Form\Type\ProfileSwitchFormType;
use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ProfileController
 *
 * @Route("/user")
 */
class ProfileController extends Controller
{
    /**
     * Switch profile
     *
     * @Route("/profile-switch", name="cocorico_user_dashboard_profile_switch")
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException|RuntimeException
     */
    public function profileSwitchAction(Request $request)
    {
        $session = $request->getSession();
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createProfileSwitchForm($request);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $session->set('profile', $data['profile']);

            $response = $this->redirect($this->generateUrl('cocorico_dashboard_message'));

            $response->headers->setCookie(new Cookie('userType', $data['profile'], 0, '/', null, false, false));

            return $response;
        }

        $type = $request->getSession()->get('profile', 'asker');
        $nbMessages = $this->get('doctrine')->getManager()->getRepository(
            'CocoricoMessageBundle:Message'
        )->getNbUnreadMessage($user, $type);

        return $this->render(
            'CocoricoUserBundle:Dashboard/Profile:profile_switch.html.twig',
            array(
                'form' => $form->createView(),
                'nbMessages' => $nbMessages,
                'type' => $type,
            )
        );
    }

    /**
     * Creates a form to switch a user profile.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createProfileSwitchForm(Request $request)
    {
        $session = $request->getSession();
        if ($session->has('profile')) {
            $selectedProfile = $session->get('profile');
        } else {
            $user = $this->getUser();
            $selectedProfile = ($user && $user->getListings()->count()) ? 'offerer' : 'asker';
            $session->set('profile', $selectedProfile);
        }

        $form = $this->get('form.factory')->createNamed(
            'profileSwitch',
            ProfileSwitchFormType::class,
            array('profile' => $selectedProfile),
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_user_dashboard_profile_switch'),
                'attr' => array('class' => 'form-switchers')
            )
        );

        return $form;
    }

}
