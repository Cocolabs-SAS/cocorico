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
     * @param Booking $booking The entity
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
                    '@CocoricoCore/Frontend/Booking/form_quote.html.twig',
                    array(
                        'form' => $form->createView(),
                        'quote' => $quote
                    )
                );
        } else {//Redirect to new Booking page if no ajax request
            return $this->redirect(
                $this->generateUrl(
                    'cocorico_quote_new',
                    array(
                        'listing_id' => $listing->getId(),
                        'start' => $quote->getStart()->format('Y-m-d-H:i'),
                        'end' => $quote->getEnd()->format('Y-m-d-H:i'),
                    )
                )
            );
        }
    }
}
