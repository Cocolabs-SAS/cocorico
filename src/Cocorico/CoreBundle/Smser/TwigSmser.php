<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Smser;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TwigSmser implements SmserInterface
{
    const TRANS_DOMAIN = 'cocorico_sms';


    protected $router;
    protected $twig;
    protected $requestStack;
    protected $parameters;
    /** @var  array locales */
    protected $locales;
    protected $templates;
    protected $adminEmail;
    protected $smsSender;

    /**
     *
     * @param UrlGeneratorInterface $router
     * @param \Twig_Environment     $twig
     * @param RequestStack          $requestStack
     * @param array                 $parameters
     * @param array                 $templates
     * @param                       $smsSender
     */
    public function __construct(
        UrlGeneratorInterface $router,
        \Twig_Environment $twig,
        RequestStack $requestStack,
        array $parameters,
        array $templates,
        $smsSender = null
    ) {
        $this->smsSender = $smsSender;
        $this->router = $router;
        $this->twig = $twig;

        /** parameters */
        $this->parameters = $parameters['parameters'];
        $this->adminEmail = $parameters['parameters']['cocorico_contact_mail'];
        $this->locales = $parameters['parameters']['cocorico_locales'];

        $this->templates = $templates;

        $this->locale = $parameters['parameters']['cocorico_locale'];
        if ($requestStack->getCurrentRequest()) {
            $this->locale = $requestStack->getCurrentRequest()->getLocale();
        }
    }


    /**
     * @param Booking $booking
     */
    public function sendBookingRequestMessageToOfferer(Booking $booking)
    {
        $this->sendBookingMessagesToOfferer($booking, "booking_request_offerer");
    }


    /**
     * @param Booking $booking
     */
    public function sendBookingExpirationAlertMessageToOfferer(Booking $booking)
    {
        $this->sendBookingMessagesToOfferer($booking, "booking_request_expiration_alert_offerer");
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingRequestExpiredMessageToOfferer(Booking $booking)
    {
        $this->sendBookingMessagesToOfferer($booking, "booking_request_expired_offerer");
    }


    /**
     * @param Booking $booking
     */
    public function sendBookingCanceledByAskerMessageToOfferer(Booking $booking)
    {
        $this->sendBookingMessagesToOfferer($booking, "booking_canceled_by_asker_offerer");
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingImminentMessageToOfferer(Booking $booking)
    {
        $this->sendBookingMessagesToOfferer($booking, "booking_imminent_offerer");
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingAcceptedMessageToAsker(Booking $booking)
    {
        $this->sendBookingMessagesToAsker($booking, "booking_accepted_asker");
    }


    /**
     * @param Booking $booking
     */
    public function sendBookingRefusedMessageToAsker(Booking $booking)
    {
        $this->sendBookingMessagesToAsker($booking, "booking_refused_asker");
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingRequestExpiredMessageToAsker(Booking $booking)
    {
        $this->sendBookingMessagesToAsker($booking, "booking_request_expired_asker");
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingImminentMessageToAsker(Booking $booking)
    {
        $this->sendBookingMessagesToAsker($booking, "booking_imminent_asker");
    }


    /**
     * @param Booking $booking
     * @param         $template
     */
    protected function sendBookingMessagesToOfferer(Booking $booking, $template)
    {
        $listing = $booking->getListing();
        $user = $listing->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $asker = $booking->getUser();
        $templateName = $this->templates['templates'][$template];

        $bookingUrl = $this->router->generate(
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
            'booking_url' => $bookingUrl,
            'booking_id' => $booking->getId(),
            'sms_type' => $template
        );

        $this->sendMessage($templateName, $context, $user->getPhone());
    }


    /**
     * @param Booking $booking
     * @param         $template
     */
    protected function sendBookingMessagesToAsker(Booking $booking, $template)
    {
        $user = $booking->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);
        $listing = $booking->getListing();
        $offerer = $listing->getUser();
        $templateName = $this->templates['templates'][$template];

        $bookingUrl = $this->router->generate(
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
            'booking_url' => $bookingUrl,
            'booking_id' => $booking->getId(),
            'sms_type' => $template
            //Used when sms ask a response to the user (see BookingManager->acceptOrRefuseFromSMS)
        );

        $this->sendMessage($templateName, $context, $user->getPhone());
    }

    /**
     * @param string $templateName
     * @param array  $context
     * @param string $phone
     */
    protected function sendMessage($templateName, $context, $phone)
    {
        $context['trans_domain'] = self::TRANS_DOMAIN;

        $context['user_locale'] = $this->locale;
        $context['locale'] = $this->locale;
        $context['app']['request']['locale'] = $this->locale;

        $options = array(
            'tag' => '',
            'prefix' => ''
        );

        if (isset($context["sms_type"])) {
            $options['tag'] = $context["sms_type"] . "-";
        }

        if (isset($context['user'])) {//user receiving the sms
            /** @var User $user */
            $user = $context['user'];
            $context['user_locale'] = $user->guessPreferredLanguage($this->locales, $this->locale);
            $context['locale'] = $context['user_locale'];
            $context['app']['request']['locale'] = $context['user_locale'];
            $options['tag'] .= $user->getId() . "-";
            $options['prefix'] = $user->getPhonePrefix();
        }

        if (isset($context['booking_id'])) {
            $options['tag'] .= $context['booking_id'];
        }

        /** @var \Twig_Template $template */
        $template = $this->twig->loadTemplate($templateName);
        $context = $this->twig->mergeGlobals($context);
        $message = $template->render($context);

        if ($this->smsSender) {
            $this->smsSender->send($phone, $message, $options);
        }

    }

}
