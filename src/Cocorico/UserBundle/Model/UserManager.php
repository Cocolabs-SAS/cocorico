<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\UserBundle\Model;

use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Entity\UserFacebook;
use Cocorico\UserBundle\Entity\UserImage;
use Cocorico\UserBundle\Entity\UserTranslation;
use Cocorico\UserBundle\Event\UserEvent;
use Cocorico\UserBundle\Event\UserEvents;
use Cocorico\UserBundle\Repository\UserFacebookRepository;
use Cocorico\UserBundle\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class UserManager extends BaseUserManager implements UserManagerInterface
{
    protected $objectManager;
    protected $repository;
    protected $kernelRoot;
    protected $dispatcher;
    protected $timeUnitIsDay;
    protected $timeZone;

    /**
     * Constructor.
     *
     *
     * @param PasswordUpdaterInterface $passwordUpdater
     * @param CanonicalFieldsUpdater   $canonicalFieldsUpdater
     * @param ObjectManager            $objectManager
     * @param string                   $class
     * @param String                   $kernelRoot
     * @param EventDispatcherInterface $dispatcher
     * @param int                      $timeUnit
     * @param string                   $timeZone
     */
    public function __construct(
        PasswordUpdaterInterface $passwordUpdater,
        CanonicalFieldsUpdater $canonicalFieldsUpdater,
        ObjectManager $objectManager,
        $class,
        $kernelRoot,
        EventDispatcherInterface $dispatcher,
        $timeUnit,
        $timeZone
    ) {
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $objectManager, $class);

        $this->objectManager = $objectManager;
        $this->repository = $objectManager->getRepository($class);

        $this->kernelRoot = $kernelRoot;
        $this->dispatcher = $dispatcher;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
        $this->timeZone = $timeZone;
    }

    /**
     * {@inheritdoc}
     */
    public function createUser()
    {
        $user = parent::createUser();
        //Set user timezone to default app timezone
        if (!$this->timeUnitIsDay) {
            $user->setTimeZone($this->timeZone);
        }

        return $user;
    }

    /**
     * Updates a user.
     *
     * @param UserInterface|User $user
     * @param Boolean            $andFlush Whether to flush the changes (default true)
     *
     * @return User|UserInterface
     */
    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $this->updateCanonicalFields($user);
        $this->updatePassword($user);

        /** @var User $user */
        $user->mergeNewTranslations();
        $user->generateSlug();

        $this->persistAndFlush($user);

        /** @var UserTranslation $translation */
        foreach ($user->getTranslations() as $translation) {
            $this->objectManager->persist($translation);
        }
        $this->objectManager->flush();
        $this->objectManager->refresh($user);

        return $user;
    }

    /**
     * @param  User $user
     * @param       $images
     * @param bool $persist
     * @return User
     * @throws AccessDeniedException
     */
    public function addImages(User $user, $images, $persist = false)
    {
        //@todo : see why user is anonymous and not authenticated
        if ($user) {
            $nbImages = $user->getImages()->count();

            foreach ($images as $i => $image) {
                $userImage = new UserImage();
                $userImage->setName($image);
                $userImage->setPosition($nbImages + $i + 1);
                $user->addImage($userImage);
            }

            if ($persist) {
                $this->objectManager->persist($user);
                $this->objectManager->flush();
            }

        } else {
            throw new AccessDeniedException();
        }

        return $user;
    }

    /**
     * @param  User    $user
     * @param  string  $imageName
     * @param  string  $existingPicture
     * @param  boolean $persist
     * @throws AccessDeniedException
     */
    public function addImagesSetFirst(User $user, $imageName, $existingPicture, $persist = false)
    {
        if ($user) {
            $pos = 2;
            foreach ($user->getImages() as $image) {
                if ($existingPicture == $image->getName()) {
                    $user->removeImage($image);
                    $this->objectManager->remove($image);
                } else {
                    $image->setPosition($pos);
                    $this->objectManager->persist($image);
                    $pos++;
                }
            }

            $userImage = new UserImage();
            $userImage->setName($imageName);
            $userImage->setPosition(1);
            $user->addImage($userImage);

            if ($persist) {
                $this->objectManager->persist($user);
                $this->objectManager->flush();
            }

        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * [checkAndCreateOrUpdateUserByOAuth checks and add/update the user data]
     *
     * @param  UserResponseInterface $response
     * @return User|bool
     *
     * @throws
     */
    public function checkAndCreateOrUpdateUserByOAuth(UserResponseInterface $response)
    {
        $responseArray = $response->getResponse();
        if (isset($responseArray['id'])) {
            $user = null;
            /** @var UserFacebookRepository $facebookRepository */
            $facebookRepository = $this->objectManager->getRepository('CocoricoUserBundle:UserFacebook');
            /** @var UserFacebook $fbUser */
            $fbUser = $facebookRepository->findOneByFacebookId($responseArray['id']);

            // if the fbUser does not exist with the id, check email if user exist with email
            if (!$fbUser) {
                $user = $this->getRepository()->findOneByEmail($responseArray['email']);
                // check if the user exists with the email id provided by facebook
                if (!$user) {
                    // create new user with the facebook details
                    $user = new User();
                    $user->setEmail($responseArray['email']);
                    $user->setLastName($responseArray['last_name']);
                    $user->setFirstName($responseArray['first_name']);
                    $user->setPassword(uniqid());
                    $user->setEnabled(true);
                    $user->setMotherTongue(substr($responseArray['locale'], 0, 2));
                    if (array_key_exists('birthday', $responseArray)) {
                        $birthDate = new DateTime($responseArray['birthday']);
                    } else {
                        $birthDate = new DateTime('1915-01-01');
                    }
                    $user->setBirthday($birthDate);
                    //todo: transform location and hometown country to iso code
//                    if (array_key_exists('location', $responseArray)) {
//                        $user->setCountryOfResidence($responseArray['location']['name']);
//                    }
//
//                    if (array_key_exists('hometown', $responseArray)) {
//                        $user->setNationality($responseArray['hometown']['name']);
//                    }

                    $user->setEmailVerified($responseArray['verified']);
                }
                // if fbUser does not exist, then add one 
                $fbUser = new UserFacebook();
                $fbUser->setUser($user);
            } else {
                $user = $fbUser->getUser();
            }

            $fbUser->setFacebookId($responseArray['id']);
            $fbUser->setLink($responseArray['link']);
            $fbUser->setEmail($responseArray['email']);
            $fbUser->setLastName($responseArray['last_name']);
            $fbUser->setFirstName($responseArray['first_name']);
            $fbUser->setVerified($responseArray['verified']);
            $fbUser->setGender($responseArray['gender']);
            $fbUser->setLocale($responseArray['locale']);
            $fbUser->setTimezone($responseArray['timezone']);

            if (array_key_exists('birthday', $responseArray)) {
                $birthDate = new DateTime($responseArray['birthday']);
                $fbUser->setBirthday($birthDate);
            }

            if (array_key_exists('location', $responseArray)) {
                $fbUser->setLocation($responseArray['location']['name']);
                $fbUser->setLocationId($responseArray['location']['id']);
            }
            if (array_key_exists('hometown', $responseArray)) {
                $fbUser->setHometown($responseArray['hometown']['name']);
                $fbUser->setHometownId($responseArray['hometown']['id']);
            }

            if (array_key_exists('friends', $responseArray)) {
                $fbUser->setNbFriends(count($responseArray['friends']['data']));
            }

            $profilePic = false;
            if ($response->getProfilepicture()) {
                $profilePic = $this->saveFacebookPicture($response->getProfilepicture(), $responseArray['id']);
            }

            $event = new UserEvent($user);
            $this->dispatcher->dispatch(UserEvents::USER_REGISTER, $event);
            $user = $event->getUser();

            $this->updateUser($user);

            if ($profilePic) {
                $this->addImagesSetFirst($user, $profilePic, $fbUser->getPicture(), true);
                $fbUser->setPicture($profilePic);
            }
            $this->objectManager->persist($fbUser);
            $this->objectManager->flush();

            return $user;
        } else {
            return false;
        }
    }

    /**
     * saveFacebookPicture
     *
     * @param  string $url
     * @param  string $facebookId
     * @return string|boolean
     */
    private function saveFacebookPicture($url, $facebookId)
    {
        if ($url && $facebookId) {
            $fileName = sha1("fb_" . $facebookId . "_" . time());
            $path = $this->kernelRoot . '/../web/uploads/users/images/';
            $path = str_replace('\\', '/', $path);
            $filePathTmp = $path . $fileName . '.tmp';

            $this->grabImage($url, $filePathTmp);

            if (file_exists($filePathTmp)) {

                $mimeType = exif_imagetype($filePathTmp);
                if ($mimeType == IMAGETYPE_GIF || $mimeType == IMAGETYPE_JPEG || $mimeType == IMAGETYPE_PNG) {
                    $filePath = $path . $fileName . "." . image_type_to_extension($mimeType, false);
                    if (rename($filePathTmp, $filePath)) {
                        return $fileName . "." . image_type_to_extension($mimeType, false);
                    } else {
                        @unlink($filePathTmp);
                    }
                } else {
                    @unlink($filePathTmp);
                }
            }
        }

        return false;
    }

    private function grabImage($url, $saveTo)
    {
        $fp = fopen($saveTo, 'w+');              // open file handle

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // enable if you want
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FILE, $fp);          // output to file
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);      // some large value to allow curl to run for a long time
//        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
//        curl_setopt($ch, CURLOPT_VERBOSE, true);   // Enable this line to see debug prints
        curl_exec($ch);

        curl_close($ch);                              // closing curl handle
        fclose($fp);
    }

    /**
     *
     * @return UserRepository
     */
    public function getRepository()
    {
        return $this->objectManager->getRepository('CocoricoUserBundle:User');
    }

    public function persistAndFlush($entity)
    {
        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }


}
