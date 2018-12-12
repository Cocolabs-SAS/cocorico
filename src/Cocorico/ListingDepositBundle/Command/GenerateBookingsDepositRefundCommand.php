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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * Refund bookings deposit Command
 * For example every hour :
 */

//Cron: 0 */1  * * *  user   php app/console cocorico_listing_deposit:bookings:generateDepositRefund

class GenerateBookingsDepositRefundCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this
            ->setName('cocorico_listing_deposit:bookings:generateDepositRefund')
            ->setDescription('Generate bookings deposit refunds.')
            ->addOption(
                'delay',
                null,
                InputOption::VALUE_OPTIONAL,
                'Booking deposit refunds creation delay in minutes. To use only on no prod env. Must be >= 0'
            )
            ->addOption(
                'test',
                null,
                InputOption::VALUE_NONE,
                'Extra precaution to ensure to use on test mode'
            )
            ->setHelp("Usage php app/console cocorico_listing_deposit:bookings:generateDepositRefund");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $delay = $this->getContainer()->getParameter('cocorico_listing_deposit.booking.deposit_refund_delay');
        if ($input->getOption('test') && $input->hasOption('delay')) {
            $delay = $input->getOption('delay');
        }

        $result = $this->getContainer()->get('cocorico_listing_deposit.booking_deposit_refund.manager')
            ->generateBookingsDepositRefund($delay);

        $output->writeln($result . " booking(s) deposit refund created");
    }

}
