<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Listener;

use Oneup\UploaderBundle\Event\ValidationEvent;
use Oneup\UploaderBundle\Uploader\Exception\ValidationException;

class ListingImageUploadValidationListener
{
    private $maxUploadFileSize;

    /**
     * @param int $maxUploadFileSize In MB
     */
    public function __construct($maxUploadFileSize)
    {
        $this->maxUploadFileSize = $maxUploadFileSize;
    }

    /**
     * @param ValidationEvent $event
     *
     * @throws ValidationException
     */
    public function onValidate(ValidationEvent $event)
    {
        $file = $event->getFile();
//        die($this->maxUploadFileSize . "--" . $file->getSize()  );
        if ($file->getSize() > ($this->maxUploadFileSize * 1000000)) {
            throw new ValidationException('File size too large');
        }

    }
}
