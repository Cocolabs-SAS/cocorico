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

use Cocorico\CoreBundle\Entity\ListingCharacteristic;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ListingCharacteristicAdmin extends Admin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'listing-characteristic';
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

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var ListingCharacteristic $subject */
//        $subject = $this->getSubject();

        //Translations fields
        $titles = $descriptions = array();
        foreach ($this->locales as $i => $locale) {
            $titles[$locale] = array(
                'label' => 'Name',
                'required' => true
            );
            $descriptions[$locale] = array(
                'label' => 'Description',
                'required' => false
            );
        }

        $formMapper
            ->with('admin.listing_characteristic.title')
            ->add(
                'translations',
                'a2lix_translations',
                array(
                    'locales' => $this->locales,
                    'required_locales' => $this->locales,
                    'fields' => array(
                        'name' => array(
                            'field_type' => 'text',
                            'locale_options' => $titles,
                        ),
                        'description' => array(
                            'field_type' => 'textarea',
                            'locale_options' => $descriptions,
                        )
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
                null,
                array(
                    'label' => 'admin.listing_characteristic.type.label'
                )
            )
            ->add(
                'listingCharacteristicGroup',
                'sonata_type_model_list',
                array(
                    'label' => 'admin.listing_characteristic.group.label'
                )
            )
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'translations.name',
                null,
                array('label' => 'admin.listing_characteristic.name.label')
            )
            ->add(
                'listingCharacteristicType',
                null,
                array('label' => 'admin.listing_characteristic.type.label')
            )
            ->add(
                'listingCharacteristicGroup',
                null,
                array('label' => 'admin.listing_characteristic.group.label')
            );
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'name',
                null,
                array(
                    'label' => 'admin.listing_characteristic.name.label',
                )
            )
            ->addIdentifier(
                'listingCharacteristicType',
                null,
                array('label' => 'admin.listing_characteristic.type.label')
            )
            ->addIdentifier(
                'listingCharacteristicGroup',
                null,
                array('label' => 'admin.listing_characteristic.group.label')
            )
            ->add(
                'position',
                null,
                array('label' => 'admin.listing_characteristic.position.label')
            );


        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    //'show' => array(),
                    'edit' => array(),
                )
            )
        );
    }

    public function getExportFields()
    {
        return array(
            'Id' => 'id',
            'Name' => 'name',
            'Type of Characteristic' => 'listingCharacteristicType',
            'Group' => 'listingCharacteristicGroup',
            'Position' => 'position'
        );
    }

    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $datasourceit = $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
        $datasourceit->setDateTimeFormat('d M Y'); //change this to suit your needs
        return $datasourceit;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions["delete"]);

        return $actions;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        //$collection->remove('create');
        //$collection->remove('delete');
    }
}
