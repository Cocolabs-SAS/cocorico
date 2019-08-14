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
use Symfony\Component\Console\Output\OutputInterface;

/*
 * Validate bookings commands
 * For example every two hours :
 */

//Cron: 0 */2  * * *  user   php app/console cocorico:bookings:checkBankWires

class CheckBookingsBankWiresCommand extends ContainerAwareCommand
{
    /** @inheritdoc */
    public function configure()
    {
        $this
            ->setName('cocorico:bookings:checkBankWires')
            ->setDescription('Check Bookings Bank Wires. Set status to done if bank wire has been transferred')
            ->setHelp("Usage php bin/console cocorico:bookings:checkBankWires");
    }

    /** @inheritdoc */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->getContainer()->get('cocorico.booking_bank_wire.manager')->checkBookingsBankWires();
        $output->writeln($result . " booking(s) bank wires checked");
    }

}
