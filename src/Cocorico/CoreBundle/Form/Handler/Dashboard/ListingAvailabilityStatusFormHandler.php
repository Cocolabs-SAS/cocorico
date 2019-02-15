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
use Symfony\Component\Form\Form;

/**
 * Handle Listing Availability Status Form
 *
 */
class ListingAvailabilityStatusFormHandler extends ListingAvailabilityFormHandler
{
    /**
     * Save Listing Availability Status.
     *
     * @param Form $form
     *
     * @return int equal to :
     * 1: Success
     */
    protected function onSuccessMany(Form $form)
    {
        /** @var Listing $listing */
        $listing = $form->getData();
        $dateTimeRange = new DateTimeRange(
            $form->get('date_range')->getData(),
            $form->has('time_ranges') ? $form->get('time_ranges')->getData() : array()
        );

        $this->availabilityManager->saveAvailabilitiesStatus(
            $listing->getId(),
            $dateTimeRange,
            $form->get('weekdays')->getData(),
            $form->get('status')->getData(),
            $listing->getPrice(),
            false,
            $listing->getUser()->getTimeZone()
        );

        $listing->setAvailabilitiesUpdatedAt(new \DateTime());
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
        $start = new \DateTime($day);
        $startTime = new \DateTime($day . ' ' . $start_time);
        $endTime = new \DateTime($day . ' ' . $end_time);
        $dateTimeRange = DateTimeRange::createFromDateTimes($start, $start, $startTime, $endTime);

        $this->availabilityManager->saveAvailabilitiesStatus(
            $listing->getId(),
            $dateTimeRange,
            array(),
            $form->get('status')->getData(),
            $listing->getPrice(),
            false,
            $listing->getUser()->getTimeZone()
        );

        $listing->setAvailabilitiesUpdatedAt(new \DateTime());
        $this->entityManager->persist($listing);
        $this->entityManager->flush();

        return 1;
    }

}