<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Listener;

use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Model\UserManager;
use Oneup\UploaderBundle\Event\PostUploadEvent;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserImageUploadListener
{
    protected $uem;

    public function __construct(UserManager $uem)
    {
        $this->uem = $uem;
    }

    public function onUpload(PostUploadEvent $event)
    {
        /** @var UploadedFile $file */
        $file = $event->getFile();
        $request = $event->getRequest();
        $response = $event->getResponse();
        //print_r($request->request->all());
        $idUser = $request->query->get("user_id");

        // $idUser = $request->getUser()->getId();
        if ($idUser) {
            /** @var User $user */
            $user = $this->uem->getRepository()->find($idUser);
            $this->uem->addImages(
                $user,
                array($file->getFilename()),
                true
            );
        }

        $response['files'] = array(
            array(
                'name' => $file->getFilename(),
//                'size' => $file->getSize(),
//                'url' => '',
//                'deleteUrl' => '',
//                'deleteType' => 'DELETE'
            )
        );
    }

}
