<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Model;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ListingSearchRequest
 *
 * Represent the listing search request
 *
 */
class ListingSearchRequest implements TranslationContainerInterface
{
    protected $location;
    protected $categories;
    protected $characteristics;
    protected $dateRange;
    protected $timeRange;
    //Number of flexibility days
    protected $flexibility;
    protected $priceRange;
    protected $sortBy;
    protected $page;
    protected $maxPerPage;
    /** @var RequestStack requestStack */
    protected $requestStack;
    /** @var Request request */
    protected $request;
    protected $similarListings;
    protected $locale;

    public static $sortByValues = array(
        'recommended' => 'listing.search.sort_by.recommended',
        'price' => 'listing.search.sort_by.price',
        'distance' => 'listing.search.sort_by.distance'
    );

    /**
     * @param RequestStack $requestStack
     * @param int          $maxPerPage
     */
    public function __construct(RequestStack $requestStack, $maxPerPage)
    {
        //Params
        $this->requestStack = $requestStack;
        $this->request = $this->requestStack->getCurrentRequest();
        if ($this->request) {
            $this->locale = $this->request->getLocale();
        }

        $this->maxPerPage = $maxPerPage;
        $this->page = 1;

        //Init date range
        $dateRange = $this->request->query->get("date_range");
        $timeRange = $this->request->query->get("time_range");
        $this->refreshData($dateRange, $timeRange);

        //Flexibility
        $this->flexibility = 0;

        //Price
        $this->priceRange = new PriceRange();

        //Location
        $this->location = new ListingLocationSearchRequest($this->locale);

        //Categories
        $this->categories = array();
        $categories = $this->request->query->get("categories");
        if (is_array($categories)) {
            $this->categories = $categories;
        }

        //Characteristics
        $this->characteristics = array();
        $characteristics = $this->request->query->get("characteristics");
        if (is_array($characteristics)) {
            $this->characteristics = $characteristics;
        }

        $this->setSimilarListings(array());
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param mixed $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return DateRange
     */
    public function getDateRange()
    {
        return $this->dateRange;
    }

    /**
     * @param DateRange $dateRange
     */
    public function setDateRange(DateRange $dateRange)
    {
        $this->dateRange = $dateRange;
    }

    /**
     * @return ListingLocationSearchRequest
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }


    /**
     * @return mixed
     */
    public function getCharacteristics()
    {
        return $this->characteristics;
    }

    /**
     * @param mixed $characteristics
     */
    public function setCharacteristics($characteristics)
    {
        $this->characteristics = $characteristics;
    }

    /**
     * @return mixed
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param mixed $sortBy
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return mixed
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * @param mixed $maxPerPage
     */
    public function setMaxPerPage($maxPerPage)
    {
        $this->maxPerPage = $maxPerPage;
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        foreach (self::$sortByValues as $key => $sortByValue) {
            $messages[] = new Message($sortByValue, 'cocorico_listing');
        }

        return $messages;
    }

    /**
     * @return TimeRange
     */
    public function getTimeRange()
    {
        return $this->timeRange;
    }

    /**
     * @param TimeRange $timeRange
     */
    public function setTimeRange($timeRange)
    {
        $this->timeRange = $timeRange;
    }

    /**
     * @return mixed
     */
    public function getFlexibility()
    {
        return $this->flexibility;
    }

    /**
     * @param mixed $flexibility
     */
    public function setFlexibility($flexibility)
    {
        $this->flexibility = $flexibility;
    }

    /**
     * @return PriceRange
     */
    public function getPriceRange()
    {
        return $this->priceRange;
    }

    /**
     * @param PriceRange $priceRange
     */
    public function setPriceRange($priceRange)
    {
        $this->priceRange = $priceRange;
    }

    /**
     * @return int[]
     */
    public function getSimilarListings()
    {
        return $this->similarListings;
    }

    /**
     * @param int[] $similarListings
     */
    public function setSimilarListings($similarListings)
    {
        $this->similarListings = $similarListings;
    }

    /**
     * Refresh dates and eventually times Listing Search Request Parameters and memorize them in session
     *
     * @param array $dateRangeParameter
     * @param array $timeRangeParameter
     * @return $this
     */
    public function refreshData($dateRangeParameter, $timeRangeParameter)
    {
        if (isset($dateRangeParameter['start']) && isset($dateRangeParameter['end']) &&
            $dateRangeParameter['start'] && $dateRangeParameter['end']
        ) {
            $start = \DateTime::createFromFormat('d/m/Y', $dateRangeParameter['start']);
            $end = \DateTime::createFromFormat('d/m/Y', $dateRangeParameter['end']);

            $dateRange = new DateRange(
                new \DateTime($start->format('Y-m-d')),
                new \DateTime($end->format('Y-m-d'))
            );
            $this->setDateRange($dateRange);
        }

        if (isset($timeRangeParameter['start']) && isset($timeRangeParameter['end']) &&
            is_numeric($timeRangeParameter['start']['hour']) && is_numeric($timeRangeParameter['end']['hour']) &&
            is_numeric($timeRangeParameter['start']['minute']) && is_numeric($timeRangeParameter['end']['minute'])
        ) {
            $timeRange = new TimeRange(
                new \DateTime(
                    "1970-01-01 " . $timeRangeParameter['start']['hour'] . ":" . $timeRangeParameter['start']['minute']
                ),
                new \DateTime(
                    "1970-01-01 " . $timeRangeParameter['end']['hour'] . ":" . $timeRangeParameter['end']['minute']
                )
            );

            $this->setTimeRange($timeRange);
        } else {
            $this->setTimeRange(null);
        }

        return $this;
    }


    /**
     * Remove some Object properties while serialisation
     *
     * @return array
     */
    public function __sleep()
    {
        return array_diff(array_keys(get_object_vars($this)), array('requestStack', 'request'));
    }
}
