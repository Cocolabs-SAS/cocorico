<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ContactBundle\Controller\Frontend;

use Cocorico\ContactBundle\Entity\Contact;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Booking controller.
 *
 * @Route("/contact")
 */
class ContactController extends Controller
{
    /**
     * Creates a new Contact entity.
     *
     * @Route("/new", name="cocorico_contact_new")
     *
     * @Method({"GET", "POST"})
     *
     * @param  Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $contact = new Contact();
        $form = $this->createCreateForm($contact);

        $submitted = $this->get('cocorico_contact.form.handler.contact')->process($form);
        if ($submitted !== false) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('contact.new.success', array(), 'cocorico_contact')
            );

            return $this->redirect($this->generateUrl('cocorico_contact_new'));
        }

        return $this->render(
            'CocoricoContactBundle:Frontend:index.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }

    /**
     * Creates a form to create a contact entity.
     *
     * @param Contact $contact The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Contact $contact)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            'contact_new',
            $contact,
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_contact_new')
            )
        );

        return $form;
    }
}
