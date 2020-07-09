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
use Cocorico\CoreBundle\Entity\Quote;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingDiscount;
use Cocorico\CoreBundle\Event\QuoteEvent;
use Cocorico\CoreBundle\Event\QuoteEvents;
use Cocorico\CoreBundle\Mailer\TwigSwiftMailer;
use Cocorico\CoreBundle\Repository\QuoteRepository;
use Cocorico\CoreBundle\Repository\ListingAvailabilityRepository;
use Cocorico\CoreBundle\Repository\ListingDiscountRepository;
use Cocorico\SMSBundle\Twig\TwigSmser;
use Cocorico\TimeBundle\Model\DateTimeRange;
use Cocorico\TimeBundle\Model\TimeRange;
use Cocorico\UserBundle\Entity\User;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class QuoteManager extends BaseManager
{
    protected $em;
    protected $dm;
    protected $availabilityManager;
    protected $mailer;
    protected $smser;
    protected $dispatcher;
    protected $defaultListingStatus;
    protected $bundles;
    public $maxPerPage;

    /**
     * @param EntityManager              $em
     * @param DocumentManager            $dm
     * @param ListingAvailabilityManager $availabilityManager
     * @param TwigSwiftMailer            $mailer
     * @param TwigSmser|null             $smser
     * @param EventDispatcherInterface   $dispatcher
     * @param array                      $parameters
     *        float     $feeAsAsker
     *        float     $feeAsOfferer
     *        boolean   $endDayIncluded
     *        int       $timeUnit App time unit includeVat
     *        int       $timesMax Max times unit if time_unit includeVat
     *        array     $hoursAvailable
     *        int       $minStartTimeDelay
     *        int       $minPrice
     *        int       $maxPerPage
     *        int       $defaultListingStatus
     *        float     $vatRate
     *        bool      $includeVat
     *
     * todo: decouple sms bundle by dispatching event each time is used
     */
    public function __construct(
        EntityManager $em,
        DocumentManager $dm,
        ListingAvailabilityManager $availabilityManager,
        TwigSwiftMailer $mailer,
        $smser,
        EventDispatcherInterface $dispatcher,
        $parameters
    ) {
        $this->em = $em;
        $this->dm = $dm;
        $this->availabilityManager = $availabilityManager;
        $this->mailer = $mailer;
        $this->smser = $smser;
        $this->dispatcher = $dispatcher;

        //Parameters
        $parameters = $parameters["parameters"];

        $this->maxPerPage = $parameters["cocorico_dashboard_max_per_page"];
        $this->defaultListingStatus = $parameters["cocorico_listing_availability_status"];
        $this->bundles = $parameters["cocorico_bundles"];
    }

    /**
     * Pre-set new Quote based data.
     *
     * @param Listing       $listing
     * @param User|null     $user
     * @param DateTimeRange $dateTimeRange
     * @return Quote
     */
    public function initQuote(Listing $listing, $user)
    {
        $quote = new Quote();
        $quote->setListing($listing);
        $quote->setUser($user);
        $quote->setStatus(Quote::STATUS_DRAFT);

        return $quote;
    }

    /**
     * @param int    $askerId
     * @param string $locale
     * @param int    $page
     * @param array  $status
     *
     * @return Paginator
     */
    public function findByAsker($askerId, $locale, $page, $status = array())
    {
        $queryBuilder = $this->getRepository()->getFindByAskerQuery($askerId, $locale, $status);

        //Pagination
        $queryBuilder
            ->setFirstResult(($page - 1) * $this->maxPerPage)
            ->setMaxResults($this->maxPerPage);

        //Query
        $query = $queryBuilder->getQuery();

        return new Paginator($query);
    }

    /**
     * @param  int    $offererId
     * @param  string $locale
     * @param  int    $page
     * @param  array  $status
     * @return Paginator
     */
    public function findByOfferer($offererId, $locale, $page, $status = array())
    {
        $queryBuilder = $this->getRepository()->getFindByOffererQuery($offererId, $locale, $status);

        //Pagination
        $queryBuilder
            ->setFirstResult(($page - 1) * $this->maxPerPage)
            ->setMaxResults($this->maxPerPage);

        //Query
        $query = $queryBuilder->getQuery();

        return new Paginator($query);
    }

    /**
     * @param int    $id
     * @param int    $askerId
     * @param string $locale
     * @param array  $status
     *
     * @return Quote|null
     *
     * @throws NonUniqueResultException
     */
    public function findOneByAsker($id, $askerId, $locale, $status = array())
    {
        $queryBuilder = $this->getRepository()->getFindOneByAskerQuery($id, $askerId, $locale, $status);

        $query = $queryBuilder->getQuery();

        return $query->getOneOrNullResult();
    }


    /**
     * Create a new quote
     *
     * @param Quote $quote
     * @return Quote|false
     */
    public function create(Quote $quote)
    {
        if (in_array($quote->getStatus(), Quote::$newableStatus)) {
            //New Quote confirmation
            $quote->setStatus(Quote::STATUS_NEW);

            $quote->setTimeZoneAsker($quote->getUser()->getTimeZone());
            $quote->setTimeZoneOfferer($quote->getListing()->getUser()->getTimeZone());

            $quote = $this->save($quote);

            // $this->mailer->sendQuoteRequestMessageToOfferer($quote);
            // $this->mailer->sendQuoteRequestMessageToAsker($quote);

            if ($this->smser) {
                $this->smser->sendQuoteRequestMessageToOfferer($quote);
            }

            return $quote;
        }

        return false;
    }

    /**
     * Return whether a quote can be canceled by asker
     *
     * @param Quote $quote
     *
     * @return bool
     */
    public function canBeCanceledByAsker(Quote $quote)
    {
        $statusIsOk = in_array($quote->getStatus(), Quote::$cancelableStatus);

        if ($statusIsOk && !$quote->isValidated()) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Return whether a quote can be accepted or refused by offerer
     * A quote can be accepted or refused no later than $acceptationDelay hours before it starts
     * and no later than $expirationDelay hours after new quote request date
     *
     * @param Quote $quote
     *
     * @return bool
     */
    public function canBeAcceptedOrRefusedByOfferer(Quote $quote)
    {
        //$refusableStatus is equal to $payableStatus
        $statusIsOk = in_array($quote->getStatus(), Quote::$cancelableStatus);

        return $statusIsOk;
        /*
        $isNotExpired = $quote->getTimeBeforeExpiration(
            $this->expirationDelay,
            $this->acceptationDelay
        );
        $isNotExpired = $isNotExpired && $isNotExpired > 0;

        return $statusIsOk && $isNotExpired;
        */
    }

    /**
     * Offerer refuse quote :
     *  Set quote status as refused
     *  Send mails
     *
     * @param Quote $quote
     * @param bool    $refusedByOfferer
     *
     * @return Quote|bool
     */
    public function refuse(Quote $quote, $refusedByOfferer = true)
    {
        $canBeAcceptedOrRefused = $this->canBeAcceptedOrRefusedByOfferer($quote);
        if ($canBeAcceptedOrRefused) {
            $quote->setStatus(Quote::STATUS_REFUSED);
            $quote->setRefusedQuoteAt(new DateTime());
            $quote = $this->save($quote);

            $this->mailer->sendQuoteRefusedMessageToAsker($quote);
            if ($refusedByOfferer) {
                $this->mailer->sendQuoteRefusedMessageToOfferer($quote);
            }

            if ($this->smser) {
                $this->smser->sendQuoteRefusedMessageToAsker($quote);
            }

            return $quote;
        }

        return false;
    }

    /**
     * Asker cancel quote.
     *  There are two cases:
     *      Either the quote has not been accepted by the offerer and so not already payed. Its status is new and
     *          no refund need to be made.
     *      Either quote status is payed and is not already validated. In this case the funds are in the asker wallet
     *      and must be refunded to his bank account.
     *
     *
     * Operations:
     *  Optionally refund asker
     *  Set quote status as cancel
     *  Send mails
     *
     * @param Quote $quote
     *
     * @return Quote|bool
     */
    public function cancel(Quote $quote)
    {
        if ($this->canBeCanceledByAsker($quote)) {
            $cancelable = false;

            if ($quote->getStatus() == Quote::STATUS_NEW) {
                $cancelable = true;
            }

            if ($cancelable) {
                $quote->setStatus(Quote::STATUS_CANCELED_ASKER);
                $quote->setCanceledAskerQuoteAt(new DateTime());
                $quote = $this->save($quote);

                // $this->mailer->sendQuoteCanceledByAskerMessageToAsker($quote);
                // $this->mailer->sendQuoteCanceledByAskerMessageToOfferer($quote);

                if ($this->smser) {
                    $this->smser->sendQuoteCanceledByAskerMessageToOfferer($quote);
                }


                return $quote;
            }
        }

        return false;
    }

    /**
     * @param Quote $quote
     * @return Quote
     */
    public function save(Quote $quote)
    {
        $this->persistAndFlush($quote);

        return $quote;
    }

    /**
     * @return TwigSwiftMailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     *
     * @return QuoteRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('CocoricoCoreBundle:Quote');
    }

    /**
     *
     * @return ListingAvailabilityRepository
     */
    protected function getAvailabilityRepository()
    {
        return $this->dm->getRepository('CocoricoCoreBundle:ListingAvailability');
    }

    /**
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }
}
