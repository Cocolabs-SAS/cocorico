<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Handler\Dashboard;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\TimeBundle\Model\DateTimeRange;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Symfony\Component\Form\Exception\OutOfBoundsException;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\Form;

/**
 * Handle Listing Availability Price Form
 *
 */
class ListingAvailabilityPriceFormHandler extends ListingAvailabilityFormHandler
{
    /**
     * Save Listing Availability Price.
     *
     * @param Form $form
     *
     * @return int equal to :
     * 1: Success
     *
     * @throws OptimisticLockException
     * @throws Exception
     * @throws OutOfBoundsException
     * @throws RuntimeException
     */
    protected function onSuccessMany(Form $form)
    {
        /** @var Listing $listing */
        $listing = $form->getData();

        //If mod_fcgi then add IPCCommTimeout, IPCConnectTimeout to Vhost
        //Else set_time_limit(120);ini_set('max_execution_time', 120);ini_set('memory_limit', '256M');

        $dateTimeRange = new DateTimeRange(
            $form->get('date_range')->getData(),
            $form->has('time_ranges') ? $form->get('time_ranges')->getData() : array()
        );

        $this->availabilityManager->saveAvailabilitiesPrices(
            $listing->getId(),
            $dateTimeRange,
            $form->get('weekdays')->getData(),
            $form->get('price_custom')->getData(),
            false,
            $listing->getUser()->getTimeZone()
        );

        $listing->setAvailabilitiesUpdatedAt(new DateTime());
        $this->entityManager->persist($listing);
        $this->entityManager->flush();

        return 1;
    }


    /**
     * Save Listing Availability Status.
     *
     * @inheritdoc
     *
     * @return int equal to :
     * 1: Success
     */
    protected function onSuccessOne(Form $form, Listing $listing, $day, $start_time, $end_time)
    {
        $start = new DateTime($day);
        $startTime = new DateTime($day.' '.$start_time);
        $endTime = new DateTime($day.' '.$end_time);
        $dateTimeRange = DateTimeRange::createFromDateTimes($start, $start, $startTime, $endTime);

        $this->availabilityManager->saveAvailabilitiesPrices(
            $listing->getId(),
            $dateTimeRange,
            array(),
            $form->get('price')->getData(),
            false,
            $listing->getUser()->getTimeZone()
        );

        $listing->setAvailabilitiesUpdatedAt(new DateTime());
        $this->entityManager->persist($listing);
        $this->entityManager->flush();

        return 1;
    }
}