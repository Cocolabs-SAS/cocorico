<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Dashboard;

use Cocorico\CoreBundle\Event\ListingFormBuilderEvent;
use Cocorico\CoreBundle\Event\ListingFormEvents;
use Cocorico\CoreBundle\Form\DataTransformer\ListingListingCategoriesToListingCategoriesTransformer;
use Cocorico\CoreBundle\Form\Type\ListingCategoryType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ListingEditCategoriesAjaxType extends AbstractType
{
    protected $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $listing = $builder->getData();

        $builder
            ->add(
                'listingListingCategories',
                ListingCategoryType::class,
                array(
                    'label' => 'listing_search.form.categories',
                    'placeholder' => 'listing_search.form.categories.empty_value',
                )
            );

        $builder
            ->get('listingListingCategories')
            ->addModelTransformer(new ListingListingCategoriesToListingCategoriesTransformer($listing));


        //Dispatch LISTING_EDIT_CATEGORIES_FORM_BUILD Event. Listener listening this event can add fields and validation
        //Used for example to add fields to categories
        $this->dispatcher->dispatch(
            ListingFormEvents::LISTING_EDIT_CATEGORIES_FORM_BUILD,
            new ListingFormBuilderEvent($builder)
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Entity\Listing',
                'csrf_token_id' => 'listing_edit',
                'translation_domain' => 'cocorico_listing',
                'constraints' => new Valid(),//To have error on collection item field,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_edit_categories_ajax';
    }

}
