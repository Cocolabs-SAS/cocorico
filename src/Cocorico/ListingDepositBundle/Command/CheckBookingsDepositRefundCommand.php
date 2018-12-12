<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ListingDepositBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * Validate bookings commands
 * For example every two hours :
 */

//Cron: 0 */2  * * *  user   php app/console cocorico_listing_deposit:bookings:checkDepositsRefund

class CheckBookingsDepositRefundCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this
            ->setName('cocorico_listing_deposit:bookings:checkDepositsRefund')
            ->setDescription('Check Deposit Refunds. Set status to done if deposit has been refunded')
            ->setHelp("Usage php app/console cocorico_listing_deposit:bookings:checkDepositsRefund");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->getContainer()->get('cocorico_listing_deposit.booking_deposit_refund.manager')
            ->checkBookingsDepositRefunds();

        $output->writeln($result . " booking(s) deposit refunds checked");
    }

}
