<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\ListingDepositBundle\Twig;


class DepositExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    public function __construct()
    {
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getFilters()
    {
        return array();
    }


    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getFunctions()
    {
        return array();
    }


    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getGlobals()
    {
        $bookingDepositRefund = new \ReflectionClass("Cocorico\ListingDepositBundle\Entity\BookingDepositRefund");
        $bookingDepositRefundConstants = $bookingDepositRefund->getConstants();

        return array(
            'BookingDepositRefundConstants' => $bookingDepositRefundConstants,
        );
    }


    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getName()
    {
        return 'deposit_extension';
    }
}
