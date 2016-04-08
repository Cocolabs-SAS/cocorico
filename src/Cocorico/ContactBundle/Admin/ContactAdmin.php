<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ContactBundle\Admin;

use Cocorico\ContactBundle\Entity\Contact;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ContactAdmin extends Admin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'contact';

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('admin.contact.title')
            ->add(
                'firstName',
                null,
                array(
                    'label' => 'admin.contact.first_name.label'
                )
            )
            ->add(
                'lastName',
                null,
                array(
                    'label' => 'admin.contact.last_name.label',
                )
            )
            ->add(
                'email',
                'email',
                array(
                    'label' => 'admin.contact.email.label',
                )
            )
            ->add(
                'phone',
                null,
                array(
                    'label' => 'admin.contact.phone.label',
                )
            )
            ->add(
                'subject',
                null,
                array(
                    'label' => 'admin.contact.subject.label',
                )
            )
            ->add(
                'message',
                null,
                array(
                    'label' => 'admin.contact.message.label',
                )
            )
            ->add(
                'status',
                'choice',
                array(
                    'choices' => array_flip(Contact::$statusValues),
                    'label' => 'admin.contact.status.label',
                    'translation_domain' => 'cocorico_contact',
                    'choices_as_values' => true
                )
            )
            ->add(
                'createdAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.contact.created_at.label',
                )
            )
            ->add(
                'updatedAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.contact.updated_at.label',
                )
            )
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'status',
                'doctrine_orm_string',
                array(),
                'choice',
                array(
                    'choices' => array_flip(Contact::$statusValues),
                    'label' => 'admin.contact.status.label',
                    'translation_domain' => 'cocorico_contact',
                    'choices_as_values' => true
                )
            )
            ->add(
                'firstName',
                null,
                array('label' => 'admin.contact.first_name.label')
            )
            ->add(
                'lastName',
                null,
                array('label' => 'admin.contact.last_name.label')
            )
            ->add(
                'email',
                null,
                array('label' => 'admin.contact.email.label')
            )
            ->add(
                'phone',
                null,
                array('label' => 'admin.contact.phone.label')
            )
            ->add(
                'subject',
                null,
                array('label' => 'admin.contact.subject.label')
            )
            ->add(
                'createdAt',
                null,
                array('label' => 'admin.contact.created_at.label')
            )
            ->add(
                'updatedAt',
                null,
                array('label' => 'admin.contact.updated_at.label')
            );
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'statusText',
                null,
                array(
                    'label' => 'admin.contact.status.label',
                    'template' => 'CocoricoSonataAdminBundle::list_field_value_translated.html.twig',
                    'data_trans' => 'cocorico_contact'
                )
            )
            ->add(
                'firstName',
                null,
                array('label' => 'admin.contact.first_name.label')
            )
            ->add(
                'lastName',
                null,
                array('label' => 'admin.contact.last_name.label')
            )
            ->add(
                'email',
                null,
                array('label' => 'admin.contact.email.label')
            )
            ->add(
                'phone',
                null,
                array('label' => 'admin.contact.phone.label')
            )
            ->add(
                'subject',
                null,
                array('label' => 'admin.contact.subject.label')
            )
            ->add(
                'createdAt',
                null,
                array(
                    'format' => "d/m/Y H:i",
                    'label' => 'admin.contact.created_at.label',
                )
            )
            ->add(
                'updatedAt',
                null,
                array(
                    'format' => "d/m/Y H:i",
                    'label' => 'admin.contact.updated_at.label',
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

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions["delete"]);

        return $actions;
    }

    public function getExportFields()
    {
        return array(
            'Id' => 'id',
            'Status Text' => 'statusText',
            'First Name' => 'firstName',
            'Last Name' => 'lastName',
            'Email' => 'email',
            'Phone' => 'phone',
            'Subject' => 'subject',
            'Created At' => 'createdAt',
            'Updated At' => 'updatedAt'
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

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
    }
}
