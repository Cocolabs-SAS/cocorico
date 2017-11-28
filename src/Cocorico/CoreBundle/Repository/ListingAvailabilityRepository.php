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
    public function getAvailabilitiesByListingAndDateRange(
        $listingId,
        $start,
        $end,
        $endDayIncluded = false,
        $hydrate = true
    ) {
        $qbDM = $this->dm->createQueryBuilder('CocoricoCoreBundle:ListingAvailability');
        if (!$hydrate) {
            $qbDM->hydrate(false);
        }

        $qbDM
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
    public function getAvailabilitiesByDateTimeRangeAndStatusAndPrice(
        DateRange $dateRange,
        $timeRange,
        $status,
        $priceRange = null,
        $timeUnitIsDay
    ) {
        $start = $dateRange->getStart();
        $end = $dateRange->getEnd();

        //If price search
        $priceMin = $priceRange ? $priceRange->getMin() : false;
        $priceMax = $priceRange ? $priceRange->getMax() : false;

        $qbDM = $this->dm->createQueryBuilder('CocoricoCoreBundle:ListingAvailability');
        $qbDM
            ->hydrate(false)
            ->distinct('lId');

        if ($timeUnitIsDay) {
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
                    $periods = new \DatePeriod(
                        $start,
                        new \DateInterval('P1D'),
                        $end
                    );

                    /** @var \DateTime $period */
                    foreach ($periods as $period) {
                        $qbDM
                            ->field('day')->equals($period)
                            ->field('status')->in($status)
                            ->exists(true);
                    }
                }
            }
        } else {
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
                    $startMinute = intval($timeRange->getStart()->getTimestamp() / 60);
                    $endMinute = intval($timeRange->getEnd()->getTimestamp() / 60) - 1;
                    if ($timeRange->getEnd()->format('H:i') == '00:00') {//End minute is equal to 1440-1=1439 and not 0
                        $endMinute = 1439;
                    }
                    $qbDMDatesExp->field('times')->elemMatch(
                        $embeddedQbDM->expr()
                            ->field('id')->in(range($startMinute, $endMinute))
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
        }


        $result = $qbDM->getQuery()->execute()->toArray();

        return $result;
    }


}
