<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\FormModel;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;
use FOS\MessageBundle\FormModel\AbstractMessage;
use FOS\MessageBundle\Model\ParticipantInterface;

class NewThreadMessage extends AbstractMessage
{

    /**
     * The user who receives the message
     *
     * @var ParticipantInterface
     */
    protected $recipient;

    /**
     * The thread subject
     *
     * @var string
     */
    protected $subject;

    /**
     * The thread listing
     *
     * @var Listing
     */
    protected $listing;

    /**
     * The thread booking
     *
     * @var Booking
     */
    protected $booking;

    /**
     * @return Listing
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @param  Listing $listing
     * @return $this
     */
    public function setListing(Listing $listing = null)
    {
        $this->listing = $listing;

        return $this;
    }

    /**
     * @return Booking
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * @param  Booking $booking
     * @return $this
     */
    public function setBooking(Booking $booking = null)
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param  string
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return ParticipantInterface
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param  ParticipantInterface
     * @return $this
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }
}
