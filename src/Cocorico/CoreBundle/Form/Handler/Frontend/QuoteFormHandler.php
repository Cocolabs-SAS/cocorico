<?php

namespace Cocorico\CoreBundle\Form\Handler\Frontend;

# use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Quote;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Model\DateRange;
use Cocorico\CoreBundle\Model\ListingSearchRequest;
# use Cocorico\CoreBundle\Model\Manager\BookingManager;
use Cocorico\CoreBundle\Model\TimeRange;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;


/**
 * Handle Booking Price Form
 *
 */
class QuoteFormHandler
{
    protected $session;
    protected $request;
    protected $listingSearchRequest;

    /**
     * Initialize the handler with the form and the request
     *
     * @param Session              $session
     * @param RequestStack         $requestStack ,
     * @param ListingSearchRequest $listingSearchRequest
     *
     */
    public function __construct(
        Session $session,
        RequestStack $requestStack,
        ListingSearchRequest $listingSearchRequest
    ) {
        $this->session = $session;
        $this->request = $requestStack->getCurrentRequest();
        $this->listingSearchRequest = $listingSearchRequest;
    }


    /**
     * Init form
     *
     * @param User|null $user
     * @param Listing   $listing
     *
     * @return Quote $quote
     */
    public function init($user, Listing $listing)
    {
        /** @var ListingSearchRequest $searchRequest */
        $searchRequest = $this->session->get('listing_search_request', $this->listingSearchRequest);
        // $booking = $this->bookingManager->initBooking($listing, $user, $dateTimeRange);
        $quote = new Quote();
        $quote->setListing($listing);

        return $quote;
    }

}
