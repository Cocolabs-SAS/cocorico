<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Controller\Dashboard\Asker;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Form\Type\Dashboard\BookingEditType;
use Cocorico\CoreBundle\Form\Type\Dashboard\BookingStatusFilterType;
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
 * @Route("/asker/booking")
 */
class BookingController extends Controller
{

    /**
     * Lists all booking entities.
     *
     * @Route("/{page}", name="cocorico_dashboard_booking_asker", defaults={"page" = 1})
     * @Method("GET")
     *
     * @param  Request $request
     * @param  int     $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, $page)
    {
        $filterForm = $this->createBookingFilterForm();
        $filterForm->handleRequest($request);

        $status = $request->query->get('status');
        $bookingManager = $this->get('cocorico.booking.manager');
        $bookings = $bookingManager->findByAsker(
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
     * @Route("/{id}/show", name="cocorico_dashboard_booking_show_asker", requirements={
     *      "id" = "\d+",
     * })
     * @Method({"GET", "POST"})
     * @Security("is_granted('view_as_asker', booking)")
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
        $form = $this->get('fos_message.reply_form.factory')->create($thread);
        $paramArr = $request->get($form->getName());
        $request->request->set($form->getName(), $paramArr);

        $formHandler = $this->get('fos_message.reply_form.handler');

        if ($message = $formHandler->process($form)) {

            $recipients = $thread->getOtherParticipants($this->getUser());
            $recipient = (count($recipients) > 0) ? $recipients[0] : $this->getUser();

            $messageEvent = new MessageEvent($thread, $recipient, $this->getUser());
            $this->get('event_dispatcher')->dispatch(MessageEvents::MESSAGE_POST_SEND, $messageEvent);

            return $this->redirectToRoute(
                'cocorico_dashboard_booking_show_asker',
                array('id' => $booking->getId())
            );
        }

        $canBeCanceledByAsker = $this->get('cocorico.booking.manager')->canBeCanceledByAsker($booking);

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Booking:show.html.twig',
            array(
                'booking' => $booking,
                'canBeCanceledByAsker' => $canBeCanceledByAsker,
                'form' => $form->createView(),
                'other_user' => $booking->getListing()->getUser(),
                'other_user_rating' => $booking->getListing()->getUser()->getAverageOffererRating(),
                'amount_total' => $booking->getAmountToPayByAskerDecimal(),
                'vat_inclusion_text' => $this->get('cocorico.twig.core_extension')
                    ->vatInclusionText($request->getLocale(), true, true),
                'user_timezone' => $booking->getTimeZoneAsker(),
            )
        );
    }


    /**
     * Edit a Booking entity. (Cancel)
     *
     * @Route("/{id}/edit/{type}", name="cocorico_dashboard_booking_edit_asker", requirements={
     *      "id" = "\d+",
     *      "type" = "cancel",
     * })
     * @Method({"GET", "POST"})
     * @Security("is_granted('edit_as_asker', booking)")
     * @ParamConverter("booking", class="Cocorico\CoreBundle\Entity\Booking")
     *
     * @param Request $request
     * @param Booking $booking
     * @param string  $type The edition type (cancel)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Booking $booking, $type)
    {
        $bookingHandler = $this->get('cocorico.form.handler.booking.asker.dashboard');
        $bookingRefundManger = $this->get('cocorico.booking_payin_refund.manager');
        $form = $this->createEditForm($booking, $type);

        $success = $bookingHandler->process($form);

        $translator = $this->get('translator');
        $session = $this->get('session');
        if ($success == 1) {

            $session->getFlashBag()->add(
                'success',
                $translator->trans('booking.edit.success', array(), 'cocorico_booking')
            );

            return $this->redirectToRoute(
                'cocorico_dashboard_booking_edit_asker',
                array(
                    'id' => $booking->getId(),
                    'type' => $type
                )
            );
        } elseif ($success < 0) {
            $errorMsg = $translator->trans('booking.new.unknown.error', array(), 'cocorico_booking');
            if ($success == -1 || $success == -2 || $success == -4) {
                $errorMsg = $translator->trans('booking.edit.error', array(), 'cocorico_booking');
            } elseif ($success == -3) {
                $errorMsg = $translator->trans('booking.edit.payin.error', array(), 'cocorico_booking');
            }
            $session->getFlashBag()->add('error', $errorMsg);
        }

        $canBeCanceledByAsker = $this->get('cocorico.booking.manager')->canBeCanceledByAsker($booking);

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Booking:edit.html.twig',
            array(
                'booking' => $booking,
                'booking_can_be_edited' => $canBeCanceledByAsker,
                'type' => $type,
                'form' => $form->createView(),
                'other_user' => $booking->getListing()->getUser(),
                'other_user_rating' => $booking->getListing()->getUser()->getAverageOffererRating(),
                'amount_total' => $bookingRefundManger->getAmountDecimalToRefundOrRefundedToAsker($booking),
                'vat_inclusion_text' => $this->get('cocorico.twig.core_extension')
                    ->vatInclusionText($request->getLocale(), true, true),
                'user_timezone' => $booking->getTimeZoneAsker(),
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
            BookingEditType::class,
            $booking,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_booking_edit_asker',
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
            BookingStatusFilterType::class,
            null,
            array(
                'action' => $this->generateUrl(
                    'cocorico_dashboard_booking_asker',
                    array('page' => 1)
                ),
                'method' => 'GET',
            )
        );

        return $form;
    }

    /**
     *
     * @Route("/{id}/show-voucher", name="cocorico_dashboard_booking_show_voucher", requirements={
     *      "id" = "\d+"
     * })
     * @Method("GET")
     *
     * @Security("is_granted('view_voucher_as_asker', booking)")
     *
     * @param Request $request
     * @param Booking $booking
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showVoucherAction(Request $request, Booking $booking)
    {
        return $this->render(
            'CocoricoCoreBundle:Dashboard/Booking:show_voucher.html.twig',
            array(
                'booking' => $booking
            )
        );
    }

}
