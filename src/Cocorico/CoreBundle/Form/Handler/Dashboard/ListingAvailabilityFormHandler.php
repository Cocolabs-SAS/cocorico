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

use Cocorico\CoreBundle\Entity\Listing;
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
    /** @var ListingAvailabilityManager $availabilityManager */
    protected $availabilityManager;
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
     * @param ListingAvailabilityManager $availabilityManager
     */
    public function setListingAvailabilityManager(ListingAvailabilityManager $availabilityManager)
    {
        $this->availabilityManager = $availabilityManager;
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
    public function processMany(Form $form)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $this->request->isMethod('POST')) {
            if ($form->isValid()) {
                $result = $this->onSuccessMany($form);
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
    abstract protected function onSuccessMany(Form $form);

    /**
     * Process form
     *
     * @param Form    $form
     * @param Listing $listing
     * @param string  $day
     * @param string  $start_time
     * @param string  $end_time
     *
     * @return int equal to :
     * 1: Success
     * 0: if form is not submitted:
     * -1: if form is not valid
     *
     */
    public function processOne(Form $form, Listing $listing, $day, $start_time, $end_time)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $this->request->isMethod('POST')) {
            if ($form->isValid()) {
                $result = $this->onSuccessOne($form, $listing, $day, $start_time, $end_time);
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
     * @param Form    $form
     * @param Listing $listing
     * @param string  $day
     * @param string  $start_time
     * @param string  $end_time
     *
     * @return int equal to :
     * 1: Success
     */
    abstract protected function onSuccessOne(Form $form, Listing $listing, $day, $start_time, $end_time);

}