<?php

namespace Cocorico\CoreBundle\Controller\Frontend;

use Cocorico\CoreBundle\Entity\Quote;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Form\Type\Frontend\QuoteType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @Route("/{listing_id}/{start}/{end}/new",
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
     * @param string $communication
     * @param integer $budget
     * @param \DateTime $prestaStartDate format yyyy-mm-dd-H:i
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(
        Request $request,
        Listing $listing,
        string $communication,
        integer $budget,
        \DateTime $prestaStartDate
    ) {
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
            QuoteNewType::class,
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
