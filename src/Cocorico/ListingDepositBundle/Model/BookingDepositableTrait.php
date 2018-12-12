<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ListingDepositBundle\Model;


use Doctrine\ORM\Mapping as ORM;

trait BookingDepositableTrait
{

    /**
     * @ORM\Column(name="amount_deposit", type="decimal", precision=8, scale=0, nullable=true)
     *
     * @var integer
     */
    protected $amountDeposit;

    /**
     * @return int
     */
    public function getAmountDeposit()
    {
        return $this->amountDeposit;
    }

    /**
     * @return float
     */
    public function getAmountDepositDecimal()
    {
        return $this->amountDeposit / 100;
    }

    /**
     * @param int $amountDeposit
     */
    public function setAmountDeposit($amountDeposit)
    {
        $this->amountDeposit = $amountDeposit;
    }


}