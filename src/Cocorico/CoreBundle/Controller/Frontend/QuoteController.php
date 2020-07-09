<?php

namespace Cocorico\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Entity\Quote;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Form\Type\Frontend\QuoteType;

use Cocorico\CoreBundle\Event\QuoteEvent;
use Cocorico\CoreBundle\Event\QuoteEvents;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Quote controller.
 *
 * @Route("/quote")
 */
class QuoteController extends Controller
{
    /**
     * Creates a new Quote entity.
     *
     * @Route("/{listing_id}/{prestaStartDate}/new",
     *      name="cocorico_quote_new",
     *      requirements={
     *          "listing_id" = "\d+"
     *      },
     * )
     *
     *
     * @Security("is_granted('quote', listing) and not has_role('ROLE_ADMIN') and has_role('ROLE_USER')")
     *
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing", options={"id" = "listing_id"}, converter="doctrine.orm")
     * @ParamConverter("prestaStartDate", options={"format": "Y-m-d-H:i"}, converter="datetime")
     *
     *
     * @Method({"GET", "POST"})
     *
     * @param Request   $request
     * @param Listing   $listing
     * @param \DateTime $prestaStartDate format yyyy-mm-dd-H:i
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(
        Request $request,
        Listing $listing,
        \DateTime $prestaStartDate=Null
    ) {
        $communication = (string)$request->query->get('communication');
        $budget = (int)$request->query->get('budget');
        $quoteHandler = $this->get('cocorico.form.handler.quote_base');
        $quote = $quoteHandler->init($this->getUser(), $listing, $communication, $budget, $prestaStartDate);
        //Availability is validated through QuoteValidator and amounts are setted through Form Event PRE_SET_DATA
        $form = $this->createCreateForm($quote);

        $success = $quoteHandler->process($form);
        if ($success === 1) {//Success
            $event = new QuoteEvent($quote);

            try {
                $this->get('event_dispatcher')->dispatch(QuoteEvents::QUOTE_NEW_SUBMITTED, $event);
                $response = $event->getResponse();

                if ($response === null) {//No response means we can create new quote
                    if ($quote) {
                        //New Quote confirmation
                        $this->get('session')->getFlashBag()->add(
                            'success',
                            $this->get('translator')->trans('quote.new.success', array(), 'cocorico_quote')
                        );

                        $response = new RedirectResponse(
                            $this->generateUrl(
                                'cocorico_dashboard_quote_show_asker',
                                array('id' => $quote->getId())
                            )
                        );
                    } else {
                        throw new \Exception('quote.new.form.error');
                    }
                }

                return $response;
            } catch (\Exception $e) {
                //Errors message are created in event subscribers
                $this->get('session')->getFlashBag()->add(
                    'error',
                    /** @Ignore */
                    $this->get('translator')->trans($e->getMessage(), array(), 'cocorico_quote')
                );
            }
        } else {
            $this->addFormMessagesToFlashBag($success);
        }

        //Breadcrumbs
        $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
        $breadcrumbs->addQuoteNewItems($request, $quote);

        return $this->render(
            'CocoricoCoreBundle:Frontend/Quote:new.html.twig',
            array(
                'quote' => $quote,
                'form' => $form->createView(),
                //Used to hide errors fields message when a secondary submission (Voucher, Delivery, ...) is done successfully
                'display_errors' => ($success < 2)
            )
        );
    }

    /**
     * Creates a form to create a Quote entity.
     *
     * @param Quote $quote The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Quote $quote)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            QuoteType::class,
            $quote,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_quote_new',
                    array(
                        'listing_id' => $quote->getListing()->getId(),
                        'communication' => $quote->getCommunication(),
                        'budget' => $quote->getBudget(),
                        'prestaStartDate' => is_null($quote->getPrestaStartDate()) ? '' : $quote->getPrestaStartDate()->format('Y-m-d-H:i'),
                    )
                ),
            )
        );

        return $form;
    }

    /**
     * Form message for specific bundles
     *
     * @param int $success
     */
    private function addFormMessagesToFlashBag($success)
    {
        $session = $this->get('session');
        $translator = $this->get('translator');

        if ($success === 2) {//Voucher code is valid
            $session->getFlashBag()->add(
                'success_voucher',
                $translator->trans('quote.new.voucher.success', array(), 'cocorico_quote')
            );
        } elseif ($success === 3) {//Delivery is valid
            $session->getFlashBag()->add(
                'success',
                $translator->trans('quote.new.delivery.success', array(), 'cocorico_quote')
            );
        } elseif ($success === 4) {//Options is valid
            $session->getFlashBag()->add(
                'success',
                $translator->trans('quote.new.options.success', array(), 'cocorico_quote')
            );
        } elseif ($success < 0) {//Errors
            $this->addFlashError($success);
        }
    }

    /**
     * @param $success
     */
    private function addFlashError($success)
    {
        $translator = $this->get('translator');
        $errorMsg = $translator->trans('quote.new.unknown.error', array(), 'cocorico_quote'); //-4
        $flashType = 'error';

        if ($success == -1) {
            $errorMsg = $translator->trans('quote.new.form.error', array(), 'cocorico_quote');
        } elseif ($success == -2) {
            $errorMsg = $translator->trans('quote.new.self_quote.error', array(), 'cocorico_quote');
        } elseif ($success == -3) {
            $errorMsg = $translator->trans('quote.new.voucher_code.error', array(), 'cocorico_quote');
            $flashType = 'error_voucher';
        } elseif ($success == -4) {
            $errorMsg = $translator->trans('quote.new.voucher_amount.error', array(), 'cocorico_quote');
            $flashType = 'error_voucher';
        } elseif ($success == -5) {
            $errorMsg = $translator->trans('quote.new.delivery_max.error', array(), 'cocorico_quote');
            $flashType = 'error';
        } elseif ($success == -6) {
            $errorMsg = $translator->trans('quote.new.delivery.error', array(), 'cocorico_quote');
            $flashType = 'error';
        }

        $this->get('session')->getFlashBag()->add($flashType, $errorMsg);
    }



    /**
     * Creates a new Quote form.
     *
     * @param  Listing $listing
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function quoteFormAction(Listing $listing)
    {
        $quoteHandler = $this->get('cocorico.form.handler.quote');
        $quote = $quoteHandler->init($this->getUser(), $listing);

        $form = $this->createQuoteForm($quote);

        return $this->render(
            '@CocoricoCore/Frontend/Quote/form_quote.html.twig',
            array(
                'form' => $form->createView(),
                'quote' => $quote
            )
        );
    }

    /**
     * Creates a form for Quote.
     *
     * @param Quote $quote The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createQuoteForm(Quote $quote)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            QuoteType::class,
            $quote,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl(
                    'cocorico_quote',
                     array(
                         'listing_id' => $quote->getListing()->getId()
                     )
                )
            )
        );

        return $form;
    }


    /**
     * Get quote
     *
     * @Route("/{listing_id}/quote", name="cocorico_quote", requirements={"listing_id" = "\d+"})
     * @Security("is_granted('quote', listing)")
     *
     * @ParamConverter("listing", class="CocoricoCoreBundle:Listing", options={"id" = "listing_id"})
     *
     * @Method({"POST"})
     *
     * @param Request  $request
     * @param  Listing $listing
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function getQuoteAction(Request $request, Listing $listing)
    {
        $quoteHandler = $this->get('cocorico.form.handler.quote');
        $quote = $quoteHandler->init($this->getUser(), $listing);

        $form = $this->createQuoteForm($quote);
        $form->handleRequest($request);

        //Return form if Ajax request
        if ($request->isXmlHttpRequest()) {
            return
                $this->render(
                    '@CocoricoCore/Frontend/Quote/form_quote.html.twig',
                    array(
                        'form' => $form->createView(),
                        'quote' => $quote
                    )
                );
        } else {//Redirect to new Quote page if no ajax request
            return $this->redirect(
                $this->generateUrl(
                    'cocorico_quote_new',
                    array(
                        'listing_id' => $listing->getId(),
                        'communication' => $quote->getCommunication(),
                        'budget' => $quote->getBudget(),
                        'prestaStartDate' => is_null($quote->getPrestaStartDate()) ? '' : $quote->getPrestaStartDate()->format('Y-m-d-H:i'),
                    )
                )
            );
        }
    }
}
