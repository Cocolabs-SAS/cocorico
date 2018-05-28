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

use Cocorico\CoreBundle\Entity\ListingCharacteristicType;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ListingCharacteristicTypeAdmin extends Admin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'listing-characteristic-type';
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
        /** @var ListingCharacteristicType $subject */
//        $subject = $this->getSubject();

        $formMapper
            ->with('admin.listing_characteristic.type.label')
            ->add(
                'name',
                null,
                array(
                    'label' => 'admin.listing_characteristic.name.label'
                )
            )
            ->add(
                'listingCharacteristicValues',
                'sonata_type_collection',
                array(
                    'by_reference' => false,
                    'label' => 'admin.listing_characteristic_type.values.label'
                ),
                array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'id',
                )
            )
            ->end();
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
//        $collection->remove('create');
//        $collection->remove('delete');
    }
}
