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
use Cocorico\CoreBundle\Entity\ListingCategory;
use Doctrine\ORM\QueryBuilder;
use Exporter\Source\DoctrineORMQuerySourceIterator;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Validator\Constraints\NotBlank;

class ListingCategoryAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'listing-category';
    protected $locales;
    protected $bundles;

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    public function setBundlesEnabled($bundles)
    {
        $this->bundles = $bundles;
    }

    /** @inheritdoc */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var ListingCategory $subject */
//        $subject = $this->getSubject();

        //Translations fields
        $titles = $descriptions = array();
        foreach ($this->locales as $i => $locale) {
            $titles[$locale] = array(
                'label' => 'Name',
                'constraints' => array(new NotBlank())
            );
            $descriptions[$locale] = array(
                'label' => 'Description',
                'constraints' => array(new NotBlank())
            );
        }

        $formMapper
            ->with('admin.listing_category.title')
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
                        'slug' => array(
                            'display' => false
                        )
                    ),
                    /** @Ignore */
                    'label' => 'Descriptions'
                )
            )
            ->add(
                'parent',
                null,
                array(
                    'label' => 'admin.listing_category.parent.label'
                )
            );

        if (array_key_exists("CocoricoListingCategoryFieldBundle", $this->bundles)) {
            $formMapper
                ->add(
                    'fields',
                    null,
                    array(
                        'label' => 'admin.listing_category.fields.label',
                        'disabled' => true,
                        'choice_label' => 'field'
                    )
                );
        }

        $formMapper
            ->end();
    }

    /** @inheritdoc */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'translations.name',
                null,
                array('label' => 'admin.listing_category.name.label')
            )
            ->add(
                'parent',
                null,
                array('label' => 'admin.listing_category.parent.label')
            );
    }

    /** @inheritdoc */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'name',
                null,
                array(
                    'label' => 'admin.listing_category.name.label',
                )
            )
            ->addIdentifier(
                'parent',
                null,
                array('label' => 'admin.listing_category.parent.label')
            );

        if (array_key_exists("CocoricoListingCategoryFieldBundle", $this->bundles)) {
            $listMapper
                ->add(
                    'fields',
                    null,
                    array(
                        'label' => 'admin.listing_category.fields.label',
                        'associated_property' => 'field'
                    )
                );
        }

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
            'name' => 'name',
            'parent' => 'parent',
        );
    }

    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $query */
        $query = parent::createQuery($context);

        if ($context === 'list') {
            $query->orderBy('o.root', 'ASC');
            $query->addOrderBy('o.lft', 'ASC');
        }

        return $query;
    }

    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        /** @var DoctrineORMQuerySourceIterator $dataSourceIt */
        $dataSourceIt = $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
        $dataSourceIt->setDateTimeFormat('d M Y');

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
        //$collection->remove('create');
        //$collection->remove('delete');
    }
}
