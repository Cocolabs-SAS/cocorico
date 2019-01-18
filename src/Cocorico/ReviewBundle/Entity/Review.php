<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ReviewBundle\Entity;

use Cocorico\ReviewBundle\Model\BaseReview;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Review
 *
 * @ORM\Entity(repositoryClass="Cocorico\ReviewBundle\Repository\ReviewRepository")
 * @ORM\Table(name="review",indexes={
 *    @ORM\Index(name="created_at_r_idx", columns={"createdAt"}),
 *  })
 *
 * @UniqueEntity(
 *     fields={"booking", "reviewBy"},
 *     errorPath="booking",
 *      message="entity.review.unique_allowed"
 * )
 * @ORM\EntityListeners({"Cocorico\ReviewBundle\Entity\Listener\ReviewListener" })
 */
class Review extends BaseReview
{
    use ORMBehaviors\Timestampable\Timestampable;

    /**
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return
            $this->getReviewBy()->getName() . " (" .
            $this->getBooking()->getListing() . ":" . $this->getBooking()->getStart()->format('d-m-Y')
            . ")";
    }
}
