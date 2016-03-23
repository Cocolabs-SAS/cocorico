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
 * Calendar update alert commands
 * Every Month on 27  :
 */

//Cron: 0 0 27 * *  user   php app/console cocorico:listings:alertUpdateCalendars

class AlertListingsCalendarUpdateCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this
            ->setName('cocorico:listings:alertUpdateCalendars')
            ->setDescription('Alert listings calendars update.')
            ->setHelp("Usage php app/console cocorico:listings:alertUpdateCalendars");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $listingManager = $container->get('cocorico.listing.manager');

        $result = $listingManager->alertUpdateCalendars();

        $output->writeln($result . " listing(s) calendar update alerted");
    }

}
