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
     */
    protected function onSuccess(Form $form)
    {
        /** @var Listing $listing */
        $listing = $form->getData();

        //If mod_fcgi then add IPCCommTimeout, IPCConnectTimeout to Vhost
        //Else set_time_limit(120);ini_set('max_execution_time', 120);ini_set('memory_limit', '256M');

        $this->listingAvailabilityManager->saveAvailabilitiesPrices(
            $listing->getId(),
            $form->get('date_range')->getData(),
            $form->get('weekdays')->getData(),
            $form->has('time_ranges') ? $form->get('time_ranges')->getData() : array(),
            $form->get('price_custom')->getData(),
            false,
            false
        );

        $listing->setAvailabilitiesUpdatedAt(new \DateTime());
        $this->entityManager->persist($listing);
        $this->entityManager->flush();

        return 1;
    }
}