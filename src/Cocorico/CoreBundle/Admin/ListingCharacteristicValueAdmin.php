<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Cocorico\CoreBundle\Entity\ListingCharacteristicValue;
use Cocorico\CoreBundle\Form\Type\EntityHiddenType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Validator\Constraints\NotBlank;

class ListingCharacteristicValueAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'listing-characteristic-value';
    protected $locales;

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'position'
    );

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    /** @inheritdoc */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var ListingCharacteristicValue $subject */
//        $subject = $this->getSubject();

        //Translations fields
        $titles = $descriptions = array();
        foreach ($this->locales as $i => $locale) {
            $titles[$locale] = array(
                'label' => 'Name',
                'constraints' => array(new NotBlank())
            );
        }

        $formMapper
            ->add(
                'translations',
                TranslationsType::class,
                array(
                    'locales' => $this->locales,
                    'required_locales' => $this->locales,
                    'fields' => array(
                        'name' => array(
                            'field_type' => 'text',
                            'locale_options' => $titles,
                        ),
                    ),
                    /** @Ignore */
                    'label' => 'Descriptions'
                )
            )
            ->add(
                'position',
                null,
                array(
                    'label' => 'admin.listing_characteristic.position.label'
                )
            )
            ->add(
                'listingCharacteristicType',
                EntityHiddenType::class,
                array(
                    /** @Ignore */
                    'label' => false,
                    'class' => 'Cocorico\CoreBundle\Entity\ListingCharacteristicType',
                    'data_class' => null
                )
            )
            ->end();
    }


    protected function configureRoutes(RouteCollection $collection)
    {
//        $collection->remove('create');
//        $collection->remove('delete');
    }
}
