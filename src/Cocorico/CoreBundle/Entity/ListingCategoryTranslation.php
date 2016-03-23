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

use Cocorico\CoreBundle\Model\BaseListingCategoryTranslation;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * ListingCategoryTranslation
 *
 * @ORM\Table(name="listing_category_translation",indexes={
 *    @ORM\Index(name="name_idx", columns={"name"})
 *  })
 *
 * @ORM\Entity
 */
class ListingCategoryTranslation extends BaseListingCategoryTranslation
{
    use ORMBehaviors\Translatable\Translation;
    use ORMBehaviors\Sluggable\Sluggable;

}
