<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\PageBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class PageAdmin extends Admin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'page';
    protected $locales;

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        //Translations fields
        $titles = $descriptions = $metaTitles = $metaDescriptions = array();
        foreach ($this->locales as $i => $locale) {
            $titles[$locale] = array(
                'label' => 'Title'
            );
            $descriptions[$locale] = array(
                'label' => 'Description',
                'required' => true,
            );
            $metaTitles[$locale] = array(
                'label' => 'Meta Title'
            );
            $metaDescriptions[$locale] = array(
                'label' => 'Meta Description'
            );
        }

        $formMapper
            ->with('Page')
            ->add(
                'translations',
                'a2lix_translations',
                array(
                    'locales' => $this->locales,
                    'required_locales' => $this->locales,
                    'fields' => array(
                        'title' => array(
                            'field_type' => 'text',
                            'locale_options' => $titles,
                            'required' => true,
                        ),
                        'description' => array(
                            'field_type' => 'ckeditor',
                            'locale_options' => $descriptions,
                            'required' => true,
                            'config' => array(
                                'filebrowser_image_browse_url' => array(
                                    'route' => 'elfinder',
                                    'route_parameters' => array('instance' => 'ckeditor'),
                                ),
                            )
                        ),
                        'metaTitle' => array(
                            'field_type' => 'text',
                            'locale_options' => $metaTitles,
                            'required' => true,
                        ),
                        'metaDescription' => array(
                            'field_type' => 'textarea',
                            'locale_options' => $metaDescriptions,
                            'required' => true,
                        ),
                        'slug' => array(
                            'field_type' => 'text',
                            'disabled' => true,
                        )
                    ),
//                    /** @Ignore */
                    'label' => 'Descriptions'
                )
            )
            ->add(
                'published',
                null,
                array(
                    'label' => 'admin.page.published.label'
                )
            )
            ->add(
                'createdAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.page.created_at.label'
                )
            )
            ->add(
                'updatedAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.page.updated_at.label'
                )
            )
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'translations.title',
                null,
                array('label' => 'admin.page.title.label')
            )
            ->add(
                'translations.description',
                null,
                array('label' => 'admin.page.description.label')
            )
            ->add(
                'published',
                null,
                array('label' => 'admin.page.published.label')
            )
            ->add(
                'createdAt',
                'doctrine_orm_callback',
                array(
                    'label' => 'admin.page.created_at.label',
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        /** @var \DateTime $date */
                        $date = $value['value'];
                        if (!$date) {
                            return false;
                        }

                        $queryBuilder
                            ->andWhere("DATE_FORMAT($alias.createdAt,'%Y-%m-%d') = :createdAt")
                            ->setParameter('createdAt', $date->format('Y-m-d'));

                        return true;
                    },
                    'field_type' => 'sonata_type_date_picker',
                    'field_options' => array('format' => 'dd/MM/yyyy'),
                ),
                null
            );
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'title',
                null,
                array('label' => 'admin.page.title.label')
            )
            ->add(
                'description',
                'html',
                array(
                    'label' => 'admin.page.description.label',
                    'truncate' => array(
                        'length' => 100,
                        'preserve' => true
                    )
                )
            )
            ->add(
                'published',
                null,
                array(
                    'editable' => true,
                    'label' => 'admin.page.published.label'
                )
            )
            ->add(
                'createdAt',
                null,
                array(
                    'label' => 'admin.page.created_at.label',
                    'format' => 'd/m/Y'
                )
            );

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
//                    'create' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            )
        );
    }

    public function getExportFields()
    {
        return array(
            'Id' => 'id',
            'Title' => 'title',
            'Description' => 'description',
            'Published' => 'published',
            'Created At' => 'createdAt'
        );
    }

    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $dataSourceIt = $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
        $dataSourceIt->setDateTimeFormat('d M Y'); //change this to suit your needs
        return $dataSourceIt;
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
