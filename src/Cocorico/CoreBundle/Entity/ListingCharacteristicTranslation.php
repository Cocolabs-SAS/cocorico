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

use Cocorico\CoreBundle\Model\BaseListingCharacteristicTranslation;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * ListingCharacteristicTranslation
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="listing_characteristic_translation")
 */
class ListingCharacteristicTranslation extends BaseListingCharacteristicTranslation
{
    use ORMBehaviors\Translatable\Translation;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
