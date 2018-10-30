<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Mailer;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;

class TwigSwiftMailer implements MailerInterface
{
    const TRANS_DOMAIN = 'cocorico_mail';

    protected $mailer;
    protected $router;
    protected $twig;
    protected $requestStack;
    protected $translator;
    protected $timeUnit;
    protected $timeUnitIsDay;
    /** @var  array locales */
    protected $locales;
    protected $templates;
    protected $fromEmail;
    protected $adminEmail;

    /**
     * @param \Swift_Mailer         $mailer
     * @param UrlGeneratorInterface $router
     * @param \Twig_Environment     $twig
     * @param RequestStack          $requestStack
     * @param Translator            $translator
     * @param array                 $parameters
     * @param array                 $templates
     */
    public function __construct(
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        \Twig_Environment $twig,
        RequestStack $requestStack,
        Translator $translator,
        array $parameters,
        array $templates
    ) {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->translator = $translator;

        /** parameters */
        $parameters = $parameters['parameters'];

        $this->fromEmail = $parameters['cocorico_from_email'];
        $this->adminEmail = $parameters['cocorico_contact_email'];

        $this->timeUnit = $parameters['cocorico_time_unit'];
        $this->timeUnitIsDay = ($this->timeUnit % 1440 == 0) ? true : false;

        $this->locales = $parameters['cocorico_locales'];
        $this->locale = $parameters['cocorico_locale'];
        if ($requestStack->getCurrentRequest()) {
            $this->locale = $requestStack->getCurrentRequest()->getLocale();
        }

        $this->templates = $templates['templates'];
    }

