<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Namer;

use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\Naming\NamerInterface;

/**
 * ImageNamer
 *
 */
class ImageNamer implements NamerInterface
{

    /**
     * @param  FileInterface $file
     * @return string
     */

    public function name(FileInterface $file)
    {
        $name = "";
        if ($extension = $file->getExtension()) {
            $name = sprintf('%s.%s', sha1(uniqid(mt_rand(), true)), $extension);
        }

        return $name;
    }
}
