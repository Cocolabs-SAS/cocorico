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

use Cocorico\CoreBundle\Event\DirectoryFormBuilderEvent;
use Cocorico\CoreBundle\Event\DirectoryFormEvents;
use Cocorico\CoreBundle\Form\DataTransformer\DirectoryListingCategoriesToListingCategoriesTransformer;
use Cocorico\CoreBundle\Form\Type\ListingCategoryType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class DirectoryEditCategoriesAjaxType extends AbstractType
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

        $directory = $builder->getData();

        $builder
            ->add(
                'directoryListingCategories',
                ListingCategoryType::class,
                array(
                    'label' => 'listing_search.form.categories',
                    'placeholder' => 'listing_search.form.categories.empty_value',
                )
            );

        $builder
            ->get('directoryListingCategories')
            ->addModelTransformer(new DirectoryListingCategoriesToListingCategoriesTransformer($directory));


        //Dispatch DIRECTORY_EDIT_CATEGORIES_FORM_BUILD Event. Listener listening this event can add fields and validation
        //Used for example to add fields to categories
        $this->dispatcher->dispatch(
            DirectoryFormEvents::DIRECTORY_EDIT_CATEGORIES_FORM_BUILD,
            new DirectoryFormBuilderEvent($builder)
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
                'data_class' => 'Cocorico\CoreBundle\Entity\Directory',
                'csrf_token_id' => 'directory_edit',
                'translation_domain' => 'cocorico_direcotry',
                'constraints' => new Valid(),//To have error on collection item field,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'directory_edit_categories_ajax';
    }

}