    /**
     * @param Listing $listing
     */
    public function sendListingActivatedMessageToOfferer(Listing $listing)
    {
        $user = $listing->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $template = $this->templates['listing_activated_offerer'];

        $listingCalendarEditUrl = $this->router->generate(
            'cocorico_dashboard_listing_edit_availabilities_status',
            array(
                'listing_id' => $listing->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'listing' => $listing,
            'listing_calendar_edit_url' => $listingCalendarEditUrl,
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingRequestMessageToOfferer(Booking $booking)
    {
        $listing = $booking->getListing();
        $user = $listing->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $asker = $booking->getUser();
        $template = $this->templates['booking_request_offerer'];

        $bookingRequestUrl = $this->router->generate(
            'cocorico_dashboard_booking_show_offerer',
            array(
                'id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'asker' => $asker,
            'listing' => $listing,
            'booking' => $booking,
            'booking_request_url' => $bookingRequestUrl,
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingAcceptedMessageToOfferer(Booking $booking)
    {
        $listing = $booking->getListing();
        $user = $listing->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $asker = $booking->getUser();
        $template = $this->templates['booking_accepted_offerer'];

        $bookingRequestUrl = $this->router->generate(
            'cocorico_dashboard_booking_show_offerer',
            array(
                'id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );
        $profilePaymentInfoUrl = $this->router->generate(
            'cocorico_user_dashboard_profile_edit_bank_account',
            array(
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'asker' => $asker,
            'listing' => $listing,
            'booking' => $booking,
            'booking_request_url' => $bookingRequestUrl,
            'profile_payment_info_url' => $profilePaymentInfoUrl,
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }


    /**
     * @param Booking $booking
     */
    public function sendBookingRefusedMessageToOfferer(Booking $booking)
    {
        $listing = $booking->getListing();
        $user = $listing->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $asker = $booking->getUser();
        $template = $this->templates['booking_refused_offerer'];

        $listingCalendarEditUrl = $this->router->generate(
            'cocorico_dashboard_listing_edit_availabilities_status',
            array(
                'listing_id' => $listing->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'asker' => $asker,
            'booking' => $booking,
            'listing_calendar_edit_url' => $listingCalendarEditUrl,
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingExpirationAlertMessageToOfferer(Booking $booking)
    {
        $listing = $booking->getListing();
        $user = $listing->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $asker = $booking->getUser();
        $template = $this->templates['booking_request_expiration_alert_offerer'];

        $bookingRequestUrl = $this->router->generate(
            'cocorico_dashboard_booking_show_offerer',
            array(
                'id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'asker' => $asker,
            'listing' => $listing,
            'booking' => $booking,
            'booking_request_url' => $bookingRequestUrl,
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingRequestExpiredMessageToOfferer(Booking $booking)
    {
        $listing = $booking->getListing();
        $user = $listing->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $asker = $booking->getUser();
        $template = $this->templates['booking_request_expired_offerer'];

        $bookingRequestUrl = $this->router->generate(
            'cocorico_dashboard_booking_show_offerer',
            array(
                'id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'asker' => $asker,
            'listing' => $listing,
            'booking' => $booking,
            'booking_request_url' => $bookingRequestUrl,
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * Remind offerer to rate asker
     *
     * @param Booking $booking
     */
    public function sendReminderToRateAskerMessageToOfferer(Booking $booking)
    {
        $listing = $booking->getListing();
        $user = $listing->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $asker = $booking->getUser();
        $template = $this->templates['reminder_to_rate_asker_offerer'];

        $offererToAskerReviewUrl = $this->router->generate(
            'cocorico_dashboard_review_new',
            array(
                'booking_id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'asker' => $asker,
            'booking' => $booking,
            'offerer_to_asker_review_url' => $offererToAskerReviewUrl
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingCanceledByAskerMessageToOfferer(Booking $booking)
    {
        $listing = $booking->getListing();
        $user = $listing->getUser();
        $asker = $booking->getUser();
        $template = $this->templates['booking_canceled_by_asker_offerer'];

        $offererCancellationAmount = $booking->getBankWire() ? $booking->getBankWire()->getAmountDecimal() : 0;

        $context = array(
            'user' => $user,
            'asker' => $asker,
            'listing' => $listing,
            'booking' => $booking,
            'offerer_cancellation_amount' => $offererCancellationAmount,
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingImminentMessageToOfferer(Booking $booking)
    {
        $listing = $booking->getListing();
        $user = $listing->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $asker = $booking->getUser();
        $template = $this->templates['booking_imminent_offerer'];

        $bookingRequestUrl = $this->router->generate(
            'cocorico_dashboard_booking_show_offerer',
            array(
                'id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'asker' => $asker,
            'listing' => $listing,
            'booking' => $booking,
            'booking_request_url' => $bookingRequestUrl,
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendWireTransferMessageToOfferer(Booking $booking)
    {
        $listing = $booking->getListing();
        $user = $listing->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $template = $this->templates['booking_bank_wire_transfer_offerer'];

        $bookingRequestUrl = $this->router->generate(
            'cocorico_dashboard_booking_show_offerer',
            array(
                'id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $paymentOffererUrl = $this->router->generate(
            'cocorico_dashboard_booking_bank_wire_offerer',
            array(
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'booking' => $booking,
            'booking_request_url' => $bookingRequestUrl,
            'offerer_payments_list' => $paymentOffererUrl
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Listing $listing
     */
    public function sendUpdateYourCalendarMessageToOfferer(Listing $listing)
    {
        $user = $listing->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $template = $this->templates['update_your_calendar_offerer'];

        $listingCalendarEditUrl = $this->router->generate(
            'cocorico_dashboard_listing_edit_availabilities_status',
            array(
                'listing_id' => $listing->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'listing_calendar_edit_url' => $listingCalendarEditUrl
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }


    /**
     * @param Booking $booking
     */
    public function sendPaymentErrorMessageToOfferer(Booking $booking)
    {
//        $listing = $booking->getListing();
//        $user = $listing->getUser();
//        $asker = $booking->getUser();
//        $bookingRequestUrl = $this->router->generate(
//            'cocorico_dashboard_booking_show_offerer',
//            array('id' => $booking->getId()),
//            true
//        );
//
//        $context = array(
//            'user' => $user,
//            'asker' => $asker,
//            'booking_request_url' => $bookingRequestUrl,
//        );
//
//        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }


    /**
     * @param Booking $booking
     */
    public function sendBookingRequestMessageToAsker(Booking $booking)
    {
        $user = $booking->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $listing = $booking->getListing();
        $offerer = $listing->getUser();
        $template = $this->templates['booking_request_asker'];

        $bookingRequestUrl = $this->router->generate(
            'cocorico_dashboard_booking_show_asker',
            array(
                'id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'offerer' => $offerer,
            'listing' => $listing,
            'booking' => $booking,
            'booking_request_url' => $bookingRequestUrl,
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingAcceptedMessageToAsker(Booking $booking)
    {
        $user = $booking->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $listing = $booking->getListing();
        $offerer = $listing->getUser();
        $template = $this->templates['booking_accepted_asker'];

        $bookingRequestUrl = $this->router->generate(
            'cocorico_dashboard_booking_show_asker',
            array(
                'id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );
        $paymentAskerUrl = $this->router->generate(
            'cocorico_dashboard_booking_payin_asker',
            array(
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'offerer' => $offerer,
            'listing' => $listing,
            'booking' => $booking,
            'booking_request_url' => $bookingRequestUrl,
            'payments_asker_list' => $paymentAskerUrl
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }


    /**
     * @param Booking $booking
     */
    public function sendBookingRefusedMessageToAsker(Booking $booking)
    {
        $user = $booking->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $template = $this->templates['booking_refused_asker'];

        $similarListingUrl = $this->router->generate(
            'cocorico_home',
            array(
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'booking' => $booking,
            'similar_booking_listings_url' => $similarListingUrl //'#'
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingRequestExpiredMessageToAsker(Booking $booking)
    {
        $user = $booking->getUser();
        $template = $this->templates['booking_request_expired_asker'];
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);

        $similarListingUrl = $this->router->generate(
            'cocorico_home',
            array(
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'booking' => $booking,
            'similar_booking_listings_url' => $similarListingUrl //'#'
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingImminentMessageToAsker(Booking $booking)
    {
        $user = $booking->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $listing = $booking->getListing();
        $offerer = $listing->getUser();
        $template = $this->templates['booking_imminent_asker'];

        $bookingRequestUrl = $this->router->generate(
            'cocorico_dashboard_booking_show_asker',
            array(
                'id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'offerer' => $offerer,
            'listing' => $listing,
            'booking' => $booking,
            'booking_request_url' => $bookingRequestUrl,
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendReminderToRateOffererMessageToAsker(Booking $booking)
    {
        $user = $booking->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $listing = $booking->getListing();
        $offerer = $listing->getUser();
        $template = $this->templates['reminder_to_rate_offerer_asker'];

        $askerToOffererReviewUrl = $this->router->generate(
            'cocorico_dashboard_review_new',
            array(
                'booking_id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'offerer' => $offerer,
            'booking' => $booking,
            'asker_to_offerer_review_url' => $askerToOffererReviewUrl
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingCanceledByAskerMessageToAsker(Booking $booking)
    {
        $user = $booking->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $listing = $booking->getListing();
        $offerer = $listing->getUser();
        $template = $this->templates['booking_canceled_by_asker_asker'];

        $profilePaymentInfoUrl = $this->router->generate(
            'cocorico_user_dashboard_profile_edit_bank_account',
            array(
                '_locale' => $userLocale
            ),
            true
        );

        $askerCancellationAmount = $booking->getPayinRefund() ? $booking->getPayinRefund()->getAmountDecimal() : 0;

        $context = array(
            'user' => $user,
            'offerer' => $offerer,
            'listing' => $listing,
            'booking' => $booking,
            'profile_payment_info_url' => $profilePaymentInfoUrl,
            'asker_cancellation_amount' => $askerCancellationAmount,
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }


    public function sendMessageToAdmin($subject, $message)
    {
        $template = $this->templates['admin_message'];

        $context = array(
            'user_locale' => $this->locale,
            'subject' => $subject,
            'admin_message' => $message
        );

        $this->sendMessage($template, $context, $this->fromEmail, $this->adminEmail);
    }

    /**
     * @param Booking $booking
     */
//    public function sendPaymentErrorMessageToAsker(Booking $booking)
//    {
//        $user = $booking->getUser();
//        $listing = $booking->getListing();
//        $offerer = $listing->getUser();
//        $template = $this->templates['payment_error_asker'];
//
//        $bookingRequestUrl = $this->router->generate(
//            'cocorico_dashboard_booking_show_offerer',
//            array('id' => $booking->getId()),
//            true
//        );
//
//        $context = array(
//            'user' => $user,
//            'offerer' => $offerer,
//            'listing' => $listing,
//            'booking' => $booking,
//            'booking_request_url' => $bookingRequestUrl
//        );
//
//        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
//    }


    /**
     * @param string $templateName
     * @param array  $context
     * @param string $fromEmail
     * @param string $toEmail
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $context['trans_domain'] = self::TRANS_DOMAIN;

        $context['user_locale'] = $this->locale;
        $context['locale'] = $this->locale;
        $context['app']['request']['locale'] = $this->locale;

        if (isset($context['user'])) {//user receiving the email
            /** @var User $user */
            $user = $context['user'];
            $context['user_locale'] = $user->guessPreferredLanguage($this->locales, $this->locale);
            $context['locale'] = $context['user_locale'];
            $context['app']['request']['locale'] = $context['user_locale'];
        }

        if (isset($context['listing'])) {
            /** @var Listing $listing */
            $listing = $context['listing'];
            $translations = $listing->getTranslations();
            if ($translations->count() && isset($translations[$context['user_locale']])) {
                $slug = $translations[$context['user_locale']]->getSlug();
                $title = $translations[$context['user_locale']]->getTitle();
            } else {
                $slug = $listing->getSlug();
                $title = $listing->getTitle();
            }
            $context['listing_public_url'] = $this->router->generate(
                'cocorico_listing_show',
                array(
                    '_locale' => $context['user_locale'],
                    'slug' => $slug
                ),
                true
            );

            $context['listing_title'] = $title;
        }

        if (isset($context['booking'])) {
            $context['booking_time_range_title'] = $context['booking_time_range'] = '';
            if (!$this->timeUnitIsDay) {
                /** @var Booking $booking */
                $booking = $context['booking'];
                $context['booking_time_range_title'] = $this->translator->trans(
                    'booking.time_range.title',
                    array(),
                    'cocorico_mail',
                    $context['user_locale']
                );
                $context['booking_time_range'] .= $booking->getStartTime()->format('H:i') . " - " .
                    $booking->getEndTime()->format('H:i');
            }
        }

        /** @var \Twig_Template $template */
        $template = $this->twig->loadTemplate($templateName);
        $context = $this->twig->mergeGlobals($context);

        $subject = $template->renderBlock('subject', $context);
        $context["message"] = $template->renderBlock('message', $context);

        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

//        echo 'subject:' . $subject . 'endsubject';
//        echo 'htmlBody:' . $htmlBody . 'endhtmlBody';
//        echo 'textBody:' . $textBody . 'endtextBody';

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message
                ->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        $this->mailer->send($message);
    }

}
