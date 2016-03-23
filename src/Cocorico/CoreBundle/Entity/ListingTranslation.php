<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Entity;

use Cocorico\CoreBundle\Model\BaseListingTranslation;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="listing_translation",indexes={
 *    @ORM\Index(name="slug_idx", columns={"slug"})
 *  })
 *
 */
class ListingTranslation extends BaseListingTranslation
{
    use ORMBehaviors\Translatable\Translation;
    use ORMBehaviors\Sluggable\Sluggable;

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }
}
