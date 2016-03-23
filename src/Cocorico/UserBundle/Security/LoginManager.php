<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Security;


use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Model\UserManager;
use FOS\UserBundle\Security\LoginManager as BaseLoginManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class LoginManager
{

    private $userManager;
    private $loginManager;
    private $firewallName;
    private $encoderFactory;

    /**
     * @param UserManager      $userManager
     * @param BaseLoginManager $loginManager
     * @param EncoderFactory   $encoderFactory
     * @param                  $firewallName
     */
    public function __construct(
        UserManager $userManager,
        BaseLoginManager $loginManager,
        EncoderFactory $encoderFactory,
        $firewallName
    ) {

        $this->userManager = $userManager;
        $this->loginManager = $loginManager;
        $this->encoderFactory = $encoderFactory;
        $this->firewallName = $firewallName;
    }

    /**
     * Login user
     *
     * @param $username
     * @param $password
     * @return bool|User
     */
    public function loginUser($username, $password)
    {
        try {
            /** @var  $user User */
            $user = $this->userManager->findUserBy(array('username' => $username));
            if ($user) {
                /** @var PasswordEncoderInterface $encoder */
                $encoder = $this->encoderFactory->getEncoder($user);
                $passwordIsValid = $encoder->isPasswordValid(
                    $user->getPassword(),
                    $password,
                    $user->getSalt()
                );

                if ($passwordIsValid) {
                    $this->loginManager->loginUser($this->firewallName, $user);

                    return $user;
                }
            }
        } catch (AccountStatusException $ex) {
            // We simply do not authenticate users which do not pass the user
            // checker (not enabled, expired, etc.).
        }

        return false;
    }

    /**
     * @return BaseLoginManager
     */
    public function getLoginManager()
    {
        return $this->loginManager;
    }


    /**
     * @return mixed
     */
    public function getFirewallName()
    {
        return $this->firewallName;
    }


}