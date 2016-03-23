<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Event;

use Cocorico\CoreBundle\Entity\Booking;

class BookingValidateEvent extends BookingEvent
{
    protected $validated;

    public function __construct(Booking $booking)
    {
        parent::__construct($booking);
        $this->validated = false;
    }

    /**
     * @param bool $validated
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
    }

    /**
     * @return bool
     */
    public function getValidated()
    {
        return $this->validated;
    }

}
