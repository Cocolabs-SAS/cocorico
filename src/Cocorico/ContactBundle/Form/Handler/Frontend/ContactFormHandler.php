<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\ContactBundle\Form\Handler\Frontend;

use Cocorico\ContactBundle\Entity\Contact;
use Cocorico\ContactBundle\Mailer\TwigSwiftMailer;
use Cocorico\ContactBundle\Model\Manager\ContactManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handle Contact Form
 */
class ContactFormHandler
{
    protected $request;
    protected $contactManager;
    protected $mailer;

    /**
     * @param RequestStack    $requestStack
     * @param ContactManager  $contactManager
     * @param TwigSwiftMailer $mailer
     */
    public function __construct(RequestStack $requestStack, ContactManager $contactManager, TwigSwiftMailer $mailer)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->contactManager = $contactManager;
        $this->mailer = $mailer;
    }

    /**
     * Process form
     *
     * @param Form $form
     *
     * @return Contact|boolean
     */
    public function process(Form $form)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $this->request->isMethod('POST') && $form->isValid()) {
            return $this->onSuccess($form);
        }

        return false;
    }

    /**
     * @param Form $form
     * @return Contact
     */
    private function onSuccess(Form $form)
    {
        /** @var Contact $contact */
        $contact = $form->getData();
        $contact = $this->contactManager->save($contact);
        $this->mailer->sendContactMessage($contact);

        return $contact;
    }
}
