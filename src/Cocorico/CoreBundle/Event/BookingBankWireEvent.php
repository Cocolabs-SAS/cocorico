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

use Cocorico\CoreBundle\Entity\BookingBankWire;
use Symfony\Component\EventDispatcher\Event;

class BookingBankWireEvent extends Event
{
    protected $bookingBankWire;
    protected $checked;

    public function __construct(BookingBankWire $bookingBankWire)
    {
        $this->bookingBankWire = $bookingBankWire;
        $this->checked = false;
    }

    /**
     * @return BookingBankWire
     */
    public function getBookingBankWire()
    {
        return $this->bookingBankWire;
    }

    /**
     * @param BookingBankWire $bookingBankWire
     */
    public function setBookingBankWire(BookingBankWire $bookingBankWire)
    {
        $this->bookingBankWire = $bookingBankWire;
    }

    /**
     * @return boolean
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * @param boolean $checked
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;
    }

}
