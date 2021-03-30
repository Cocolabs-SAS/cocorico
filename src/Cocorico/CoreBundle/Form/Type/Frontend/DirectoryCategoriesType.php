<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Frontend;

use Cocorico\CoreBundle\Event\DirectoryFormBuilderEvent;
use Cocorico\CoreBundle\Event\DirectoryFormEvents;
use Cocorico\CoreBundle\Form\DataTransformer\DirectoryListingCategoriesToListingCategoriesTransformer;
use Cocorico\CoreBundle\Form\Type\ListingCategoryType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DirectoryCategoriesType extends AbstractType
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
                ListingCategoryType::class
            );

        $builder
            ->get('directoryListingCategories')
            ->addModelTransformer(new DirectoryListingCategoriesToListingCategoriesTransformer($directory));


        $this->dispatcher->dispatch(
            DirectoryFormEvents::DIRECTORY_NEW_CATEGORIES_FORM_BUILD,
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
                'csrf_token_id' => 'directory_new_categories',
                'translation_domain' => 'cocorico_directory',
//                'cascade_validation' => false,//To have error on collection item field,
                'validation_groups' => false,//To not have directory validation errors when categories are only edited
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'directory_new_categories';
    }

}
