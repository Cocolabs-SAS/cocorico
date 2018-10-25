<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Mailer;

use Cocorico\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class TwigSwiftMailer implements MailerInterface
{
    protected $mailer;
    protected $router;
    protected $twig;
    protected $requestStack;
    protected $fromEmail;
    protected $templates;

    /**
     * @param \Swift_Mailer         $mailer
     * @param UrlGeneratorInterface $router
     * @param \Twig_Environment     $twig
     * @param RequestStack          $requestStack
     * @param array                 $parameters
     * @param array                 $templates
     */
    public function __construct(
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        \Twig_Environment $twig,
        RequestStack $requestStack,
        array $parameters,
        array $templates
    ) {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $parameters = $parameters['parameters'];

        $this->fromEmail = $parameters['cocorico_from_email'];
        $this->locales = $parameters['cocorico_locales'];
        $this->locale = $parameters['cocorico_locale'];

        if ($requestStack->getCurrentRequest()) {
            $this->locale = $requestStack->getCurrentRequest()->getLocale();
        }

        $this->templates = $templates['templates'];
    }

    /**
     * @param  int               $threadId
     * @param UserInterface|User $recipient
     * @param UserInterface      $sender
     */
    public function sendNewThreadMessageToUser($threadId, UserInterface $recipient, UserInterface $sender)
    {
        $userLocale = $recipient->guessPreferredLanguage($this->locales, $this->locale);
        $template = $this->templates['new_thread_message_user'];

        $threadUrl = $this->router->generate(
            'cocorico_dashboard_message_thread_view',
            array(
                'threadId' => $threadId,
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $recipient,
            'sender' => $sender,
            'thread_url' => $threadUrl
        );

        $this->sendMessage($template, $context, $this->fromEmail, $recipient->getEmail());
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

        if (isset($context['user'])) {
            /** @var User $user */
            $user = $context['user'];
            $context['user_locale'] = $user->guessPreferredLanguage($this->locales, $this->locale);
            $context['locale'] = $context['user_locale'];
            $context['app']['request']['locale'] = $context['user_locale'];
        }
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
