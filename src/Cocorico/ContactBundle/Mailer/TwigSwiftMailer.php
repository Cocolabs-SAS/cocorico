<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ContactBundle\Mailer;

use Cocorico\ContactBundle\Entity\Contact;

class TwigSwiftMailer implements MailerInterface
{
    protected $mailer;
    protected $twig;
    protected $parameters;
    protected $templates;
    protected $fromEmail;
    protected $contactEmail;
    protected $locale;

    /**
     * @param \Swift_Mailer     $mailer
     * @param \Twig_Environment $twig
     * @param array             $parameters
     * @param array             $templates
     */
    public function __construct(
        \Swift_Mailer $mailer,
        \Twig_Environment $twig,
        array $parameters,
        array $templates
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;

        /** parameters */
        $this->parameters = $parameters['parameters'];
        $this->fromEmail = $parameters['parameters']['cocorico_contact_from_email'];
        $this->contactEmail = $parameters['parameters']['cocorico_contact_contact_email'];
        $this->templates = $templates;
        $this->locale = $parameters['parameters']['cocorico_locale'];
    }

    /**
     * @param Contact $contact
     */
    public function sendContactMessage(Contact $contact)
    {
        $template = $this->templates['templates']['contact_message'];

        $context = array(
            'contact' => $contact,
            'user_locale' => $this->locale,
        );

        $this->sendMessage($template, $context, $this->fromEmail, $this->contactEmail);
    }


    /**
     * @param string $templateName
     * @param array  $context
     * @param string $fromEmail
     * @param string $toEmail
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $context['user_locale'] = $this->locale;
        $context['locale'] = $this->locale;
        $context['app']['request']['locale'] = $this->locale;

        /** @var \Twig_Template $template */
        $template = $this->twig->loadTemplate($templateName);
        $context = $this->twig->mergeGlobals($context);

        $subject = $template->renderBlock('subject', $context);
        $context["message"] = $template->renderBlock('message', $context);

        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message
                ->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        $this->mailer->send($message);
    }

}
