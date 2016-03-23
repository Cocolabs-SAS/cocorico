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
    protected function onSuccess(Form $form)
    {
        /** @var Listing $listing */
        $listing = $form->getData();
        $this->listingAvailabilityManager->saveAvailabilitiesStatus(
            $listing->getId(),
            $form->get('date_range')->getData(),
            $form->get('weekdays')->getData(),
            $form->has('time_ranges') ? $form->get('time_ranges')->getData() : array(),
            $form->get('status')->getData(),
            $listing->getPrice(),
            false,
            false
        );

        $listing->setAvailabilitiesUpdatedAt(new \DateTime());
        $this->entityManager->persist($listing);
        $this->entityManager->flush();

        return 1;
    }
}