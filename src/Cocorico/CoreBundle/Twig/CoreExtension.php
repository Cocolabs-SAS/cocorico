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
use Cocorico\CoreBundle\Utils\PHP;
use Lexik\Bundle\CurrencyBundle\Twig\Extension\CurrencyExtension;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Translation\TranslatorInterface;

class CoreExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $currencyExtension;
    protected $translator;
    protected $session;
    protected $locales;
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $timeZone;
    protected $daysDisplayMode;
    protected $timesDisplayMode;
    protected $timeUnitFlexibility;
    protected $timeUnitAllDay;
    protected $timePicker;
    protected $timeHoursAvailable;
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
    protected $bookingExpirationDelay;
    protected $bookingAcceptationDelay;
    protected $bookingValidationMoment;
    protected $bookingValidationDelay;
    protected $bookingPriceMin;
    protected $vatRate;
    protected $includeVat;
    protected $displayVat;
    protected $listingSearchMinResult;
    protected $listingDuplication;
    protected $minStartDelay;
    protected $minStartTimeDelay;

    /**
     *
     * @param CurrencyExtension          $currencyExtension
     * @param TranslatorInterface $translator
     * @param Session             $session
     * @param array                      $parameters
     *
     */

    public function __construct(
        $currencyExtension,
        TranslatorInterface $translator,
        Session $session,
        array $parameters
    ) {
        //Services
        $this->currencyExtension = $currencyExtension;
        $this->translator = $translator;
        $this->session = $session;

        $parameters = $parameters['parameters'];

        $this->locales = $parameters["cocorico_locales"];

        //Time unit
        $this->timeUnit = $parameters["cocorico_time_unit"];
        $this->timeUnitIsDay = ($this->timeUnit % 1440 == 0) ? true : false;
        $this->timeZone = $parameters["cocorico_time_zone"];
        $this->timeUnitAllDay = $parameters["cocorico_time_unit_allday"];
        $this->timeUnitFlexibility = $parameters["cocorico_time_unit_flexibility"];
        $this->daysDisplayMode = $parameters["cocorico_days_display_mode"];
        $this->timesDisplayMode = $parameters["cocorico_times_display_mode"];
        $this->timePicker = $parameters["cocorico_time_picker"];
        $this->timeHoursAvailable = $parameters["cocorico_time_hours_available"];

        //Currencies
        $this->currencies = $parameters["cocorico_currencies"];
        $this->defaultCurrency = $parameters["cocorico_currency"];
        $this->currentCurrency = $session->get('currency', $this->defaultCurrency);

        //Fees
        $this->feeAsOfferer = $parameters["cocorico_fee_as_offerer"];
        $this->feeAsAsker = $parameters["cocorico_fee_as_asker"];

        //Status
        $this->listingDefaultStatus = $parameters["cocorico_listing_availability_status"];

        //Prices
        $this->listingPricePrecision = $parameters["cocorico_listing_price_precision"];
        $this->priceMin = $parameters["cocorico_listing_price_min"];
        $this->priceMax = $parameters["cocorico_listing_price_max"];
        $this->bookingPriceMin = $parameters["cocorico_booking_price_min"];

        //Map
        $this->displayMarker = $parameters["cocorico_listing_map_display_marker"];

        $this->listingSearchMinResult = $parameters["cocorico_listing_search_min_result"];
        $this->listingDuplication = $parameters["cocorico_listing_duplication"];

        $this->allowSingleDay = $parameters["cocorico_booking_allow_single_day"];
        $this->endDayIncluded = $parameters["cocorico_booking_end_day_included"];

        //Delay
        $this->bookingExpirationDelay = $parameters["cocorico_booking_expiration_delay"];
        $this->bookingAcceptationDelay = $parameters["cocorico_booking_acceptation_delay"];
        $this->bookingValidationMoment = $parameters["cocorico_booking_validated_moment"];
        $this->bookingValidationDelay = $parameters["cocorico_booking_validated_delay"];
        $this->minStartDelay = $parameters["cocorico_booking_min_start_delay"];
        $this->minStartTimeDelay = $parameters["cocorico_booking_min_start_time_delay"];

        //VAT
        $this->vatRate = $parameters["cocorico_vat"];
        $this->includeVat = $parameters["cocorico_include_vat"];
        $this->displayVat = $parameters["cocorico_display_vat"];

        $this->addressDelivery = $parameters["cocorico_user_address_delivery"];
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
            new \Twig_SimpleFilter('format_price', array($this, 'formatPriceFilter')),
            new \Twig_SimpleFilter('strip_private_info', array($this, 'stripPrivateInfo'))
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
     * @param int $seconds
     * @param string $format
     *
     * @return string
     */
    public function formatSecondsFilter($seconds, $format = 'dhm')
    {
        $time = PHP::seconds_to_time($seconds);
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
     * @param string $text
     * @param array  $typeInfo
     * @param string $replaceBy Text replacement translated
     *
     * @return string
     */
    public function stripPrivateInfo(
        $text,
        $typeInfo = array("phone", "email", "domain"),
        $replaceBy = 'default'
    ) {

        if ($replaceBy == 'default') {
            $replaceBy = $this->translator->trans(
                'private_info_replacement',
                array(),
                'cocorico'
            );
        }

        return PHP::strip_texts($text, $typeInfo, $replaceBy);
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
            new \Twig_SimpleFunction('staticProperty', array($this, 'staticProperty')),
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
     * Get static properties values
     *
     * @param string $class
     * @param string $property
     * @return mixed
     */
    public function staticProperty($class, $property)
    {
        if (property_exists($class, $property)) {
            return $class::$$property;
        }

        return null;
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
            'timeZone' => $this->timeZone,
            'timeUnitAllDay' => $this->timeUnitAllDay,
            'timePicker' => $this->timePicker,
            'timeHoursAvailable' => $this->timeHoursAvailable,
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
            'bookingAcceptationDelay' => $this->bookingAcceptationDelay,
            'bookingValidationMoment' => $this->bookingValidationMoment,
            'bookingValidationDelay' => $this->bookingValidationDelay,
            'bookingPriceMin' => $this->bookingPriceMin,
            'vatRate' => $this->vatRate,
            'includeVat' => $this->includeVat,
            'displayVat' => $this->displayVat,
            'listingSearchMinResult' => $this->listingSearchMinResult,
            'listingDuplication' => $this->listingDuplication,
            'minStartDelay' => $this->minStartDelay,
            'minStartTimeDelay' => $this->minStartTimeDelay,
            'addressDelivery' => $this->addressDelivery
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
