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

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\TimeBundle\Model\DateTimeRange;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * Migrate booking dates for multi timezone and time ranges spanning days upgrade compatibility.
 * To do just after the upgrade deployment.
 */

//One shot

class MigrateBookingsDatesCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this
            ->setName('cocorico:bookings:migrate-dates')
            ->setDescription('Migrate booking dates.')
            ->setHelp("Usage php bin/console cocorico:bookings:migrate-dates");
    }

    /** @inheritdoc */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $bookingManager = $this->getContainer()->get('cocorico.booking.manager');
        /** @var Booking[] $bookings */
        $bookings = $bookingManager->getRepository()->findAll();

        foreach ($bookings as $index => $booking) {
            $dateRange = $booking->getDateRange();
            $timeRange = $booking->getTimeRange();

            if ($booking->getTimeRange()->getStart()->format('Y-m-d') != '1970-01-01') {
                $dateTimeRange = DateTimeRange::addTimesToDates($dateRange, $timeRange);
            } else {//Date storage previous multi timezone implementation
                $start = $dateRange->getStart()->format('Y-m-d');
                $end = $dateRange->getEnd()->format('Y-m-d');
                $startTime = $timeRange->getStart()->format('H:i');
                $endTime = $timeRange->getEnd()->format('H:i');

                $startDateTime = new DateTime($start.' '.$startTime);
                $dateRange->setStart($startDateTime);
                $dateRange->setEnd(new DateTime($end.' '.$endTime));

                $timeRange->setStart($startDateTime);
                $timeRange->setEnd(new DateTime($start.' '.$endTime));
                $dateTimeRange = new DateTimeRange($dateRange, $timeRange);
            }

            $booking->setDateRange($dateTimeRange->getDateRange());
            $booking->setTimeRange($dateTimeRange->getFirstTimeRange());
            $bookingManager->save($booking);
        }
        $output->writeln(count($bookings) . " booking(s) date migrated");
    }

}
