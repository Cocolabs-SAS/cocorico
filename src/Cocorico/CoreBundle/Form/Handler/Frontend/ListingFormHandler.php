<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\CoreBundle\Form\Handler\Frontend;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Model\Manager\ListingManager;
use Cocorico\UserBundle\Form\Handler\RegistrationFormHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handle Listing Form
 *
 */
class ListingFormHandler
{
    protected $request;
    protected $listingManager;
    protected $registrationHandler;


    /**
     * @param RequestStack            $requestStack
     * @param ListingManager          $listingManager
     * @param RegistrationFormHandler $registrationHandler
     */
    public function __construct(
        RequestStack $requestStack,
        ListingManager $listingManager,
        RegistrationFormHandler $registrationHandler
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->listingManager = $listingManager;
        $this->registrationHandler = $registrationHandler;
    }


    /**
     * @return Listing
     */
    public function init()
    {
        $listing = new Listing();
        $listing = $this->addImages($listing);
        $listing = $this->addCategories($listing);

        return $listing;
    }

    /**
     * Process form
     *
     * @param Form $form
     *
     * @return Booking|string
     */
    public function process($form)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $this->request->isMethod('POST') && $form->isValid()) {
            return $this->onSuccess($form);
        }

        return false;
    }

    /**
     * @param Form $form
     * @return boolean
     */
    private function onSuccess(Form $form)
    {
        /** @var Listing $listing */
        $listing = $form->getData();

        //Login is done in BookingNewType form
        if ($this->request->request->get('_username') || $this->request->request->get('_password')) {
        } //Register : Authentication and Welcome email after registration
        elseif ($form->has('user') && $form->get('user')->has("email")) {
            $user = $listing->getUser();
            $this->registrationHandler->handleRegistration($user);
        }

        $this->listingManager->save($listing);

        return true;
    }


    /**
     * @param  Listing $listing
     * @return Listing
     */
    private function addImages(Listing $listing)
    {
        //Files to upload
        $imagesUploaded = $this->request->request->get("listing");
        $imagesUploaded = $imagesUploaded["image"]["uploaded"];

        if ($imagesUploaded) {
            $imagesUploadedArray = explode(",", trim($imagesUploaded, ","));
            $listing = $this->listingManager->addImages(
                $listing,
                $imagesUploadedArray
            );
        }

        return $listing;
    }

    /**
     * Add selected categories and corresponding fields values from post parameters while listing deposit
     *
     * @param  Listing $listing
     * @return Listing
     */
    public function addCategories(Listing $listing)
    {
        $categories = $this->request->request->get("listing_categories");

        $listingCategories = isset($categories["listingListingCategories"]) ? $categories["listingListingCategories"] : array();
        $listingCategoriesValues = isset($categories["categoriesFieldsSearchableValuesOrderedByGroup"]) ? $categories["categoriesFieldsSearchableValuesOrderedByGroup"] : array();

        if ($categories) {
            $listing = $this->listingManager->addCategories(
                $listing,
                $listingCategories,
                $listingCategoriesValues
            );
        }

        return $listing;
    }

}