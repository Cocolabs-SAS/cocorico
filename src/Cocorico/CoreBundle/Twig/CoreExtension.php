<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\CoreBundle\Twig;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Helper\GlobalHelper;
use Lexik\Bundle\CurrencyBundle\Twig\Extension\CurrencyExtension;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Translation\TranslatorInterface;

class CoreExtension extends \Twig_Extension
{
    protected $currencyExtension;
    protected $translator;
    protected $locales;
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $daysDisplayMode;
    protected $timesDisplayMode;
    protected $timeUnitFlexibility;
    protected $timeUnitAllDay;
    protected $allowSingleDay;
    protected $endDayIncluded;
    protected $listingDefaultStatus;
    protected $listingPricePrecision;
    protected $currencies;
    protected $defaultCurrency;
    protected $currentCurrency;
    protected $priceMin;
    protected $priceMax;
    protected $feeAsOfferer;
    protected $feeAsAsker;
    protected $displayMarker;
    protected $session;
    protected $bookingExpirationDelay;
    protected $bookingValidationMoment;
    protected $bookingValidationDelay;
    protected $bookingPriceMin;
    protected $globalHelper;
    protected $vatRate;
    protected $includeVat;
    protected $displayVat;
    protected $listingSearchMinResult;
    protected $listingDuplication;
    protected $minStartDelay;
    protected $minStartTimeDelay;

    /**
     *
     * @param CurrencyExtension   $currencyExtension
     * @param TranslatorInterface $translator
     * @param array               $locales
     * @param int                 $timeUnit                App unit time in minutes
     * @param boolean             $timeUnitFlexibility
     * @param boolean             $timeUnitAllDay
     * @param string              $daysDisplayMode
     * @param string              $timesDisplayMode
     * @param boolean             $allowSingleDay
     * @param boolean             $endDayIncluded
     * @param int                 $listingDefaultStatus
     * @param int                 $listingPricePrecision
     * @param array               $currencies
     * @param string              $defaultCurrency
     * @param string              $priceMin
     * @param string              $priceMax
     * @param float               $feeAsOfferer
     * @param float               $feeAsAsker
     * @param boolean             $displayMarker
     * @param Session             $session
     * @param int                 $bookingExpirationDelay  Delay to expire a new booking in minute
     * @param string              $bookingValidationMoment Moment when the booking is validated (start or end)
     * @param int                 $bookingValidationDelay  Delay in minutes after or before $bookingValidationMoment
     * @param int                 $bookingPriceMin
     * @param GlobalHelper        $globalHelper
     * @param float               $vatRate
     * @param bool                $includeVat
     * @param bool                $displayVat
     * @param int                 $listingSearchMinResult
     * @param bool                $listingDuplication
     * @param int                 $minStartDelay
     * @param int                 $minStartTimeDelay
     *
     */

