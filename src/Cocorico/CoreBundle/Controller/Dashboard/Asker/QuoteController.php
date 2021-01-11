<?php

namespace Cocorico\CoreBundle\Controller\Dashboard\Asker;

use Cocorico\CoreBundle\Entity\Quote;
use Cocorico\CoreBundle\Form\Type\Dashboard\QuoteEditType;
use Cocorico\CoreBundle\Form\Type\Dashboard\QuoteStatusFilterType;
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
 * @Route("/asker/quote")
 */
class QuoteController extends Controller
{

    /**
     * Lists all quote entities.
     *
     * @Route("/{page}", name="cocorico_dashboard_quote_asker", defaults={"page" = 1})
     * @Method("GET")
     *
     * @param  Request $request
     * @param  int     $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, $page)
    {
        $filterForm = $this->createQuoteFilterForm();
        $filterForm->handleRequest($request);

        $status = $request->query->get('status');
        $quoteManager = $this->get('cocorico.quote.manager');
        $quotes = $quoteManager->findByAsker(
            $this->getUser()->getId(),
            $request->getLocale(),
            $page,
            array($status)
        );

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Quote:index.html.twig',
            array(
                'quotes' => $quotes,
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
     * @Route("/{id}/show", name="cocorico_dashboard_quote_show_asker", requirements={
     *      "id" = "\d+",
     * })
     * @Method({"GET", "POST"})
     * @Security("is_granted('view_as_asker', quote)")
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

        if ($message = $formHandler->process($form)) {

            $recipients = $thread->getOtherParticipants($this->getUser());
            $recipient = (count($recipients) > 0) ? $recipients[0] : $this->getUser();

            $messageEvent = new MessageEvent($thread, $recipient, $this->getUser());
            $this->get('event_dispatcher')->dispatch(MessageEvents::MESSAGE_POST_SEND, $messageEvent);

            $quoteManager->notifyQuote($quote, 'off-msg');

            return $this->redirectToRoute(
                'cocorico_dashboard_quote_show_asker',
                array('id' => $quote->getId())
            );
        }

        $canBeCanceledByAsker = $this->get('cocorico.quote.manager')->canBeCanceledByAsker($quote);
        $canBeAcceptedByAsker = $this->get('cocorico.quote.manager')->canBeAcceptedByAsker($quote);
        $canBeRefusedByAsker = $this->get('cocorico.quote.manager')
            ->canBeRefusedByAsker($quote);

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Quote:show.html.twig',
            array(
                'party' => 'asker',
                'quote' => $quote,
                'canBeCanceledByAsker' => $canBeCanceledByAsker,
                'canBeRefusedByAsker' => $canBeRefusedByAsker,
                'canBeAcceptedByAsker' => $canBeAcceptedByAsker,
                'canShowContactInfo' => $this->get('cocorico.quote.manager')->canShowContactInfo($quote),
                'form' => $form->createView(),
                'other_user' => $quote->getListing()->getUser(),
                'other_user_rating' => $quote->getListing()->getUser()->getAverageOffererRating(),
                'user_timezone' => $quote->getTimeZoneAsker(),
            )
        );
    }


    /**
     * Edit a Quote entity. (Cancel)
     *
     * @Route("/{id}/edit/{type}", name="cocorico_dashboard_quote_edit_asker", requirements={
     *      "id" = "\d+",
     *      "type" = "cancel|accept|refuse",
     * })
     * @Method({"GET", "POST"})
     * @Security("is_granted('edit_as_asker', quote)")
     * @ParamConverter("quote", class="Cocorico\CoreBundle\Entity\Quote")
     *
     * @param Request $request
     * @param Quote $quote
     * @param string  $type The edition type (cancel)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Quote $quote, $type)
    {
        $quoteHandler = $this->get('cocorico.form.handler.quote.asker.dashboard');
        $form = $this->createEditForm($quote, $type);

        $success = $quoteHandler->process($form);

        $translator = $this->get('translator');
        $session = $this->get('session');
        if ($success == 1) {

            $session->getFlashBag()->add(
                'success',
                $translator->trans('quote.edit.success', array(), 'cocorico_quote')
            );

            return $this->redirectToRoute(
                'cocorico_dashboard_quote_edit_asker',
                array(
                    'id' => $quote->getId(),
                    'type' => $type
                )
            );
        } elseif ($success < 0) {
            $errorMsg = $translator->trans('quote.new.unknown.error', array(), 'cocorico_quote');
            if ($success == -1 || $success == -2 || $success == -4) {
                $errorMsg = $translator->trans('quote.edit.error', array(), 'cocorico_quote');
            } elseif ($success == -3) {
                $errorMsg = $translator->trans('quote.edit.payin.error', array(), 'cocorico_quote');
            }
            $session->getFlashBag()->add('error', $errorMsg);
        }

        $canBeCanceledByAsker = $this->get('cocorico.quote.manager')->canBeCanceledByAsker($quote);

        return $this->render(
            'CocoricoCoreBundle:Dashboard/Quote:edit.html.twig',
            array(
                'quote' => $quote,
                'quote_can_be_edited' => true,
                'type' => $type,
                'form' => $form->createView(),
                'other_user' => $quote->getListing()->getUser(),
                'other_user_rating' => $quote->getListing()->getUser()->getAverageOffererRating(),
                'user_timezone' => $quote->getTimeZoneAsker(),
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
                    'cocorico_dashboard_quote_edit_asker',
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
                    'cocorico_dashboard_quote_asker',
                    array('page' => 1)
                ),
                'method' => 'GET',
            )
        );

        return $form;
    }

    /**
     *
     * @Route("/{id}/show-voucher", name="cocorico_dashboard_quote_show_voucher", requirements={
     *      "id" = "\d+"
     * })
     * @Method("GET")
     *
     * @Security("is_granted('view_voucher_as_asker', quote)")
     *
     * @param Request $request
     * @param Quote $quote
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showVoucherAction(Request $request, Quote $quote)
    {
        return $this->render(
            'CocoricoCoreBundle:Dashboard/Quote:show_voucher.html.twig',
            array(
                'quote' => $quote
            )
        );
    }

}
