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

use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ChangePasswordController
 *
 */
class ChangePasswordController extends ContainerAware
{
    /**
     * Change password
     *
     * @Route("/change-password", name="cocorico_user_change_password")
     * @Method({"GET", "POST"})
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */
    public function changePasswordAction()
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->container->get('fos_user.change_password.form');
        $formHandler = $this->container->get('fos_user.change_password.form.handler');

        $process = $formHandler->process($user);
        if ($process) {
            $this->container->get('session')->getFlashBag()->add(
                'success',
                $this->container->get('translator')->trans('user.change_password.success', array(), 'cocorico_user')
            );

            $url = $this->container->get('router')->generate('cocorico_user_change_password');

            return new RedirectResponse($url);
        }

        return $this->container->get('templating')->renderResponse(
            '@CocoricoUser/Frontend/ChangePassword/changePassword.html.twig',
            array('form' => $form->createView())
        );
    }


}
