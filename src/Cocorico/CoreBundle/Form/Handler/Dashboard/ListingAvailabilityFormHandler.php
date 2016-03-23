<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\CoreBundle\Form\Handler\Dashboard;

use Cocorico\CoreBundle\Model\Manager\ListingAvailabilityManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handle Listing Availability Form
 *
 */
abstract class ListingAvailabilityFormHandler
{
    /** @var Request $request */
    protected $request;
    /** @var ListingAvailabilityManager $listingAvailabilityManager */
    protected $listingAvailabilityManager;
    /** @var  EntityManager $entityManager */
    protected $entityManager;

    /**
     * @param RequestStack $requestStack
     */
    public function setRequest(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();;
    }

    /**
     * @param ListingAvailabilityManager $listingAvailabilityManager
     */
    public function setListingAvailabilityManager(ListingAvailabilityManager $listingAvailabilityManager)
    {
        $this->listingAvailabilityManager = $listingAvailabilityManager;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Process form
     *
     * @param $form
     *
     * @return int equal to :
     * 1: Success
     * 0: if form is not submitted:
     * -1: if form is not valid
     *
     */
    public function process(Form $form)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $this->request->isMethod('POST')) {
            if ($form->isValid()) {
                $result = $this->onSuccess($form);
            } else {
                $result = -1;//form not valid
            }
        } else {
            $result = 0; //Not submitted
        }

        return $result;
    }

    /**
     * To override
     *
     * @param Form $form
     *
     * @return int equal to :
     * 1: Success
     */
    abstract protected function onSuccess(Form $form);


}