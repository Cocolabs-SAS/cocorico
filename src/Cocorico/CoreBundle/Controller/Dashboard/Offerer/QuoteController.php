<?php

namespace Cocorico\CoreBundle\Controller\Dashboard\Offerer;

use Cocorico\CoreBundle\Entity\Quote;
use Cocorico\CoreBundle\Form\Type\Dashboard\QuoteEditType;
use Cocorico\CoreBundle\Form\Type\Dashboard\QuoteStatusFilterType;
use Cocorico\MessageBundle\Entity\Message;
use Cocorico\MessageBundle\Event\MessageEvent;
use Cocorico\MessageBundle\Event\MessageEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Quote Dashboard controller.
 *
 * @Route("/offerer/quote")
 */
class QuoteController extends Controller
{

    /**
     * Lists all quote entities.
     *
     * @Route("/{page}", name="cocorico_dashboard_quote_offerer", defaults={"page" = 1})
     * @Method("GET")
     *
     * @param  Request $request
     * @param  int     $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, $page)
    {
        $filterForm = $this->createQuoteFilterForm();
        $filterForm->handleRequest($request);

        $status = $request->query->get('status');
        $quoteManager = $this->get('cocorico.quote.manager');
        $quotes = $quoteManager->findByOfferer(
            $this->getUser()->getId(),
            $request->getLocale(),
            $page,
            array($status)
        );

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Quote:index.html.twig',
            array(
                'quotes' => $quotes,
                'user' => $this->getUser(),
                'pagination' => array(
                    'page' => $page,
                    'pages_count' => ceil($quotes->count() / $quoteManager->maxPerPage),
                    'route' => $request->get('_route'),
                    'route_params' => $request->query->all()
                ),
                'filterForm' => $filterForm->createView(),
            )
        );
    }


    /**
     * Finds and displays a Quote entity.
     *
     * @Route("/{id}/show", name="cocorico_dashboard_quote_show_offerer", requirements={
     *      "id" = "\d+",
     * })
     * @Method({"GET", "POST"})
     * @Security("is_granted('view_as_offerer', quote)")
     * @ParamConverter("quote", class="Cocorico\CoreBundle\Entity\Quote")
     *
     * @param Request $request
     * @param Quote $quote
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request, Quote $quote)
    {
        $thread = $quote->getThread();
        /** @var Form $form */
        $form = $this->get('fos_message.reply_form.factory')->create($thread);

        $paramArr = $request->get($form->getName());
        $request->request->set($form->getName(), $paramArr);
        $formHandler = $this->get('fos_message.reply_form.handler');
        $quoteManager = $this->get('cocorico.quote.manager');

        /** @var Message $message */
        if ($message = $formHandler->process($form)) {

            $recipients = $thread->getOtherParticipants($this->getUser());
            $recipient = (count($recipients) > 0) ? $recipients[0] : $this->getUser();

            $messageEvent = new MessageEvent($thread, $recipient, $this->getUser());
            $this->get('event_dispatcher')->dispatch(MessageEvents::MESSAGE_POST_SEND, $messageEvent);

            $quoteManager->notifyQuote($quote, 'ask-msg');


            return $this->redirectToRoute(
                'cocorico_dashboard_quote_show_offerer',
                array('id' => $quote->getId())
            );
        }

        $canBeAcceptedOrRefusedByOfferer = $this->get('cocorico.quote.manager')
            ->canBeAcceptedOrRefusedByOfferer($quote);

        $canBeRefusedByOfferer = $this->get('cocorico.quote.manager')
            ->canBeRefusedByOfferer($quote);

        $preQuoteCanBeAccepted = $this->get('cocorico.quote.manager')
            ->preQuoteCanBeAccepted($quote);

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Quote:show.html.twig',
            array(
                'party' => 'offerer',
                'quote' => $quote,
                'canBeAcceptedOrRefusedByOfferer' => $canBeAcceptedOrRefusedByOfferer,
                'canBeRefusedByOfferer' => $canBeRefusedByOfferer,
                'preQuoteCanBeAccepted' => $preQuoteCanBeAccepted,
                'canShowContactInfo' => $this->get('cocorico.quote.manager')->canShowContactInfo($quote),
                'form' => $form->createView(),
                'other_user' => $quote->getUser(),
                'other_user_rating' => $quote->getUser()->getAverageAskerRating(),
                'user_timezone' => $quote->getTimeZoneOfferer(),
            )
        );
    }


    /**
     * Edit a Quote entity. (Accept or Refuse)
     *
     * @Route("/{id}/edit/{type}", name="cocorico_dashboard_quote_edit_offerer", requirements={
     *      "id" = "\d+",
     *      "type" = "accept|refuse|sent_quote|accept_prequote",
     * })
     * @Method({"GET", "POST"})
     * @Security("is_granted('edit_as_offerer', quote)")
     * @ParamConverter("quote", class="Cocorico\CoreBundle\Entity\Quote")
     *
     * @param Request $request
     * @param Quote $quote
     * @param string  $type The edition type (accept or refuse)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Quote $quote, $type)
    {
        $quoteHandler = $this->get('cocorico.form.handler.quote.offerer.dashboard');
        $form = $this->createEditForm($quote, $type);

        $success = $quoteHandler->process($form);

        $translator = $this->get('translator');
        $session = $this->get('session');
        if ($success == 1) {
            $url = $this->generateUrl(
                'cocorico_dashboard_quote_edit_offerer',
                array(
                    'id' => $quote->getId(),
                    'type' => $type
                )
            );

            $session->getFlashBag()->add(
                'success',
                $translator->trans('quote.edit.success', array(), 'cocorico_quote')
            );

            return $this->redirect($url);
        } elseif ($success < 0) {
            $errorMsg = $translator->trans('quote.new.unknown.error', array(), 'cocorico_quote');
            if ($success == -1 || $success == -2 || $success == -4) {
                $errorMsg = $translator->trans('quote.edit.error', array(), 'cocorico_quote');
            } elseif ($success == -3) {
                $errorMsg = $translator->trans('quote.edit.payin.error', array(), 'cocorico_quote');
            }
            $session->getFlashBag()->add('error', $errorMsg);
        }

        $canBeAcceptedOrRefusedByOfferer = $this->get('cocorico.quote.manager')
            ->canBeAcceptedOrRefusedByOfferer($quote);

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Quote:edit.html.twig',
            array(
                'quote' => $quote,
                'quote_can_be_edited' => true,
                'type' => $type,
                'form' => $form->createView(),
                'other_user' => $quote->getUser(),
                'other_user_rating' => $quote->getUser()->getAverageAskerRating(),
                'user_timezone' => $quote->getTimeZoneOfferer(),
            )
        );

    }

    /**
     * Creates a form to edit a Quote entity.
     *
     * @param Quote $quote The entity
     * @param string  $type    The edition type (accept or refuse)
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Quote $quote, $type)
    {
        $form = $this->get('form.factory')->createNamed(
            'quote',
            QuoteEditType::class,
            $quote,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_dashboard_quote_edit_offerer',
                    array(
                        'id' => $quote->getId(),
                        'type' => $type,
                    )
                ),
            )
        );

        return $form;
    }

    /**
     * Creates a form to filter quotes
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createQuoteFilterForm()
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            QuoteStatusFilterType::class,
            null,
            array(
                'action' => $this->generateUrl(
                    'cocorico_dashboard_quote_offerer',
                    array('page' => 1)
                ),
                'method' => 'GET',
            )
        );

        return $form;
    }


}
