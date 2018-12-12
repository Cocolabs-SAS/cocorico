<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ListingDepositBundle\Entity;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\ListingDepositBundle\Model\BaseBookingDepositRefund;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BookingDepositRefund
 *
 * @ORM\Entity(repositoryClass="Cocorico\ListingDepositBundle\Repository\BookingDepositRefundRepository")
 * @UniqueEntity("booking", message="assert.unique")
 *
 * @ORM\Table(name="booking_deposit_refund",indexes={
 *    @ORM\Index(name="status_asker_bdr_idx", columns={"status_asker"}),
 *    @ORM\Index(name="status_offerer_bdr_idx", columns={"status_offerer"}),
 *    @ORM\Index(name="created_at_bdr_idx", columns={"createdAt"}),
 *  })
 */
class BookingDepositRefund extends BaseBookingDepositRefund
{
    use ORMBehaviors\Timestampable\Timestampable;
    use \Cocorico\MangoPayBundle\Model\BookingDepositPayinRefundMangoPayableTrait;
    use \Cocorico\MangoPayBundle\Model\BookingDepositBankWireMangoPayableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Cocorico\CoreBundle\Model\CustomIdGenerator")
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User", inversedBy="bookingDepositRefundsAsAsker")
     * @ORM\JoinColumn(name="asker_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    private $asker;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User", inversedBy="bookingDepositRefundsAsOfferer")
     * @ORM\JoinColumn(name="offerer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    private $offerer;
    /**
     * @ORM\OneToOne(targetEntity="Cocorico\CoreBundle\Entity\Booking", inversedBy="depositRefund")
     * @ORM\JoinColumn(name="booking_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     *
     * @var Booking
     */
    private $booking;


    public function __construct()
    {
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

    /**
     * @return mixed
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * @param mixed $booking
     */
    public function setBooking($booking)
    {
        $this->booking = $booking;
    }

    /**
     * @return User
     */
    public function getAsker()
    {
        return $this->asker;
    }

    /**
     * @param User $asker
     */
    public function setAsker($asker)
    {
        $this->asker = $asker;
    }

    /**
     * @return User
     */
    public function getOfferer()
    {
        return $this->offerer;
    }

    /**
     * @param User $offerer
     */
    public function setOfferer($offerer)
    {
        $this->offerer = $offerer;
    }

    public function __toString()
    {
        $booking = $this->getBooking();

        return $this->getId() . " (" . $booking->getListing() . ":" . $booking->getStart()->format('d-m-Y') . ")";
    }
}
