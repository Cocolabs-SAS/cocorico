<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ReviewBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ReviewAdmin extends Admin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'review';

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add(
                'reviewBy',
                null,
                array(
                    'label' => 'admin.review.reviewBy.label'
                )
            )
            ->add(
                'reviewTo',
                null,
                array(
                    'label' => 'admin.review.reviewTo.label',
                )
            )
            ->add(
                'rating',
                null,
                array(
                    'label' => 'admin.review.rating.label',
                )
            )
            ->add(
                'comment',
                null,
                array(
                    'label' => 'admin.review.comment.label',
                )
            )
            ->add(
                'createdAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.review.created_at.label',
                )
            )
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $choices = array(
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5'
        );

        $datagridMapper
            ->add(
                'rating',
                'doctrine_orm_string',
                array(),
                'choice',
                array(
                    'label' => 'admin.review.rating.label',
                    'choices' => $choices,
                    'empty_value' => 'admin.review.rating.label',
                    'translation_domain' => 'SonataAdminBundle',
                )
            )
            ->add(
                'reviewBy.email',
                null,
                array('label' => 'admin.review.reviewBy.label')
            )
            ->add(
                'reviewTo.email',
                null,
                array('label' => 'admin.review.reviewTo.label')
            )
            ->add(
                'booking.listing.translations.title',
                null,
                array('label' => 'admin.review.listing.label')
            )
            ->add(
                'createdAt',
                'doctrine_orm_callback',
                array(
                    'label' => 'admin.review.created_at.label',
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
            )
            ->add(
                'comment',
                null,
                array('label' => 'admin.review.search.label')
            );
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'reviewBy',
                null,
                array(
                    'label' => 'admin.review.reviewBy.label'
                )
            )
            ->add(
                'reviewTo',
                null,
                array(
                    'label' => 'admin.review.reviewTo.label',
                )
            )
            ->add(
                'booking.listing',
                null,
                array(
                    'label' => 'admin.review.listing.label',
                )
            )
            ->add(
                'rating',
                null,
                array(
                    'label' => 'admin.review.rating.label',
                )
            )
            ->add(
                'comment',
                null,
                array(
                    'template' => 'CocoricoReviewBundle:Admin:list_comment.html.twig',
                    'label' => 'admin.review.comment.label',
                )
            )
            ->add(
                'createdAt',
                null,
                array(
                    'format' => "d/m/Y H:i",
                    'label' => 'admin.review.created_at.label',
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
            'Review By' => 'reviewBy',
            'Review To' => 'reviewTo',
            'Booking Listing' => 'booking.listing',
            'Rating' => 'rating',
            'Comment' => 'comment',
            'Created At' => 'createdAt'
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
