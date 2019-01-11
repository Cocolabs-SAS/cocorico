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
 * Expire bookings commands
 * For example every hour :
 */

//Cron: 0 */1  * * *  user   php app/console cocorico:bookings:expire

class ExpireBookingsCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this
            ->setName('cocorico:bookings:expire')
            ->setDescription('Expire Bookings.')
            ->addOption(
                'expiration-delay',
                null,
                InputOption::VALUE_OPTIONAL,
                'Booking expiration delay in minutes. To use only on no prod env'
            )
            ->addOption(
                'acceptation-delay',
                null,
                InputOption::VALUE_OPTIONAL,
                'Booking acceptation delay in minutes. To use only on no prod env'
            )
            ->addOption(
                'test',
                null,
                InputOption::VALUE_NONE,
                'Extra precaution to ensure to use on test mode'
            )
            ->setHelp("Usage php app/console cocorico:bookings:expire");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $expirationDelay = $acceptationDelay = null;

        if ($input->getOption('test') && $input->getOption('expiration-delay') &&
            $input->getOption('acceptation-delay')
        ) {
            $expirationDelay = $input->getOption('expiration-delay');
            $acceptationDelay = $input->getOption('acceptation-delay');
        }

        $result = $this->getContainer()->get('cocorico.booking.manager')->expireBookings(
            $expirationDelay,
            $acceptationDelay
        );

        $output->writeln($result . " booking(s) expired");
    }

}
