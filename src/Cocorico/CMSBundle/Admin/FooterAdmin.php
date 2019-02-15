<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CMSBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Cocorico\CMSBundle\Entity\Footer;
use Cocorico\CMSBundle\Model\Manager\FooterManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Validator\Constraints\NotBlank;

class FooterAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'footer';
    protected $locales;
    /** @var  FooterManager */
    protected $footerManager;

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    public function setFooterManager(FooterManager $footerManager)
    {
        $this->footerManager = $footerManager;
    }

    /** @inheritdoc */
    protected function configureFormFields(FormMapper $formMapper)
    {
        //Translations fields
        $titles = $links = $urls = $urlsHash = array();
        foreach ($this->locales as $i => $locale) {
            $titles[$locale] = array(
                'label' => 'Title',
                'constraints' => array(new NotBlank())
            );
            $links[$locale] = array(
                'label' => 'Link',
                'constraints' => array(new NotBlank())
            );
            $urls[$locale] = array(
                'label' => 'URL',
            );
            $urlsHash[$locale] = array(
                'label' => 'URL Hash',
            );
        }

        $formMapper
            ->with('admin.footer.title')
            ->add(
                'translations',
                TranslationsType::class,
                array(
                    'locales' => $this->locales,
                    'required_locales' => $this->locales,
                    'fields' => array(
                        'url' => array(
                            'field_type' => 'url',
                            'locale_options' => $urls,
                            'required' => false,
                        ),
                        'urlHash' => array(
                            'field_type' => 'text',
                            'locale_options' => $urlsHash,
                            'required' => false,
                            'disabled' => true
                        ),
                        'title' => array(
                            'field_type' => 'text',
                            'locale_options' => $titles,
                            'required' => true,
                        ),
                        'link' => array(
                            'field_type' => 'url',
                            'locale_options' => $links,
                            'required' => true,

                        ),
                    ),
                    /** @Ignore */
                    'label' => 'Descriptions',
                    'help' => 'admin.footer.help'
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

    /** @inheritdoc */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'translations.title',
                null,
                array('label' => 'admin.page.title.label')
            )
            ->add(
                'translations.link',
                null,
                array('label' => 'admin.page.link.label')
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

    /** @inheritdoc */
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
                'link',
                'url',
                array(
                    'label' => 'admin.page.link.label',
                    'truncate' => array(
                        'length' => 100,
                        'preserve' => true
                    )
                )
            )
            ->add(
                'url',
                'html',
                array(
                    'label' => 'admin.footer.url.label',
                    'truncate' => array(
                        'length' => 50
                    )
                )
            )
            ->add(
                'published',
                null,
                array(
                    'editable' => true,
                    'label' => 'admin.page.published.label',
                )
            )
            ->add(
                'createdAt',
                null,
                array(
                    'label' => 'admin.page.created_at.label',
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
            'Links' => 'link',
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

    /**
     * @param mixed|Footer $footer
     * @return mixed
     */
    public function postPersist($footer)
    {
        return $this->footerManager->save($footer);
    }

    /**
     * @param mixed|Footer $footer
     * @return mixed
     */
    public function postUpdate($footer)
    {
        return $this->footerManager->save($footer);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
//        $collection->remove('create');
//        $collection->remove('delete');
    }


}
