<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Handler\Frontend;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Model\ListingSearchRequest;
use Cocorico\CoreBundle\Model\Manager\BookingManager;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Handle Booking Price Form
 *
 */
class BookingPriceFormHandler
{
    protected $session;
    protected $request;
    protected $listingSearchRequest;
    protected $bookingManager;

    /**
     * Initialize the handler with the form and the request
     *
     * @param Session              $session
     * @param RequestStack         $requestStack ,
     * @param ListingSearchRequest $listingSearchRequest
     * @param BookingManager       $bookingManager
     *
     */
    public function __construct(
        Session $session,
        RequestStack $requestStack,
        ListingSearchRequest $listingSearchRequest,
        BookingManager $bookingManager
    ) {
        $this->session = $session;
        $this->request = $requestStack->getCurrentRequest();
        $this->listingSearchRequest = $listingSearchRequest;
        $this->bookingManager = $bookingManager;
    }


    /**
     * Init form
     *
     * @param User|null $user
     * @param Listing   $listing
     *
     * @return Booking $booking
     */
    public function init($user, Listing $listing)
    {
        /** @var ListingSearchRequest $searchRequest */
        $searchRequest = $this->session->get('listing_search_request', $this->listingSearchRequest);
        $dateTimeRange = $searchRequest->getDateTimeRange();
        $booking = $this->bookingManager->initBooking($listing, $user, $dateTimeRange);

        return $booking;
    }

    /**
     * Process form
     *
     * @return boolean
     */
    public function process()
    {
        // Check the method
        if ('POST' == $this->request->getMethod()) {
            // Bind value with form
            $this->form->handleRequest($this->request);

            $data = $this->form->getData();
            $this->onSuccess($data);

            return true;
        }

        return false;
    }

    /**
     *
     * @param array $data
     *
     */
    protected function onSuccess($data)
    {

    }
}