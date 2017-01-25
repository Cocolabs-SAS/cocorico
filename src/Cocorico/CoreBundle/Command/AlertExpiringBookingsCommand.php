<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * Bookings expiring alert
 * Every  15 minutes :
 */

//Cron: */15 * * * *  user   php app/console cocorico:bookings:alertExpiring

class AlertExpiringBookingsCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this
            ->setName('cocorico:bookings:alertExpiring')
            ->setDescription('Alert Expiring Bookings.')
            ->addOption(
                'delay',
                null,
                InputOption::VALUE_OPTIONAL,
                'Booking expiration delay in minutes. To use only on no prod env'
            )
            ->addOption(
                'alert_delay',
                null,
                InputOption::VALUE_OPTIONAL,
                'Booking expiring alert delay in minutes. To use only on no prod env'
            )
            ->addOption(
                'test',
                null,
                InputOption::VALUE_NONE,
                'Extra precaution to ensure to use on test mode'
            )
            ->setHelp("Usage php app/console cocorico:bookings:alertExpiring");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $delay = $this->getContainer()->getParameter('cocorico.booking.expiration_delay');
        $alertDelay = $this->getContainer()->getParameter('cocorico.booking.alert_expiration_delay');

        if ($input->getOption('test') && $input->hasOption('delay') && $input->hasOption('alert_delay')) {
            $delay = $input->getOption('delay');
            $alertDelay = $input->getOption('alert_delay');
        }

        $container = $this->getContainer();
        $bookingManager = $container->get('cocorico.booking.manager');

        $result = $bookingManager->alertExpiringBookings($delay, $alertDelay);

        $output->writeln($result . " booking(s) expiring alerted");
    }

}
