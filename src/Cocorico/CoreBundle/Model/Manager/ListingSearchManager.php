<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Model\Manager;

use Cocorico\CoreBundle\Document\ListingAvailability;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Event\ListingSearchEvent;
use Cocorico\CoreBundle\Event\ListingSearchEvents;
use Cocorico\CoreBundle\Model\ListingSearchRequest;
use Cocorico\CoreBundle\Model\PriceRange;
use Cocorico\CoreBundle\Repository\ListingAvailabilityRepository;
use Cocorico\CoreBundle\Repository\ListingRepository;
use Cocorico\TimeBundle\Model\DateRange;
use Cocorico\TimeBundle\Model\DateTimeRange;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ListingSearchManager
{
    protected $em;
    protected $dm;
    protected $dispatcher;
    protected $endDayIncluded;
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $maxPerPage;
    protected $listingDefaultStatus;

    /**
     * @param EntityManager            $em
     * @param DocumentManager          $dm
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $parameters
     */
    public function __construct(
        EntityManager $em,
        DocumentManager $dm,
        EventDispatcherInterface $dispatcher,
        array $parameters
    ) {
        $this->em = $em;
        $this->dm = $dm;
        $this->dispatcher = $dispatcher;

        $parameters = $parameters["parameters"];
        $this->endDayIncluded = $parameters["cocorico_booking_end_day_included"];
        $this->timeUnit = $parameters["cocorico_time_unit"];
        $this->timeUnitIsDay = ($this->timeUnit % 1440 == 0) ? true : false;
        $this->maxPerPage = $parameters["cocorico_listing_search_max_per_page"];
        $this->listingDefaultStatus = $parameters["cocorico_listing_availability_status"];
    }

    /**
     * @param ListingSearchRequest $listingSearchRequest
     * @param                      $locale
     *
     * @return Paginator|null
     */
    public function search(ListingSearchRequest $listingSearchRequest, $locale)
    {
        //Select
        $queryBuilder = $this->getRepository()->getFindQueryBuilder();

        //Geo location
        $queryBuilder = $this->getSearchByGeoLocationQueryBuilder($listingSearchRequest, $queryBuilder);

        $queryBuilder
            ->andWhere('t.locale = :locale')
            ->andWhere('l.status = :listingStatus')
            ->setParameter('locale', $locale)
            ->setParameter('listingStatus', Listing::STATUS_PUBLISHED);

        //Dates
        $queryBuilder = $this->getSearchByDateQueryBuilder($listingSearchRequest, $queryBuilder);

        //Prices
        $priceRange = $listingSearchRequest->getPriceRange();
        if ($priceRange->getMin() && $priceRange->getMax()) {
            $queryBuilder
                ->andWhere('l.price BETWEEN :minPrice AND :maxPrice')
                ->setParameter('minPrice', $priceRange->getMin())
                ->setParameter('maxPrice', $priceRange->getMax());
        }

        //Categories
        $categories = $listingSearchRequest->getCategories();
        if (count($categories)) {
            $queryBuilder
                ->andWhere("llcat.category IN (:categories)")
                ->setParameter("categories", $categories);
        }

        //Characteristics
        $queryBuilder = $this->getSearchByCharacteristicsQueryBuilder($listingSearchRequest, $queryBuilder);

        //Order
        switch ($listingSearchRequest->getSortBy()) {
            case 'price':
                $queryBuilder->orderBy("l.price", "ASC");
                break;
            case 'distance':
                $queryBuilder->orderBy("distance", "ASC");
                break;
            default:
                $queryBuilder->orderBy("distance", "ASC");
                break;
        }
        $queryBuilder->addOrderBy("l.averageRating", "DESC");
        $queryBuilder->addOrderBy("l.adminNotation", "DESC");

        if (!$listingSearchRequest->getIsXmlHttpRequest()) {
            $event = new ListingSearchEvent($listingSearchRequest, $queryBuilder);
            $this->dispatcher->dispatch(ListingSearchEvents::LISTING_SEARCH, $event);
            $queryBuilder = $event->getQueryBuilder();
        }

        //Pagination
        if ($listingSearchRequest->getMaxPerPage()) {
            $queryBuilder
                ->setFirstResult(($listingSearchRequest->getPage() - 1) * $listingSearchRequest->getMaxPerPage())
                ->setMaxResults($listingSearchRequest->getMaxPerPage());
        }

        //Query
        $query = $queryBuilder->getQuery();
        $query->setHydrationMode(Query::HYDRATE_ARRAY);

        return new Paginator($query);
    }

    /**
     * @param ListingSearchRequest       $listingSearchRequest
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getSearchByGeoLocationQueryBuilder(ListingSearchRequest $listingSearchRequest, $queryBuilder)
    {
        $searchLocation = $listingSearchRequest->getLocation();
        //Select distance
        $queryBuilder
            ->addSelect('GEO_DISTANCE(co.lat = :lat, co.lng = :lng) AS distance')
            ->setParameter('lat', $searchLocation->getLat())
            ->setParameter('lng', $searchLocation->getLng());

        $viewport = $searchLocation->getBound();
        $queryBuilder
            ->where('co.lat < :neLat')
            ->andWhere('co.lat > :swLat')
            ->andWhere('co.lng < :neLng')
            ->andWhere('co.lng > :swLng')
            ->setParameter('neLat', $viewport["ne"]["lat"])
            ->setParameter('swLat', $viewport["sw"]["lat"])
            ->setParameter('neLng', $viewport["ne"]["lng"])
            ->setParameter('swLng', $viewport["sw"]["lng"]);

        return $queryBuilder;
    }

    /**
     * @param ListingSearchRequest       $listingSearchRequest
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getSearchByDateQueryBuilder(ListingSearchRequest $listingSearchRequest, $queryBuilder)
    {
        //Dates availabilities (from MongoDB)
        $dateTimeRange = $listingSearchRequest->getDateTimeRange();
        $dateRange = $dateTimeRange->getDateRange();
        if ($dateRange && $dateRange->getStart() && $dateRange->getEnd()) {
            if ($this->listingDefaultStatus == ListingAvailability::STATUS_AVAILABLE) {
                //Get listings unavailable for searched dates
                $listingsUnavailable = $this->getListingsAvailability(
                    $dateTimeRange,
                    $listingSearchRequest->getFlexibility(),
                    null,
                    array(ListingAvailability::STATUS_UNAVAILABLE, ListingAvailability::STATUS_BOOKED)
                );
//                print_r($listingsUnavailable);

                if (count($listingsUnavailable)) {
                    $queryBuilder
                        ->andWhere('l.id NOT IN (:listingsUnavailable)')
                        ->setParameter('listingsUnavailable', array_keys($listingsUnavailable));
                }

            } else {//By default listing are unavailable
                //Get listings available for searched dates
                $listingsAvailable = $this->getListingsAvailability(
                    $dateTimeRange,
                    $listingSearchRequest->getFlexibility(),
                    null,
                    array(ListingAvailability::STATUS_AVAILABLE)
                );

                if (count($listingsAvailable)) {
                    $queryBuilder
                        ->andWhere('l.id IN (:listingsAvailable)')
                        ->setParameter('listingsAvailable', array_keys($listingsAvailable));
                } else {
                    $queryBuilder
                        ->andWhere('l.id IN (:listingsAvailable)')
                        ->setParameter('listingsAvailable', array(0));
                }
            }

            //Min/Max durations
            $duration = false;
            if ($this->timeUnitIsDay) {
                $duration = $dateRange->getDuration($this->endDayIncluded);
            } else {
                $timeRange = $dateTimeRange->getFirstTimeRange();
                if ($timeRange && $timeRange->getStart() && $timeRange->getEnd()) {
                    if ($timeRange->getStart()->format('H:i') !== $timeRange->getEnd()->format('H:i')
                        && ($timeRange->getStart()->format('H:i') != '00:00')
                    ) {
                        $duration = $timeRange->getDuration($this->timeUnit);
                    }
                }
            }

            if ($duration !== false) {
                $queryBuilder
                    ->andWhere(
                        "(l.minDuration IS NULL OR  l.minDuration <= :duration ) AND (l.maxDuration IS NULL OR l.maxDuration >= :duration)"
                    )
                    ->setParameter('duration', $duration);
            }
        }

        return $queryBuilder;
    }

    /**
     * @param ListingSearchRequest       $listingSearchRequest
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getSearchByCharacteristicsQueryBuilder(ListingSearchRequest $listingSearchRequest, $queryBuilder)
    {
        $characteristics = $listingSearchRequest->getCharacteristics();
        $characteristics = array_filter($characteristics);
        if (count($characteristics)) {
            $queryBuilderCharacteristics = $this->em->createQueryBuilder();
            $queryBuilderCharacteristics
                ->select('IDENTITY(c.listing)')
                ->from('CocoricoCoreBundle:ListingListingCharacteristic', 'c');

            foreach ($characteristics as $characteristicId => $characteristicValueId) {
                $queryBuilderCharacteristics
                    ->orWhere(
                        "( c.listingCharacteristic = :characteristic$characteristicId AND c.listingCharacteristicValue = :value$characteristicId )"
                    );

                $queryBuilder
                    ->setParameter("characteristic$characteristicId", $characteristicId)
                    ->setParameter("value$characteristicId", intval($characteristicValueId));
            }

            $queryBuilderCharacteristics
                ->groupBy('c.listing')
                ->having("COUNT(c.listing) = :nbCharacteristics");

            $queryBuilder
                ->setParameter("nbCharacteristics", count($characteristics));

            $queryBuilder
                ->leftJoin('l.listingListingCharacteristics', 'llc')
                ->andWhere(
                    $queryBuilder->expr()->in(
                        'l.id',
                        $queryBuilderCharacteristics->getDQL()
                    )
                );
        }

        return $queryBuilder;
    }

    /**
     * Get listings highest ranked
     *
     * @param ListingSearchRequest $listingSearchRequest
     * @param                      $limit
     * @param                      $locale
     * @return Paginator
     */
    public function getHighestRanked(ListingSearchRequest $listingSearchRequest, $limit, $locale)
    {
        $queryBuilder = $this->getRepository()->getFindByHighestRankingQueryBuilder($limit, $locale);

        $event = new ListingSearchEvent($listingSearchRequest, $queryBuilder);
        $this->dispatcher->dispatch(ListingSearchEvents::LISTING_SEARCH_HIGH_RANK_QUERY, $event);
        $queryBuilder = $event->getQueryBuilder();

        try {
            $query = $queryBuilder->getQuery();
            $query->setHydrationMode(Query::HYDRATE_ARRAY);
            $query->useResultCache(true, 21600, 'getHighestRanked');

            return new Paginator($query);//Important to manage limit
        } catch (NoResultException $e) {
            return null;
        }
    }


    /**
     * Get listings availabilities
     *
     * @param DateTimeRange    $dateTimeRange
     * @param  int             $flexibility in number of days
     * @param  PriceRange|null $priceRange
     * @param  array           $status
     *
     * @return int[] ListingId   array key = Id listing, value = number of times listing is available inside date ranges
     */
    public function getListingsAvailability(
        DateTimeRange $dateTimeRange,
        $flexibility,
        $priceRange = null,
        $status
    ) {
        $daysFlexibility = $flexibility ? $flexibility : 0;
        $dateRange = $dateTimeRange->getDateRange();

        //Create first date range from flexibility days
        $now = date('Ymd');
        $newStart = new \DateTime($dateRange->getStart()->format('Y-m-d'));
        $newStart->sub(new \DateInterval('P' . $daysFlexibility . 'D'));

        if ($this->endDayIncluded) {
            $newEnd = new \DateTime($dateRange->getEnd()->format('Y-m-d H:i'));
        } else {
            $newEnd = new \DateTime($dateRange->getEnd()->format('Y-m-d'));
        }
        $newEnd->sub(new \DateInterval('P' . $daysFlexibility . 'D'));
        $dateTimeRange->setDateRange(new DateRange($newStart, $newEnd));

        /** @var ListingAvailabilityRepository $listingAvailabilityRepository */
        $listingAvailabilityRepository = $this->dm->getRepository("CocoricoCoreBundle:ListingAvailability");
        $listings = array();
        $nbDateRanges = 0;

        //Verify if there are listings (un)available for all range dates defined by flexibility days
        for ($i = 0; $i <= $daysFlexibility * 2; $i++) {
            //Only for date range greater than or equal to today
            if ($newStart->format('Ymd') >= $now) {
                $nbDateRanges++;
                $listingsTmp = $listingAvailabilityRepository->findAvailabilities(
                    $dateTimeRange,
                    $status,
                    $priceRange,
                    $this->timeUnitIsDay
                );

//                echo "<br />" . $newStart->format('Y-m-d') . "/" . $newEnd->format('Y-m-d') . ":";
//                print_r($listingsTmp);

                /**
                 * All listings are (un)available for this date:
                 *
                 * If listing availability status searched is "unavailable" and default listings availability is "available" then
                 *  No listings unavailable for this date means all listing are available for this date
                 *  This condition is enough to return empty array meaning all listing are available for at least one date range
                 * Else if status searched is "available" and default listings availability is "unavailable" then
                 *  No listing available for this date and the search continue for next dates range
                 */
                if (!count($listingsTmp)) {
                    if (in_array(ListingAvailability::STATUS_UNAVAILABLE, $status)) {
                        $listings = array();
                        break;
                    }
                } else {//Merge all (un)available listings.
                    $listings = array_merge($listings, $listingsTmp);
                }
            }

            //Next date range
            $dateTimeRange->setDateRange(
                new DateRange($newStart->add(new \DateInterval('P1D')), $newEnd->add(new \DateInterval('P1D')))
            );
        }

        //Count number of unavailability by listing
        $listings = array_count_values($listings);

        if (in_array(ListingAvailability::STATUS_UNAVAILABLE, $status)) {
            //Get listings unavailable for all dates ranges
            $listings = array_diff($listings, range(0, ($nbDateRanges - 1)));
        }
//        else {
//            Get listings available for one of the dates ranges. $listings already contains them
//        }

//        print_r($listings);
        return $listings;
    }


    /**
     * getListingsByIds returns the listings, depending upon ids provided
     *
     * @param ListingSearchRequest $listingSearchRequest
     * @param array                $ids
     * @param int                  $page
     * @param string               $locale
     * @param array                $idsExcluded
     * @param int                  $maxPerPage
     *
     * @return Paginator|null
     */
    public function getListingsByIds(
        $listingSearchRequest,
        $ids,
        $page,
        $locale,
        array $idsExcluded = array(),
        $maxPerPage = null
    ) {
        // Remove the current listing id from the similar listings
        $ids = array_diff($ids, $idsExcluded);

        $queryBuilder = $this->getRepository()->getFindQueryBuilder();

        //Where
        $queryBuilder
            ->where('t.locale = :locale')
            ->andWhere('l.status = :listingStatus')
            ->andWhere('l.id IN (:ids)')
            ->setParameter('locale', $locale)
            ->setParameter('listingStatus', Listing::STATUS_PUBLISHED)
            ->setParameter('ids', $ids);

        $event = new ListingSearchEvent($listingSearchRequest, $queryBuilder);
        $this->dispatcher->dispatch(ListingSearchEvents::LISTING_SEARCH_BY_IDS_QUERY, $event);
        $queryBuilder = $event->getQueryBuilder();

        if ($maxPerPage === null) {
            //Pagination
            if ($page) {
                $queryBuilder->setFirstResult(($page - 1) * $this->maxPerPage);
            }

            $queryBuilder->setMaxResults($this->maxPerPage);
        }

        //Query
        $query = $queryBuilder->getQuery();

        $query->setHydrationMode(Query::HYDRATE_ARRAY);

        return new Paginator($query);
    }

    /**
     * @return int
     */
    public function getListingDefaultStatus()
    {
        return $this->listingDefaultStatus;
    }


    /**
     *
     * @return ListingRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('CocoricoCoreBundle:Listing');
    }

}
