<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Validator\Constraints;

use Cocorico\CoreBundle\Entity\Booking as BookingEntity;
use Cocorico\CoreBundle\Form\Type\Frontend\BookingNewType;
use Cocorico\CoreBundle\Model\Manager\BookingManager;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class BookingValidator extends ConstraintValidator
{
    private $bookingManager;
    private $minStartDelay;
    private $minStartTimeDelay;
    private $currency;
    private $currencySymbol;

    /**
     * @param BookingManager $bookingManager
     * @param int            $minStartDelay
     * @param int            $minStartTimeDelay
     * @param string         $currency
     */
    public function __construct(BookingManager $bookingManager, $minStartDelay, $minStartTimeDelay, $currency)
    {
        $this->bookingManager = $bookingManager;
        $this->minStartDelay = $minStartDelay;
        $this->minStartTimeDelay = $minStartTimeDelay;
        $this->currency = $currency;
        $this->currencySymbol = Intl::getCurrencyBundle()->getCurrencySymbol($currency);
    }

    /**
     * @param BookingEntity|mixed $booking
     * @param Booking|Constraint  $constraint
     */
    public function validate($booking, Constraint $constraint)
    {
        if ($booking->getStart() && $booking->getEnd()) {

            $violations = $this->getViolations($booking, $constraint);

            if (count($violations)) {
                foreach ($violations as $violation) {
                    $message = $violation['message'];
                    $atPath = isset($violation['atPath']) ? $violation['atPath'] : 'date_range';
                    $domain = isset($violation['domain']) ? $violation['domain'] : 'cocorico_booking';
                    $parameters = isset($violation['parameter']) ? $violation['parameter'] : array();
                    reset($parameters);
                    foreach ($parameters as $key => $value) {
                        $parameters['{{ ' . $key . ' }}'] = $value;
                    }

                    if ($parameters) {
                        $this->context->buildViolation($message)
                            ->atPath($atPath)
                            ->setParameters($parameters)
                            ->setTranslationDomain($domain)
                            ->addViolation();
                    } else {
                        $this->context->buildViolation($message)
                            ->atPath($atPath)
                            ->setTranslationDomain($domain)
                            ->addViolation();
                    }
                }
            }
        }
    }

    /**
     * @param BookingEntity      $booking
     * @param Booking|Constraint $constraint
     * @return array
     */
    private function getViolations($booking, $constraint)
    {
        $violations = array();

//        if ($booking->getUser() == $booking->getListing()->getUser()) {
//            $violations[] = array(
//                'message' => $constraint::$messageSelfBooking,
//            );
//        }


        $errors = $this->bookingManager->checkBookingAvailabilityAndSetAmounts($booking);
        //Availability error
        if (in_array('unavailable', $errors)) {
            $violations[] = array(
                'message' => $constraint::$messageUnavailable,
            );
        }

        //Duration error
        if (in_array('duration_invalid', $errors)) {
            $violations[] = array(
                'message' => $constraint::$messageDurationInvalid,
            );
        }

        //Date Time errors
        if (in_array('date_range.invalid.min_start', $errors)) {
            $minStart = new \DateTime();
            $minStart->setTimezone(new \DateTimeZone($this->bookingManager->getTimeZone()));
            if ($this->minStartDelay > 0) {
                $minStart->add(new \DateInterval('P' . $this->minStartDelay . 'D'));
            }
            $violations[] = array(
                'message' => 'date_range.invalid.min_start {{ min_start_day }}',
                'parameter' => array('min_start_day' => $minStart->format('d/m/Y')),
                'domain' => 'cocorico'
            );
        }

        if (in_array('date_range.invalid.max_end', $errors)) {
            $violations[] = array(
                'message' => 'date_range.invalid.max_end {{ date_max }}',
                'parameter' => array('date_max' => $booking->getEnd()->format('d/m/Y')),
                'domain' => 'cocorico'
            );
        }

        if (in_array('date_range.invalid.end_before_start', $errors)) {
            $violations[] = array(
                'message' => 'date_range.invalid.end_before_start',
            );
        }

        if (in_array('time_range.invalid.end_before_start', $errors)) {
            $violations[] = array(
                'message' => 'time_range.invalid.end_before_start',
            );
        }

        if (in_array('time_range.invalid.single_time', $errors)) {
            $violations[] = array(
                'message' => 'time_range.invalid.single_time',
            );
        }

        if (in_array('time_range.invalid.duration', $errors)) {
            $violations[] = array(
                'message' => 'time_range.invalid.duration',
            );
        }

        if (in_array('time_range.invalid.required', $errors)) {
            $violations[] = array(
                'message' => 'time_range.invalid.required',
            );
        }

        if (in_array('time_range.invalid.min_start', $errors)) {
            $minStart = new \DateTime();
            $minStart->setTimezone(new \DateTimeZone($this->bookingManager->getTimeZone()));
            if ($this->minStartTimeDelay > 0) {
                $minStart->add(new \DateInterval('PT' . $this->minStartTimeDelay . 'M'));
            }
            $violations[] = array(
                'message' => 'time_range.invalid.min_start {{ min_start_time }}',
                'parameter' => array('min_start_time' => $minStart->format('d/m/Y H:i')),
                'domain' => 'cocorico'
            );
        }

        //Amount error
        if (in_array('amount_invalid', $errors)) {
            $violations[] = array(
                'message' => $constraint::$messageAmountInvalid,
                'parameter' => array(
                    'min_price' => $this->bookingManager->minPrice / 100 . " " . $this->currencySymbol
                ),
                'domain' => 'cocorico'
            );
        }

        //Voucher error
        if (in_array('code_voucher_invalid', $errors)) {
            $violations[] = array(
                'message' => BookingNewType::$voucherError,
                'atPath' => 'codeVoucher',
            );
        }

        if (in_array('amount_voucher_invalid', $errors)) {
            $violations[] = array(
                'message' => $constraint::$messageAmountInvalid,
                'parameter' => array(
                    'min_price' => $this->bookingManager->minPrice / 100 . " " . $this->currencySymbol
                ),
                'atPath' => 'codeVoucher',
                'domain' => 'cocorico'
            );
        }

        //Delivery error
        if (in_array('delivery_max_invalid', $errors)) {
            $violations[] = array(
                'message' => BookingNewType::$messageDeliveryMaxInvalid,
                'atPath' => 'deliveryAddress',
            );
        }

        if (in_array('delivery_invalid', $errors)) {
            $violations[] = array(
                'message' => BookingNewType::$messageDeliveryInvalid,
                'atPath' => 'deliveryAddress',
            );
        }

        return $violations;
    }
}
