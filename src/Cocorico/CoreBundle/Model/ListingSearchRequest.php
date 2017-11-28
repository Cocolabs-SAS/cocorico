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
    //todo: decouple category fields and delivery
    protected $categoriesFields;
    protected $delivery;

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

        //Set date range and time range only while listing search
        if ($this->request && $this->request->get('_route') == 'cocorico_listing_search_result') {
            $this->setDateRange(DateRange::createFromArray($this->request->query->get("date_range")));
            $this->setTimeRange(TimeRange::createFromArray($this->request->query->get("time_range")));
        }

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

        //Categories fields
        $this->categoriesFields = array();
        $categoriesFields = $this->request->query->get("categories_fields");
        if (is_array($categoriesFields)) {
            $this->categoriesFields = $categoriesFields;
        }

        //Characteristics
        $this->characteristics = array();
        $characteristics = $this->request->query->get("characteristics");
        if (is_array($characteristics)) {
            $this->characteristics = $characteristics;
        }

        $this->setSimilarListings(array());

        //Delivery
        $delivery = $this->request->query->get("delivery");
        if ($delivery) {
            $this->delivery = $delivery;
        }
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
    public function setDateRange(DateRange $dateRange = null)
    {
        $this->dateRange = $dateRange;
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
    public function setTimeRange(TimeRange $timeRange = null)
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
     * @return bool
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * @param bool $delivery
     */
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * @return array
     */
    public function getCategoriesFields()
    {
        return $this->categoriesFields;
    }

    /**
     * @param array $categoriesFields
     */
    public function setCategoriesFields($categoriesFields)
    {
        $this->categoriesFields = $categoriesFields;
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
