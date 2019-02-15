<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ReviewBundle\Controller\Dashboard;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\ReviewBundle\Entity\Review;
use Cocorico\ReviewBundle\Form\Type\Dashboard\ReviewType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Review controller.
 *
 * @Route("/review")
 */
class ReviewController extends Controller
{
    /**
     * Creates a new rating for a booking.
     *
     * @Route("/new/{booking_id}", name="cocorico_dashboard_review_new")
     *
     * @Method({"GET", "POST"})
     * @ParamConverter("booking", class="Cocorico\CoreBundle\Entity\Booking",
     *          options={"id" = "booking_id"})
     * @Security("is_granted('add', booking)")
     *
     * @param  Request $request
     * @param  Booking $booking
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */
    public function newAction(Request $request, Booking $booking)
    {
        $user = $this->getUser();
        $formHandler = $this->get('cocorico.form.handler.review');
        $translator = $this->get('translator');

        //Reviews form handling
        $review = $formHandler->create($booking, $user);
        if (!$review) {
            throw new AccessDeniedException('Review already added for this booking by user');
        }
        $form = $this->createCreateForm($review);
        $submitted = $formHandler->process($form);
        if ($submitted !== false) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $translator->trans('review.new.success', array(), 'cocorico_review')
            );

            return $this->redirect($this->generateUrl('cocorico_dashboard_reviews_made'));
        }

        return $this->render(
            'CocoricoReviewBundle:Dashboard/Review:new.html.twig',
            array(
                'form' => $form->createView(),
                'booking' => $booking,
                'reviewTo' => $review->getReviewTo(),
                'user_timezone' => $user == $booking->getUser() ?
                    $booking->getTimeZoneAsker() : $booking->getTimeZoneOfferer()
            )
        );
    }

    /**
     * Creates a form to create a review entity.
     *
     * @param review $review The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Review $review)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            ReviewType::class,
            $review
        );

        return $form;
    }


    /**
     * List of reviews made by the user
     *
     * @Route("/reviews-made", name="cocorico_dashboard_reviews_made")
     *
     * @Method({"GET"})
     *
     * @param  Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function madeReviewsAction(Request $request)
    {
        $user = $this->getUser();
        $userType = $request->getSession()->get('profile', 'asker');

        $reviewManager = $this->get('cocorico.review.manager');
        $madeReviews = $reviewManager->getUserReviews($userType, $user, 'made');
        $unreviewedBookings = $reviewManager->getUnreviewedBookings($userType, $user);

        return $this->render(
            'CocoricoReviewBundle:Dashboard/Review:index.html.twig',
            array(
                'reviews' => $madeReviews,
                'unreviewed_bookings' => $unreviewedBookings,
                'reviews_type' => 'made'
            )
        );
    }

    /**
     * List of reviews received to the user user
     *
     * @Route("/reviews-received", name="cocorico_dashboard_reviews_received")
     *
     * @Method({"GET"})
     *
     * @param  Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function receivedReviewsAction(Request $request)
    {
        $user = $this->getUser();
        $userType = $request->getSession()->get('profile', 'asker');

        $reviewManager = $this->get('cocorico.review.manager');

        $receivedReviews = $reviewManager->getUserReviews($userType, $user, 'received');
        $unreviewedBookings = $reviewManager->getUnreviewedBookings($userType, $user);

        return $this->render(
            'CocoricoReviewBundle:Dashboard/Review:index.html.twig',
            array(
                'reviews' => $receivedReviews,
                'unreviewed_bookings' => $unreviewedBookings,
                'reviews_type' => 'received'
            )
        );
    }
}
