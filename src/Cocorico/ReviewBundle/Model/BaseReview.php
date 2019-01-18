<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\ReviewBundle\Model;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BaseReview
 *
 * @ORM\MappedSuperclass()
 *
 */
abstract class BaseReview
{

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\Booking", inversedBy="reviews", cascade={"persist"})
     * @ORM\JoinColumn(name="booking_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var Booking
     */
    protected $booking;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User", inversedBy="reviewsBy", cascade={"persist"})
     * @ORM\JoinColumn(name="review_by", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    protected $reviewBy;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     *
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User", inversedBy="reviewsTo", cascade={"persist"})
     * @ORM\JoinColumn(name="review_to", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    protected $reviewTo;

    /**
     * @Assert\NotBlank(message="cocorico_review.rating.not_blank", groups={"new"})
     * @Assert\Range(
     *      min = 1,
     *      max = 5,
     *      minMessage = "entity.review.min_limit",
     *      maxMessage = "entity.review.max_limit"
     * )
     *
     * @ORM\Column(name="rating", type="smallint", nullable=false)
     *
     * @var integer
     */
    protected $rating;

    /**
     * @Assert\NotBlank(message="cocorico_review.comment.not_blank", groups={"new"})
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=false)
     *
     * @var string
     */
    protected $comment;


    public function __construct()
    {
        $this->rating = 0;
    }

    /**
     * @return Integer
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Set ReviewBy
     *
     * @param \Cocorico\UserBundle\Entity\User|null $reviewBy
     * @return $this
     */
    public function setReviewBy($reviewBy)
    {
        $this->reviewBy = $reviewBy;

        return $this;
    }

    /**
     * Get ReviewBy
     *
     * @return \Cocorico\UserBundle\Entity\User
     */
    public function getReviewBy()
    {
        return $this->reviewBy;
    }


    /**
     * Set ReviewTo
     *
     * @param \Cocorico\UserBundle\Entity\User|null $reviewTo
     * @return $this
     */
    public function setReviewTo($reviewTo)
    {
        $this->reviewTo = $reviewTo;

        return $this;
    }

    /**
     * Get ReviewTo
     *
     * @return \Cocorico\UserBundle\Entity\User
     */
    public function getReviewTo()
    {
        return $this->reviewTo;
    }

    /**
     * Set booking
     *
     * @param \Cocorico\CoreBundle\Entity\Booking $booking
     * @return $this
     */
    public function setBooking(Booking $booking)
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     * Get booking
     *
     * @return \Cocorico\CoreBundle\Entity\Booking
     */
    public function getBooking()
    {
        return $this->booking;
    }


}
