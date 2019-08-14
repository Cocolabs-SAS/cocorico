<?php

/*
* This file is part of the Cocorico package.
*
* (c) Cocolabs SAS <contact@cocolabs.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Cocorico\UserBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

//One shot

class ResetNbBookingsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cocorico_user:reset_nb_bookings')
            ->setDescription(
                'Reset nb bookings as asker and offerer'
            )
            ->setHelp("Usage php bin/console cocorico_user:reset_nb_bookings");
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        return false;

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        //Set to 0
        $em->createQueryBuilder()
            ->update('CocoricoUserBundle:User', 'u')
            ->set('u.nbBookingsAsker', ':nb_bookings_asker')
            ->set('u.nbBookingsOfferer', ':nb_bookings_offerer')
            ->setParameter('nb_bookings_asker', 0)
            ->setParameter('nb_bookings_offerer', 0)
            ->getQuery()
            ->execute();

        //Set to nb bookings
        $bookingRepo = $em->getRepository('CocoricoCoreBundle:Booking');
        $bookingsValidated = $bookingRepo->findBy(array('validated' => true));

        foreach ($bookingsValidated as $i => $bookingValidated) {
            $asker = $bookingValidated->getUser();
            $offerer = $bookingValidated->getListing()->getUser();

            $offerer->setNbBookingsOfferer($offerer->getNbBookingsOfferer() + 1);
            $asker->setNbBookingsAsker($asker->getNbBookingsAsker() + 1);

            $em->persist($asker);
            $em->persist($offerer);
        }

        $em->flush();

        $output->writeln(count($bookingsValidated) . " bookings(s) processed");
    }
}
