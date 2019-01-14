<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Repository;

use Cocorico\CoreBundle\Document\ListingAvailability;
use Cocorico\CoreBundle\Model\DateRange;
use Cocorico\CoreBundle\Model\PriceRange;
use Cocorico\CoreBundle\Model\TimeRange;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentRepository;


class ListingAvailabilityRepository extends DocumentRepository
{
    /**
     *
     * Get all ListingAvailability documents by date range and listing
     *
     * @param int       $listingId
     * @param \DateTime $start
     * @param \DateTime $end
     * @param boolean   $endDayIncluded
     * @param boolean   $hydrate
     *
     * @return ArrayCollection|ListingAvailability[]
     */
    public function findAvailabilitiesByListing(
        $listingId,
        $start,
        $end,
        $endDayIncluded = false,
        $hydrate = true
    ) {
        $qbDM = $this->dm->createQueryBuilder('CocoricoCoreBundle:ListingAvailability')
            ->hydrate($hydrate)
            ->select('day', 'status', 'price', 'times', 'listingId')
            ->field('listingId')->equals(intval($listingId))
            ->field('day')->gte(new \MongoDate($start->getTimestamp()));

        if (!$endDayIncluded) {
            $qbDM->field('day')->lt(new \MongoDate($end->getTimestamp()));
        } else {
            $qbDM->field('day')->lte(new \MongoDate($end->getTimestamp()));
        }

        $qbDM
            ->sort(
                array(
                    'day' => 'asc',
                )
            );

        //print_r($qbDM->getQuery()->debug());
        return $qbDM->getQuery()->execute();
    }

    /**
     * @param DateRange         $dateRange
     * @param TimeRange|boolean $timeRange
     * @param array             $status
     * @param PriceRange|null   $priceRange
     * @param boolean           $timeUnitIsDay
     *
     * @return \Cocorico\CoreBundle\Document\ListingAvailability[]
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findAvailabilities(
        DateRange $dateRange,
        $timeRange,
        $status,
        $priceRange = null,
        $timeUnitIsDay
    ) {
        //If price search
        $priceMin = $priceRange ? $priceRange->getMin() : false;
        $priceMax = $priceRange ? $priceRange->getMax() : false;

        if ($timeUnitIsDay) {
            $result = $this->findAvailabilitiesInDayMode($dateRange, $status, $priceMin, $priceMax);
        } else {
            $result = $this->findAvailabilitiesInNoDayMode(
                $dateRange,
                $timeRange,
                $status,
                $priceMin,
                $priceMax
            );
        }


        return $result;
    }

    /**
     * @param DateRange                           $dateRange
     * @param                                     $status
     * @param                                     $priceMin
     * @param                                     $priceMax
     *
     * @return array
     */
    private function findAvailabilitiesInDayMode(DateRange $dateRange, $status, $priceMin, $priceMax)
    {
        $qbDM = $this->dm->createQueryBuilder('CocoricoCoreBundle:ListingAvailability');
        $qbDM
            ->hydrate(false)
            ->distinct('lId');

        $start = $dateRange->getStart();
        $end = $dateRange->getEnd();

        if (is_numeric($priceMin) && $priceMin && is_numeric($priceMax) && $priceMax) {//not use for now
            //Unavailable or not in price range
        } else {
            //If listing availability status searched is "unavailable" then
            //we search listings having at least one date unavailable in date range
            if (in_array(ListingAvailability::STATUS_UNAVAILABLE, $status)) {
                $qbDM
                    ->field('day')->range($start, $end)
                    ->field('status')->in($status);
            }//Else we search listings having all dates available for date range
            else {
                $periods = new \DatePeriod($start, new \DateInterval('P1D'), $end);

                /** @var \DateTime $period */
                foreach ($periods as $period) {
                    $qbDM
                        ->field('day')->equals($period)
                        ->field('status')->in($status)
                        ->exists(true);
                }
            }
        }

        return $qbDM->getQuery()->execute()->toArray();
    }


    /**
     * @param DateRange                           $dateRange
     * @param TimeRange                           $timeRange
     * @param                                     $status
     * @param                                     $priceMin
     * @param                                     $priceMax
     *
     * @return array
     */
    private function findAvailabilitiesInNoDayMode(
        DateRange $dateRange,
        TimeRange $timeRange = null,
        $status,
        $priceMin,
        $priceMax
    ) {
        $result = array();

        $qbDM = $this->dm->createQueryBuilder('CocoricoCoreBundle:ListingAvailability');
        $qbDM
            ->hydrate(false)
            ->distinct('lId');

        $timeRange = $timeRange ? array($timeRange) : array();
        $daysTimeRanges = $dateRange->getTimeRangesByDay($timeRange, true, false);

        foreach ($daysTimeRanges as $index => $dayTimeRanges) {
            /** @var \DateTime $start */
            $start = $dayTimeRanges->day;
            $end = clone $start;
            $end->setTime(23, 59, 59);
            /** @var TimeRange $timeRange */
            $timeRange = reset($dayTimeRanges->timeRanges);

            if (is_numeric($priceMin) && $priceMin && is_numeric($priceMax) && $priceMax) {//not use for now
                //(Un)available or not in price range
            } else {//(Un)available
                //TODO: CHECK LISTING DEFAULT STATUS = UNAVAILABLE
                $qbDMDatesExp = $qbDM
                    ->expr()
                    ->field('day')->range($start, $end);

                //Embedded documents require their own query builders to search on their fields
                $embeddedQbDM = $this->dm->createQueryBuilder('CocoricoCoreBundle:ListingAvailabilityTime');

                if ($timeRange && $timeRange->getStart() && $timeRange->getEnd()) {
                    $qbDMDatesExp->field('times')->elemMatch(
                        $embeddedQbDM->expr()
                            ->field('id')->in(range($timeRange->getStartMinute(), $timeRange->getEndMinute() - 1))
                            ->field('status')->in($status)
                    );
                } else {
                    //No time unit in the day is available
                    if (in_array(ListingAvailability::STATUS_UNAVAILABLE, $status)) {
                        $startMinutes = range(0, 1360, 60);//each first minute of each hour of the day
                        foreach ($startMinutes as $startMinute) {
                            $qbDMDatesExp->field('times.' . $startMinute . '.s')->in($status);
                        }
                    } //At least one time unit in the day is available
                    else {
                        $qbDMDatesExp->field('times')->elemMatch(
                            $embeddedQbDM->expr()
                                ->field('status')->in($status)
                        );
                    }
                }

                $qbDM
                    ->addOr(
                        $qbDMDatesExp
                    )
                    ->addOr(
                        $qbDM
                            ->expr()
                            ->field('day')->range($start, $end)
                            ->field('times')->size(0)
                            ->field('status')->in($status)
                    )->addOr(
                        $qbDM
                            ->expr()
                            ->field('day')->range($start, $end)
                            ->field('times')->exists(false)
                            ->field('status')->in($status)
                    );
            }

            $result = array_merge($result, $qbDM->getQuery()->execute()->toArray());
        }

//        echo print_r($result, 1) . '<br>';
        //Todo: if listing status is by default available then listing id must be in one of the $result else listing id must be in all $result
        //Count number of unavailability by listing
//        $result = array_count_values($result);
//
//        if (in_array(ListingAvailability::STATUS_AVAILABLE, $status)) {
//            //Get listings unavailable for all dates ranges
//            $result = array_diff($result, range(0, (count($daysTimeRanges) - 1)));
//        } else {
//            //Get listings available for one of the dates ranges. $listings already contains them
//        }

        return $result;

    }
}