    public function __construct(
        $currencyExtension,
        $translator,
        $locales,
        //time unit
        $timeUnit,
        $timeUnitFlexibility,
        $timeUnitAllDay,
        $daysDisplayMode,
        $timesDisplayMode,
        $allowSingleDay,
        $endDayIncluded,
        $listingDefaultStatus,
        $listingPricePrecision,
        //Currencies
        $currencies,
        $defaultCurrency,
        //Prices
        $priceMin,
        $priceMax,
        $feeAsOfferer,
        $feeAsAsker,
        $displayMarker,
        Session $session,
        //Delay
        $bookingExpirationDelay,
        $bookingValidationMoment,
        $bookingValidationDelay,
        $bookingPriceMin,
        GlobalHelper $globalHelper,
        $vatRate,
        $includeVat,
        $displayVat,
        $listingSearchMinResult,
        $listingDuplication,
        $minStartDelay,
        $minStartTimeDelay
    ) {
        $this->currencyExtension = $currencyExtension;
        $this->translator = $translator;
        $this->locales = $locales;
        //Time unit
        $this->timeUnit = $timeUnit;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
        $this->timeUnitAllDay = $timeUnitAllDay;
        $this->daysDisplayMode = $daysDisplayMode;
        $this->timesDisplayMode = $timesDisplayMode;
        $this->timeUnitFlexibility = $timeUnitFlexibility;

        $this->allowSingleDay = $allowSingleDay;
        $this->endDayIncluded = $endDayIncluded;

        $this->listingDefaultStatus = $listingDefaultStatus;
        $this->listingPricePrecision = $listingPricePrecision;

        //Currencies
        $this->currencies = $currencies;
        $this->defaultCurrency = $defaultCurrency;
        $this->currentCurrency = $session->get('currency', $defaultCurrency);

        //Prices
        $this->priceMin = $priceMin;
        $this->priceMax = $priceMax;
        $this->feeAsOfferer = $feeAsOfferer;
        $this->feeAsAsker = $feeAsAsker;

        $this->displayMarker = $displayMarker;
        $this->session = $session;

        //Delay
        $this->bookingExpirationDelay = $bookingExpirationDelay * 60;//Converted to seconds
        $this->bookingValidationMoment = $bookingValidationMoment;
        $this->bookingValidationDelay = $bookingValidationDelay;
        $this->bookingPriceMin = $bookingPriceMin;

        $this->globalHelper = $globalHelper;
        $this->vatRate = $vatRate;
        $this->includeVat = $includeVat;
        $this->displayVat = $displayVat;
        $this->listingSearchMinResult = $listingSearchMinResult;
        $this->listingDuplication = $listingDuplication;
        $this->minStartDelay = $minStartDelay;
        $this->minStartTimeDelay = $minStartTimeDelay;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('repeat', array($this, 'stringRepeatFilter')),
            new \Twig_SimpleFilter('format_seconds', array($this, 'formatSecondsFilter')),
            new \Twig_SimpleFilter('add_time_unit_text', array($this, 'addTimeUnitTextFilter')),
            new \Twig_SimpleFilter('ucwords', 'ucwords'),
            new \Twig_SimpleFilter('format_price', array($this, 'formatPriceFilter'))
        );
    }

    /**
     * @param $input
     * @param $multiplier
     * @return string
     */
    public function stringRepeatFilter($input, $multiplier)
    {
        return str_repeat($input, $multiplier);
    }

    /**
     * Format time from seconds to unit
     *
     * @param int    $seconds
     * @param string $format
     *
     * @return string
     */
    public function formatSecondsFilter($seconds, $format = 'dhm')
    {
        $time = $this->globalHelper->secondsToTime($seconds);
        switch ($format) {
            case 'h':
                $result = ($time['d'] * 24) + $time['h'] . "h";
                break;
            default:
                $result = ($time['d'] * 24) + $time['h'] . "h " . $time['m'] . "m";
        }

        return $result;
    }

    /**
     * Add unit time text to duration value
     *
     * @param int    $duration
     * @param string $locale
     * @return string
     */
    public function addTimeUnitTextFilter($duration, $locale = null)
    {
        if ($this->timeUnitIsDay) {
            if ($this->timeUnitAllDay) {
                return $this->translator->transChoice(
                    'time_unit_day',
                    $duration,
                    array('%count%' => $duration),
                    'cocorico',
                    $locale
                );
            } else {
                return $this->translator->transChoice(
                    'time_unit_night',
                    $duration,
                    array('%count%' => $duration),
                    'cocorico',
                    $locale
                );
            }
        } else {
            return $this->translator->transChoice(
                'time_unit_hour',
                $duration,
                array('%count%' => $duration),
                'cocorico',
                $locale
            );
        }
    }

    /**
     * @param int    $price
     * @param string $locale
     * @param int    $precision
     * @param bool   $convert
     * @return string
     */
    public function formatPriceFilter($price, $locale, $precision = null, $convert = true)
    {
        if (is_null($precision)) {
            $precision = $this->listingPricePrecision;
        }

        $targetCurrency = $this->currentCurrency;
        if (!$convert) {
            $targetCurrency = $this->defaultCurrency;
        }

        $this->currencyExtension->getFormatter()->setLocale($locale);
        if ($price > 0) {
            $price = $this->currencyExtension->convert($price, $targetCurrency, !$precision);
        } else {
            $price = 0;
        }

        $price = $this->currencyExtension->format($price, $targetCurrency, $precision);


        return $price;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'session_upload_progress_name', function () {
                return ini_get("session.upload_progress.name");
            }
            ),
            new \Twig_SimpleFunction('currencySymbol', array($this, 'currencySymbolFunction')),
            new \Twig_SimpleFunction('cancellationPolicies', array($this, 'cancellationPoliciesFunction')),
            new \Twig_SimpleFunction('vatInclusionText', array($this, 'vatInclusionText')),
        );
    }

    /**
     * Get currency symbol of currency arg
     *
     * @param $currency
     * @return null|string
     */
    public function currencySymbolFunction($currency)
    {
        return Intl::getCurrencyBundle()->getCurrencySymbol($currency);
    }

    /**
     * Display cancelation Policies text rules
     *
     * @return string
     */
    public function cancellationPoliciesFunction()
    {
        $policiesText = $this->translator->trans(
                'listing.cancellation_policy.help',
                array(),
                'cocorico_listing'
            ) . ":<br/>";

        foreach (Listing::$cancellationPolicyValues as $policyValue => $policyText) {
            /** @Ignore */
            $policyTextTrans = $this->translator->trans($policyText, array(), 'cocorico_listing');
            /** @Ignore */
            $policyDescTrans = $this->translator->trans(
                Listing::$cancellationPolicyDescriptions[$policyValue],
                array(),
                'cocorico_listing'
            );

            $policiesText .= "-" . $policyTextTrans . ":<br/>" . $policyDescTrans . "<br/>";
        }

        return $policiesText;
    }


    /**
     * Display VAT include / exclude text
     *
     * @param string    $locale
     * @param bool|null $displayVat Override default app parameter if setted
     * @param bool|null $includeVat Override default app parameter if setted
     *
     * @return string
     */
    public function vatInclusionText($locale, $displayVat = null, $includeVat = null)
    {
        if (($this->displayVat && $displayVat === null) || $displayVat === true) {
            if (($this->includeVat && $includeVat === null) || $includeVat === true) {
                return $this->translator->trans(
                    'vat_included',
                    array(),
                    'cocorico',
                    $locale
                );
            } else {
                return $this->translator->trans(
                    'vat_excluded',
                    array(),
                    'cocorico',
                    $locale
                );
            }
        }

        return '';
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getGlobals()
    {
        $listing = new \ReflectionClass("Cocorico\CoreBundle\Entity\Listing");
        $listingConstants = $listing->getConstants();

        $listingAvailability = new \ReflectionClass("Cocorico\CoreBundle\Document\ListingAvailability");
        $listingAvailabilityConstants = $listingAvailability->getConstants();

        $listingImage = new \ReflectionClass("Cocorico\CoreBundle\Entity\ListingImage");
        $listingImageConstants = $listingImage->getConstants();

        $userImage = new \ReflectionClass("Cocorico\UserBundle\Entity\UserImage");
        $userImageConstants = $userImage->getConstants();

        $booking = new \ReflectionClass("Cocorico\CoreBundle\Entity\Booking");
        $bookingConstants = $booking->getConstants();


        //CSS class by status
        $bookingStatusClass = array(
            Booking::STATUS_DRAFT => 'btn-yellow',
            Booking::STATUS_NEW => 'btn-yellow',
//            Booking::STATUS_ACCEPTED => 'btn-polo-blue',
            Booking::STATUS_PAYED => 'btn-algae-green',
            Booking::STATUS_EXPIRED => 'btn-nomad',
            Booking::STATUS_REFUSED => 'btn-flamingo',
            Booking::STATUS_CANCELED_ASKER => 'btn-salmon',
//            Booking::STATUS_CANCELED_OFFERER => 'btn-salmon',
            Booking::STATUS_PAYMENT_REFUSED => 'btn-fuzzy-brown'
        );

        $bookingBankWire = new \ReflectionClass("Cocorico\CoreBundle\Entity\BookingBankWire");
        $bookingBankWireConstants = $bookingBankWire->getConstants();

        $bookingPayinRefund = new \ReflectionClass("Cocorico\CoreBundle\Entity\BookingPayinRefund");
        $bookingPayinRefundConstants = $bookingPayinRefund->getConstants();

        return array(
            'locales' => $this->locales,
            'ListingConstants' => $listingConstants,
            'ListingAvailabilityConstants' => $listingAvailabilityConstants,
            'ListingImageConstants' => $listingImageConstants,
            'UserImageConstants' => $userImageConstants,
            'BookingConstants' => $bookingConstants,
            'BookingBankWireConstants' => $bookingBankWireConstants,
            'BookingPayinRefundConstants' => $bookingPayinRefundConstants,
            'bookingStatusClass' => $bookingStatusClass,
            'timeUnit' => $this->timeUnit,
            'timeUnitIsDay' => $this->timeUnitIsDay,
            'timeUnitAllDay' => $this->timeUnitAllDay,
            'daysDisplayMode' => $this->daysDisplayMode,
            'timesDisplayMode' => $this->timesDisplayMode,
            'timeUnitFlexibility' => $this->timeUnitFlexibility,
            'allowSingleDay' => $this->allowSingleDay,
            'endDayIncluded' => $this->endDayIncluded,
            'listingDefaultStatus' => $this->listingDefaultStatus,
            'listingPricePrecision' => $this->listingPricePrecision,
            'currencies' => $this->currencies,
            'defaultCurrency' => $this->defaultCurrency,
            'currentCurrency' => $this->currentCurrency,
            'priceMin' => $this->priceMin,
            'priceMax' => $this->priceMax,
            'feeAsOfferer' => $this->feeAsOfferer,
            'feeAsAsker' => $this->feeAsAsker,
            'displayMarker' => $this->displayMarker,
            'bookingExpirationDelay' => $this->bookingExpirationDelay,
            'bookingValidationMoment' => $this->bookingValidationMoment,
            'bookingValidationDelay' => $this->bookingValidationDelay,
            'bookingPriceMin' => $this->bookingPriceMin,
            'vatRate' => $this->vatRate,
            'includeVat' => $this->includeVat,
            'displayVat' => $this->displayVat,
            'listingSearchMinResult' => $this->listingSearchMinResult,
            'listingDuplication' => $this->listingDuplication,
            'minStartDelay' => $this->minStartDelay,
            'minStartTimeDelay' => $this->minStartTimeDelay
        );
    }


    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getName()
    {
        return 'core_extension';
    }
}
