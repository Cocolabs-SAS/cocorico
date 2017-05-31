<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cocorico\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormBuilderInterface;

class ListingFormBuilderEvent extends Event
{
    private $formBuilder;

    /**
     * @param FormBuilderInterface $formBuilder
     */
    public function __construct(FormBuilderInterface $formBuilder)
    {
        $this->formBuilder = $formBuilder;
    }

    /**
     * @return FormBuilderInterface
     */
    public function getFormBuilder()
    {
        return $this->formBuilder;
    }
}
