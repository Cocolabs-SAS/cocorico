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
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface as Context;
use Symfony\Component\Validator\ExecutionContextInterface;

class BookingValidator extends ConstraintValidator
{
    private $bookingManager;
    private $session;
    private $minStartTimeDelay;
    private $acceptationDelay;
    private $currency;
    private $currencySymbol;
    private $timezone;

    /**
     * @param BookingManager $bookingManager
     * @param Session        $session
     * @param int            $minStartTimeDelay
     * @param int            $acceptationDelay
     * @param string         $currency
     */
    public function __construct(
        BookingManager $bookingManager,
        Session $session,
        $minStartTimeDelay,
        $acceptationDelay,
        $currency
    )
    {
        $this->bookingManager = $bookingManager;
        $this->session = $session;
        $this->timezone = $this->session->get('timezone');
        $this->minStartTimeDelay = $minStartTimeDelay;
        $this->acceptationDelay = $acceptationDelay;
        $this->currency = $currency;
        $this->currencySymbol = Intl::getCurrencyBundle()->getCurrencySymbol($currency);
    }

    /**
     * @param BookingEntity|mixed $booking
     * @param Booking|Constraint  $constraint
     *
     * @throws Exception
     */
    public function validate($booking, Constraint $constraint)
    {
        if ($booking->getStart() && $booking->getEnd()) {
            $violations = $this->getViolations($booking, $constraint);
            self::buildViolations($this->context, $violations);
        }
    }

    /**
     * @param BookingEntity      $booking
     * @param Booking|Constraint $constraint
     * @return array
     *
     * @throws Exception
     */
    private function getViolations($booking, $constraint)
    {
        $violations = array();

        $result = $this->bookingManager->checkBookingAndSetAmounts($booking);
        /** @var BookingEntity $booking */
        $booking = $result->booking;
        $errors = $result->errors;

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
            $minStart = new DateTime();
            $minStart->setTimezone(new DateTimeZone($this->timezone));
            if ($this->minStartTimeDelay > 0) {
                $minStart->add(new DateInterval('PT'.$this->minStartTimeDelay.'M'));
            }
            $violations[] = array(
                'message' => 'date_range.invalid.min_start {{ min_start_day }}',
                'parameter' => array('min_start_day' => $minStart->format('d/m/Y')),
                'domain' => 'cocorico'
            );
        }

        if (in_array('date_range.invalid.acceptation', $errors)) {
            $maxAcceptableDate = new DateTime();
            $maxAcceptableDate->setTimezone(new DateTimeZone($this->timezone));
            $maxAcceptableDate->add(new DateInterval('PT'.$this->acceptationDelay.'M'));
            $maxAcceptableDate->add(new DateInterval('P1D'));
            $violations[] = array(
                'message' => 'date_range.invalid.min_start {{ min_start_day }}',
                'parameter' => array('min_start_day' => $maxAcceptableDate->format('d/m/Y')),
                'domain' => 'cocorico',
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
            $minStart = new DateTime();
            $minStart->setTimezone(new DateTimeZone($this->timezone));
            if ($this->minStartTimeDelay > 0) {
                $minStart->add(new DateInterval('PT'.$this->minStartTimeDelay.'M'));
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


    /**
     * Build violations
     *
     * @param Context|ExecutionContextInterface $context
     * @param array $violations
     */
    public static function buildViolations($context, $violations)
    {
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
                    $context->buildViolation($message)
                        ->atPath($atPath)
                        ->setParameters($parameters)
                        ->setTranslationDomain($domain)
                        ->addViolation();
                } else {
                    $context->buildViolation($message)
                        ->atPath($atPath)
                        ->setTranslationDomain($domain)
                        ->addViolation();
                }
            }
        }
    }
}
