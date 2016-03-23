<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BreadcrumbBundle\Services;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class BreadcrumbsManager implements TranslationContainerInterface
{
    /**
     * @param Breadcrumbs         $breadcrumbs
     * @param RouterInterface     $router
     * @param LoaderInterface     $loader
     * @param TranslatorInterface $translator
     */
    public function __construct(
        Breadcrumbs $breadcrumbs,
        RouterInterface $router,
        LoaderInterface $loader,
        TranslatorInterface $translator
    ) {
        $this->breadcrumbs = $breadcrumbs;
        $this->router = $router;
        $this->loader = $loader;
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     * @param string  $routeName
     */
    public function addBreadcrumbsForRoute(Request $request, $routeName)
    {
        $breadcrumbLinks = $this->loader->load('breadcrumbs.yml');

        if (isset($breadcrumbLinks[$routeName]) && is_array($breadcrumbLinks[$routeName])) {

            $this->addPreItems($request);

            foreach ($breadcrumbLinks[$routeName] as $value) {
                $url = '';
                if (isset($value['path'])) {
                    $url = $value['path'];
                } elseif (isset($value['route'])) {
                    $url = $this->router->generate($value['route']);
                }

                $this->breadcrumbs->addItem(
                /** @Ignore */
                    $this->translator->trans($value['text'], array(), 'cocorico_breadcrumbs'),
                    $url
                );
            }
        }
    }

    /**
     * @param Request $request
     */
    public function addPreItems(Request $request)
    {
        $this->breadcrumbs->addItem(
            $this->translator->trans('Home', array(), 'cocorico_breadcrumbs'),
            $this->router->generate('cocorico_home')
        );
        $url = $this->router->generate('cocorico_dashboard_message');
        if ($request->getSession()->get('profile', 'asker') == 'asker') {
            $this->breadcrumbs->addItem(
                $this->translator->trans('Asker', array(), 'cocorico_breadcrumbs'),
                $url
            );
        } else {
            $this->breadcrumbs->addItem(
                $this->translator->trans('Offerer', array(), 'cocorico_breadcrumbs'),
                $url
            );
        }
    }

    /**
     * @param Request $request
     * @param Listing $listing
     */
    public function addListingItem(Request $request, Listing $listing)
    {
        $this->addPreItems($request);

        $this->breadcrumbs->addItem(
            $this->translator->trans('Listing', array(), 'cocorico_breadcrumbs'),
            $this->router->generate('cocorico_dashboard_listing')
        );

        $this->breadcrumbs->addItem(
            $listing->getTitle(),
            $this->router->generate(
                'cocorico_dashboard_listing_edit_presentation',
                array('id' => $listing->getId())
            )
        );
    }

    /**
     * @param string $text
     * @param string $path
     */
    public function addItem($text, $path)
    {
        $this->breadcrumbs->addItem($text, $path);
    }

    /**
     * @param Request $request
     * @param Booking $booking
     */
    public function addBookingShowBreadcrumbs(Request $request, Booking $booking)
    {
        $this->addPreItems($request);

        if ($request->getSession()->get('profile', 'asker') == 'asker') {
            $this->breadcrumbs->addItem(
                $this->translator->trans('Bookings', array(), 'cocorico_breadcrumbs'),
                $this->router->generate('cocorico_dashboard_booking_asker')
            );
        } else {
            $this->breadcrumbs->addItem(
                $this->translator->trans('Bookings', array(), 'cocorico_breadcrumbs'),
                $this->router->generate('cocorico_dashboard_booking_offerer')
            );
        }

        $this->breadcrumbs->addItem(
            $booking->getListing()->getTitle()
        );
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages[] = new Message("Messages", 'cocorico_breadcrumbs');
        $messages[] = new Message("Payments", 'cocorico_breadcrumbs');
        $messages[] = new Message("Comments", 'cocorico_breadcrumbs');
        $messages[] = new Message("Profile", 'cocorico_breadcrumbs');
        $messages[] = new Message("About me", 'cocorico_breadcrumbs');
        $messages[] = new Message("Payment Information", 'cocorico_breadcrumbs');
        $messages[] = new Message("Contact Information", 'cocorico_breadcrumbs');

        return $messages;
    }

}