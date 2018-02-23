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
 * Validate bookings commands
 * For example every hour :
 */

//Cron: 0 */1  * * *  user   php app/console cocorico:bookings:validate

class ValidateBookingsCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this
            ->setName('cocorico:bookings:validate')
            ->setDescription('Validate Bookings.')
            ->addOption(
                'delay',
                null,
                InputOption::VALUE_OPTIONAL,
                'Booking validation delay in minutes. To use only on no prod env'
            )
            ->addOption(
                'moment',
                null,
                InputOption::VALUE_OPTIONAL,
                'Booking validation moment. To use only on no prod env'
            )
            ->addOption(
                'test',
                null,
                InputOption::VALUE_NONE,
                'Extra precaution to ensure to use on test mode'
            )
            ->setHelp("Usage php app/console cocorico:bookings:validate");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $moment = $this->getContainer()->getParameter('cocorico.booking.validated_moment');
        $delay = $this->getContainer()->getParameter('cocorico.booking.validated_delay');
        if ($input->getOption('test') && $input->hasOption('delay') && $input->getOption('moment')) {
            $moment = $input->getOption('moment');
            $delay = $input->getOption('delay');
        }

        $result = $this->getContainer()->get('cocorico.booking.manager')->validateBookings($moment, $delay);

        $output->writeln($result . " booking(s) validated");
    }

}
