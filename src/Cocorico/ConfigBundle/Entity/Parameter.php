<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ConfigBundle\Entity;

use Cocorico\ConfigBundle\Model\BaseParameter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Parameter
 *
 * @ORM\Entity
 *
 * @UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="assert.unique"
 * )
 * @ORM\Table(name="parameter",indexes={
 *    @ORM\Index(name="value_idx", columns={"value"})
 *  })
 *
 */
class Parameter extends BaseParameter
{
    /**
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;

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