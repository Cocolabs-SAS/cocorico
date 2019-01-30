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
use Cocorico\CoreBundle\Model\PriceRange;
use Cocorico\TimeBundle\Model\DateTimeRange;
use Cocorico\TimeBundle\Model\TimeRange;
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
//        echo 'findAvailabilitiesByListing' . '<br>';
//        echo $start->format('Y-m-d H:i') . ' /' .  $end->format('Y-m-d H:i') . '<br>';

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
     * @param DateTimeRange   $dateTimeRange
     * @param array           $status
     * @param PriceRange|null $priceRange
     * @param boolean         $timeUnitIsDay
     *
     * @return \Cocorico\CoreBundle\Document\ListingAvailability[]
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function findAvailabilities(
        DateTimeRange $dateTimeRange,
        $status,
        $priceRange = null,
        $timeUnitIsDay
    ) {
        //If price search
        $priceMin = $priceRange ? $priceRange->getMin() : false;
        $priceMax = $priceRange ? $priceRange->getMax() : false;

        if ($timeUnitIsDay) {
            $result = $this->findAvailabilitiesInDayMode($dateTimeRange, $status, $priceMin, $priceMax);
        } else {
            $result = $this->findAvailabilitiesInNoDayMode(
                $dateTimeRange,
                $status,
                $priceMin,
                $priceMax
            );
        }


        return $result;
    }

    /**
     * @param DateTimeRange                       $dateTimeRange
     * @param                                     $status
     * @param                                     $priceMin
     * @param                                     $priceMax
     *
     * @return array
     */
    private function findAvailabilitiesInDayMode(DateTimeRange $dateTimeRange, $status, $priceMin, $priceMax)
    {
        $qbDM = $this->dm->createQueryBuilder('CocoricoCoreBundle:ListingAvailability');
        $qbDM
            ->hydrate(false)
            ->distinct('lId');

        $start = $dateTimeRange->getDateRange()->getStart();
        $end = $dateTimeRange->getDateRange()->getEnd();

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
     * @param DateTimeRange                       $dateTimeRange
     * @param                                     $status
     * @param                                     $priceMin
     * @param                                     $priceMax
     *
     * @return array
     */
    private function findAvailabilitiesInNoDayMode(
        DateTimeRange $dateTimeRange,
        $status,
        $priceMin,
        $priceMax
    ) {
        $result = array();

        $qbDM = $this->dm->createQueryBuilder('CocoricoCoreBundle:ListingAvailability');
        $qbDM
            ->hydrate(false)
            ->distinct('lId');

        $daysTimeRanges = $dateTimeRange->getDaysTimeRanges(true);

        foreach ($daysTimeRanges as $dayTimeRanges) {
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
                    if (in_array(ListingAvailability::STATUS_UNAVAILABLE, $status)) {
                        //search listings unavailable
                    $qbDMDatesExp->field('times')->elemMatch(
                        $embeddedQbDM->expr()
                            ->field('id')->in(range($timeRange->getStartMinute(), $timeRange->getEndMinute() - 1))
                            ->field('status')->in($status)
                    );
                    } else {
                        //Search listings available
                        //Search listings not having at least one minute unavailable in the time range
                        //todo: handle search time range  between existing minutes and non existing minutes
                        $qbDMDatesExp
                            ->addAnd(
                                $qbDMDatesExp->field('times')->elemMatch(
                                    $embeddedQbDM->expr()
                                        ->field('id')->in(
                                            range($timeRange->getStartMinute(), $timeRange->getEndMinute() - 1)
                                        )
                                )
                            )
                            ->addAnd(
                                $qbDMDatesExp->field('times')->not(
                                    $embeddedQbDM->expr()->elemMatch(
                                        $embeddedQbDM->expr()
                                            ->field('id')->in(
                                                range($timeRange->getStartMinute(), $timeRange->getEndMinute() - 1)
                                            )
                                            ->field('status')->in(
                                                array(
                                                    ListingAvailability::STATUS_UNAVAILABLE,
                                                    ListingAvailability::STATUS_BOOKED
                                                )
                                            )
                                    )
                                )
                            );


//                        //Interesting solutions to search in array elements on multiple fields (id=1 and s=1, id=2 and s=1, ...)
//                        $criterias = array();

//                        //First solution by object
//                        $startMinutes = range($timeRange->getStartMinute(), $timeRange->getEndMinute() - 1);
//                        foreach ($startMinutes as $i => $startMinute) {
//                            # Add expression as a sub query to array
//                            $criterias[] = $embeddedQbDM->expr()->elemMatch(
//                                $embeddedQbDM->expr()
//                                    ->field('id')->equals($startMinute)
//                                    ->field('status')->equals(ListingAvailability::STATUS_AVAILABLE)
//                            )->getQuery();
//                        }
//                        $qbDMDatesExp->field('times')->all($criterias);

//                        //Second solution by array
//                        $startMinutes = range($timeRange->getStartMinute(), $timeRange->getEndMinute() - 1);
//                        foreach ($startMinutes as $startMinute) {
//                            $criterias[] = array(
//                                '$elemMatch' => array(
//                                    "_id" => $startMinute,
//                                    "s" => ListingAvailability::STATUS_AVAILABLE
//                                )
//                            );
//                        }
//                        $qbDMDatesExp->field('times')->all($criterias);
                    }

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
