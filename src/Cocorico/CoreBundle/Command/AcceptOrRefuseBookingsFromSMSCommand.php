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
 * Accept or refuse bookings from SMS
 * For example every hour :
 */

//Cron: 5 */1  * * *  user   php app/console cocorico:bookings:acceptOrRefuseFromSMS

class AcceptOrRefuseBookingsFromSMSCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this
            ->setName('cocorico:bookings:acceptOrRefuseFromSMS')
            ->setDescription('Accept or refuse bookings from SMS.')
            ->addOption(
                'enabled',
                null,
                InputOption::VALUE_OPTIONAL,
                'Does SMS is enabled. To use only on no prod env'
            )
            ->addOption(
                'test',
                null,
                InputOption::VALUE_NONE,
                'Extra precaution to ensure to use on test mode'
            )
            ->setHelp("Usage php app/console cocorico:bookings:acceptOrRefuseFromSMS");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $bookingManager = $container->get('cocorico.booking.manager');
        $smsEnabled = $container->getParameter('cocorico_sms.enabled');
        if ($input->getOption('test') && $input->hasOption('enabled')) {
            $smsEnabled = $input->getOption('enabled');
        }

        if ($smsEnabled) {
            $result = $bookingManager->acceptOrRefuseFromSMS();
            $output->writeln($result . " booking(s) accepted or refused");
        } else {
            $output->writeln("SMS is disabled");
        }
    }
}
