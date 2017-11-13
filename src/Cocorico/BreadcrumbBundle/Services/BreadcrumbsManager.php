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
use Cocorico\CoreBundle\Model\ListingSearchRequest;
use Cocorico\GeoBundle\Entity\Area;
use Cocorico\GeoBundle\Entity\City;
use Cocorico\GeoBundle\Entity\Country;
use Cocorico\GeoBundle\Entity\Department;
use Cocorico\MessageBundle\Entity\Thread;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class BreadcrumbsManager implements TranslationContainerInterface
{
    protected $breadcrumbs;
    protected $router;
    protected $loader;
    protected $translator;
    protected $em;

    /**
     * @param Breadcrumbs         $breadcrumbs
     * @param RouterInterface     $router
     * @param LoaderInterface     $loader
     * @param TranslatorInterface $translator
     * @param EntityManager       $em
     */
    public function __construct(
        Breadcrumbs $breadcrumbs,
        RouterInterface $router,
        LoaderInterface $loader,
        TranslatorInterface $translator,
        EntityManager $em
    ) {
        $this->breadcrumbs = $breadcrumbs;
        $this->router = $router;
        $this->loader = $loader;
        $this->translator = $translator;
        $this->em = $em;
    }

    /**
     * Add breadcrumb items from breadcrumbs.yml file (used for dashboard pages)
     * For frontend pages, breadcrumbs are added from related action
     *
     * @param Request $request
     * @param string  $routeName
     */
    public function addItemsFromYAML(Request $request, $routeName)
    {
        //Breadcrumbs definition
        $breadcrumbLinks = $this->loader->load('breadcrumbs.yml');

        if (isset($breadcrumbLinks[$routeName]) && is_array($breadcrumbLinks[$routeName])) {

            $this->addPreItems($request, strpos($routeName, 'dashboard') !== false);

            foreach ($breadcrumbLinks[$routeName] as $breadcrumb) {
                $text = '';
                if (isset($breadcrumb['text']) && !is_array($breadcrumb['text'])) {
                    $text = $breadcrumb['text'];
                } elseif (isset($breadcrumb['text']) && is_array($breadcrumb['text'])) {
                    $entityId = $breadcrumb['text']['entityId'];
                    $entityId = $request->get($entityId);
                    $entityName = $breadcrumb['text']['entityName'];
                    $repository = $this->em->getRepository($entityName);
                    $entity = $repository->find($entityId);
                    $entityMethod = $breadcrumb['text']['entityMethod'];
                    $text = $entity->$entityMethod();
                }

                $url = '';
                if (isset($breadcrumb['path'])) {
                    $url = $breadcrumb['path'];
                } elseif (isset($breadcrumb['route'])) {
                    $url = $this->router->generate($breadcrumb['route']);
                }

                if ($text) {
                    $this->breadcrumbs->addItem(
                    /** @Ignore */
                        $this->translator->trans($text, array(), 'cocorico_breadcrumbs'),
                        $url
                    );
                }
            }
        }
    }

    /**
     * Add first breadcrumb items
     *
     * @param Request $request
     * @param bool    $forDashboard
     */
    public function addPreItems(Request $request, $forDashboard)
    {
        $this->breadcrumbs->addItem(
            $this->translator->trans('Home', array(), 'cocorico_breadcrumbs'),
            $this->router->generate('cocorico_home')
        );

        if ($forDashboard) {
            //Asker or offerer
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
    }

    /**
     * Add breadcrumbs to thread view action
     *
     * @param Request $request
     * @param Thread  $threadObj
     * @param User    $user
     */
    public function addThreadViewItems(Request $request, $threadObj, $user)
    {
        $this->addPreItems($request, true);
        $this->breadcrumbs->addItem(
            $this->translator->trans('Messages', array(), 'cocorico_breadcrumbs'),
            $this->router->generate('cocorico_dashboard_message')
        );

        $users = $threadObj->getOtherParticipants($user);
        $user = (count($users) > 0) ? $users[0] : $user;

        $this->breadcrumbs->addItem(
            $this->translator->trans(
                'Discussion with %name%',
                array('%name%' => $user->getName()),
                'cocorico_breadcrumbs'
            )
        );
    }


    /**
     * Add breadcrumbs to listing show action
     * todo: Optimize SQL queries
     *
     * @param Request $request
     * @param Listing $listing
     */
    public function addListingShowItems(Request $request, $listing)
    {
        $this->addPreItems($request, false);
        $this->breadcrumbs->addItem(
            $this->translator->trans('Search results', array(), 'cocorico_breadcrumbs')
        );

        $coordinate = $listing->getLocation()->getCoordinate();
        $urlParams = array(
            'location[area]' => '',
            'location[department]' => '',
            'location[city]' => '',
            'location[zip]' => '',
            'location[route]' => '',
            'location[streetNumber]' => '',
            'page' => 1,
        );

        $country = $coordinate->getCountry();
        $places = array('country', 'area', 'department', 'city');

        foreach ($places as $place) {
            $coordinateMethod = "get" . ucfirst($place);
            /** @var Country|Area|Department|City $placeEntity */
            if ($placeEntity = $coordinate->$coordinateMethod()) {
                $url = '';
                if ($geocoding = $placeEntity->getGeocoding()) {
                    $address = (string)$placeEntity . ($place != 'country' ? ', ' . $country : '');
                    $urlParams['location[address]'] = $address;
                    $urlParams['location[lat]'] = $geocoding->getLat();
                    $urlParams['location[lng]'] = $geocoding->getLng();
                    $urlParams['location[viewport]'] = $geocoding->getViewport();
                    $urlParams['location[addressType]'] = $geocoding->getAddressType();
                    //Adapt breadcrumbs items length in result page. Allow to know what is the place type search.
                    $urlParams['location[' . $place . ']'] = (string)$placeEntity;
                    $urlParams['location[country]'] = $country->getCode();
                    $url = $this->router->generate('cocorico_listing_search_result', $urlParams);
                }
                $this->breadcrumbs->addItem((string)$placeEntity, $url);
            }
        }

        if ($coordinate->getZip()) {
            $this->breadcrumbs->addItem($coordinate->getZip());
        }

        $this->breadcrumbs->addItem($listing->getTitle());
    }

    /**
     * Add breadcrumbs to listing result action
     *
     * @param Request              $request
     * @param ListingSearchRequest $listingSearchRequest
     */
    public function addListingResultItems(Request $request, $listingSearchRequest)
    {
        $this->addPreItems($request, false);
        $this->breadcrumbs->addItem(
            $this->translator->trans('Search results', array(), 'cocorico_breadcrumbs')
        );

        //Location of search request
        $location = $listingSearchRequest->getLocation();
        $urlParams = array(
            'location[area]' => '',
            'location[department]' => '',
            'location[city]' => '',
            'location[zip]' => '',
            'location[route]' => '',
            'location[streetNumber]' => '',
            'page' => 1,
        );

        //Get geocoding info for each search location places
        $country = $area = $department = $city = null;
        if ($location->getCountry()) {
            $repo = $this->em->getRepository("CocoricoGeoBundle:Country");
            $country = $repo->findOneByCode($location->getCountry());

            if ($country && $location->getArea()) {
                $repo = $this->em->getRepository("CocoricoGeoBundle:Area");
                $area = $repo->findOneByNameAndCountry($location->getArea(), $country);
//                $urlParams['location[country]'] = $location->getCountry();

                if ($area && $location->getDepartment()) {
                    $repo = $this->em->getRepository("CocoricoGeoBundle:Department");
                    $department = $repo->findOneByNameAndArea($location->getDepartment(), $area);
//                    $urlParams['location[area]'] = $location->getArea();

                    if ($department && $location->getCity()) {
                        $repo = $this->em->getRepository("CocoricoGeoBundle:City");
                        $city = $repo->findOneByNameAndDepartment($location->getCity(), $department);
//                        $urlParams['location[department]'] = $location->getDepartment();
                    }
                }
            }
        }

        $places = array(
            'country',
            'area',
            'department',
            'city',
        );

        foreach ($places as $place) {
            /** @var Country|Area|Department|City $placeEntity */
            if ($placeEntity = $$place) {
                $url = '';
                if ($geocoding = $placeEntity->getGeocoding()) {
                    $address = (string)$placeEntity . ($place != 'country' ? ', ' . $country : '');
                    $urlParams['location[address]'] = $address;
                    $urlParams['location[lat]'] = $geocoding->getLat();
                    $urlParams['location[lng]'] = $geocoding->getLng();
                    $urlParams['location[viewport]'] = $geocoding->getViewport();
                    $urlParams['location[addressType]'] = $geocoding->getAddressType();
                    $urlParams['location[' . $place . ']'] = (string)$placeEntity;
                    $urlParams['location[country]'] = $country->getCode();
                    $url = $this->router->generate('cocorico_listing_search_result', $urlParams);
                }
                $this->breadcrumbs->addItem((string)$placeEntity, $url);
            }
        }

        if ($location->getZip()) {
            $this->breadcrumbs->addItem($location->getZip());
        }
    }

    /**
     * Add breadcrumbs to listing show action
     *
     * @param Request $request
     * @param Booking $booking
     */
    public function addBookingNewItems(Request $request, $booking)
    {
        $listing = $booking->getListing();
        $this->addListingShowItems($request, $listing);

        $this->breadcrumbs->offsetUnset($this->breadcrumbs->count() - 1);
        $url = $this->router->generate('cocorico_listing_show', array('slug' => $listing->getSlug()));;
        $this->breadcrumbs->addItem($listing->getTitle(), $url);
        $this->breadcrumbs->addItem($this->translator->trans('Reservation', array(), 'cocorico_breadcrumbs'));
    }


    /**
     * Add breadcrumbs to profile show action
     *
     * @param Request $request
     * @param User    $user
     */
    public function addProfileShowItems(Request $request, $user)
    {
        $this->addPreItems($request, false);
        $this->breadcrumbs->addItem(
            $this->translator->trans('Profile', array(), 'cocorico_breadcrumbs')
        );

        $this->breadcrumbs->addItem($user->getName());
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