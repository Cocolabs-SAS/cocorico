<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Controller\Dashboard\Offerer;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\MessageBundle\Entity\Message;
use Cocorico\MessageBundle\Event\MessageEvent;
use Cocorico\MessageBundle\Event\MessageEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Booking Dashboard controller.
 *
 * @Route("/offerer/booking")
 */
class BookingController extends Controller
{

    /**
     * Lists all booking entities.
     *
     * @Route("/{page}", name="cocorico_dashboard_booking_offerer", defaults={"page" = 1})
     * @Method("GET")
     *
     * @param  Request $request
     * @param  int     $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, $page)
    {
        $filterForm = $this->createBookingFilterForm();
        $filterForm->handleRequest($request);

        $status = $request->query->get('status');
        $bookingManager = $this->get('cocorico.booking.manager');
        $bookings = $bookingManager->findByOfferer(
            $this->getUser()->getId(),
            $request->getLocale(),
            $page,
            array($status)
        );

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Booking:index.html.twig',
            array(
                'bookings' => $bookings,
                'pagination' => array(
                    'page' => $page,
                    'pages_count' => ceil($bookings->count() / $bookingManager->maxPerPage),
                    'route' => $request->get('_route'),
                    'route_params' => $request->query->all()
                ),
                'filterForm' => $filterForm->createView(),
            )
        );
    }


    /**
     * Finds and displays a Booking entity.
     *
     * @Route("/{id}/show", name="cocorico_dashboard_booking_show_offerer", requirements={
     *      "id" = "\d+",
     * })
     * @Method({"GET", "POST"})
     * @Security("is_granted('view_as_offerer', booking)")
     * @ParamConverter("booking", class="Cocorico\CoreBundle\Entity\Booking")
     *
     * @param Request $request
     * @param Booking $booking
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request, Booking $booking)
    {
        $thread = $booking->getThread();
        /** @var Form $form */
        $form = $this->container->get('fos_message.reply_form.factory')->create($thread);

        $paramArr = $request->get($form->getName());
        $request->request->set($form->getName(), $paramArr);
        $formHandler = $this->container->get('fos_message.reply_form.handler');

        /** @var Message $message */
        if ($message = $formHandler->process($form)) {
            $selfUrl = $this->container->get('router')->generate(
                'cocorico_dashboard_booking_show_offerer',
                array('id' => $booking->getId())
            );

            $recipients = $thread->getOtherParticipants($this->getUser());
            $recipient = (count($recipients) > 0) ? $recipients[0] : $this->getUser();

            $messageEvent = new MessageEvent($thread, $recipient, $this->getUser());
            $this->container->get('event_dispatcher')->dispatch(MessageEvents::MESSAGE_POST_SEND, $messageEvent);

            return new RedirectResponse($selfUrl);
        }

        //Amount excl or incl tax
        $amountTotal = $booking->getAmountToPayToOffererDecimal();
        if (!$this->container->getParameter('cocorico.include_vat')) {
            $amountTotal = $booking->getAmountToPayToOffererExcludingVATDecimal(
                $this->container->getParameter('cocorico.vat')
            );
        }

        $canBeAcceptedOrRefusedByOfferer = $this->get('cocorico.booking.manager')
            ->canBeAcceptedOrRefusedByOfferer($booking);

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Booking:show.html.twig',
            array(
                'booking' => $booking,
                'canBeAcceptedOrRefusedByOfferer' => $canBeAcceptedOrRefusedByOfferer,
                'form' => $form->createView(),
                'other_user' => $booking->getUser(),
                'other_user_rating' => $booking->getUser()->getAverageAskerRating(),
                'amount_total' => $amountTotal,
                'vat_inclusion_text' => $this->get('cocorico.twig.core_extension')
                    ->vatInclusionText($request->getLocale())
            )
        );
    }


    /**
     * Edit a Booking entity. (Accept or Refuse)
     *
     * @Route("/{id}/edit/{type}", name="cocorico_dashboard_booking_edit_offerer", requirements={
     *      "id" = "\d+",
     *      "type" = "accept|refuse",
     * })
     * @Method({"GET", "POST"})
     * @Security("is_granted('edit_as_offerer', booking)")
     * @ParamConverter("booking", class="Cocorico\CoreBundle\Entity\Booking")
     *
     * @param Request $request
     * @param Booking $booking
     * @param string  $type The edition type (accept or refuse)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Booking $booking, $type)
    {
        $bookingHandler = $this->get('cocorico.form.handler.booking.offerer.dashboard');
        $form = $this->createEditForm($booking, $type);

        $success = $bookingHandler->process($form);

        $translator = $this->container->get('translator');
        $session = $this->container->get('session');
        if ($success == 1) {
            $url = $this->generateUrl(
                'cocorico_dashboard_booking_edit_offerer',
                array(
                    'id' => $booking->getId(),
                    'type' => $type
                )
            );

            $session->getFlashBag()->add(
                'success',
                $translator->trans('booking.edit.success', array(), 'cocorico_booking')
            );

            return $this->redirect($url);
        } elseif ($success < 0) {
            $errorMsg = $translator->trans('booking.new.unknown.error', array(), 'cocorico_booking');
            if ($success == -1 || $success == -2 || $success == -4) {
                $errorMsg = $translator->trans('booking.edit.error', array(), 'cocorico_booking');
            } elseif ($success == -3) {
                $errorMsg = $translator->trans('booking.edit.payin.error', array(), 'cocorico_booking');
            }
            $session->getFlashBag()->add('error', $errorMsg);
        }

        //Amount excl or incl tax
        $amountTotal = $booking->getAmountToPayToOffererDecimal();
        if (!$this->container->getParameter('cocorico.include_vat')) {
            $amountTotal = $booking->getAmountToPayToOffererExcludingVATDecimal(
                $this->container->getParameter('cocorico.vat')
            );
        }

        $canBeAcceptedOrRefusedByOfferer = $this->get('cocorico.booking.manager')
            ->canBeAcceptedOrRefusedByOfferer($booking);

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Booking:edit.html.twig',
            array(
                'booking' => $booking,
                'booking_can_be_edited' => $canBeAcceptedOrRefusedByOfferer,
                'type' => $type,
                'form' => $form->createView(),
                'other_user' => $booking->getUser(),
                'other_user_rating' => $booking->getUser()->getAverageAskerRating(),
                'amount_total' => $amountTotal,
                'vat_inclusion_text' => $this->get('cocorico.twig.core_extension')
                    ->vatInclusionText($request->getLocale())
            )
        );

    }

    /**
     * Creates a form to edit a Booking entity.
     *
     * @param Booking $booking The entity
     * @param string  $type    The edition type (accept or refuse)
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Booking $booking, $type)
    {
        $form = $this->get('form.factory')->createNamed(
            'booking',
            'booking_edit',
            $booking,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_booking_edit_offerer',
                    array(
                        'id' => $booking->getId(),
                        'type' => $type,
                    )
                ),
            )
        );

        return $form;
    }

    /**
     * Creates a form to filter bookings
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createBookingFilterForm()
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            'booking_status_filter',
            null,
            array(
                'action' => $this->generateUrl(
                    'cocorico_dashboard_booking_offerer',
                    array('page' => 1)
                ),
                'method' => 'GET',
            )
        );

        return $form;
    }


}
