<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\PageBundle\Entity;

use Cocorico\PageBundle\Model\BasePageTranslation;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="page_translation",indexes={
 *    @ORM\Index(name="slug_pt_idx", columns={"slug"})
 *  })
 *
 */
class PageTranslation extends BasePageTranslation
{
    use ORMBehaviors\Translatable\Translation;
    use ORMBehaviors\Sluggable\Sluggable;

}
