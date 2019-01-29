<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Admin;

use Cocorico\MessageBundle\Entity\Thread;
use Cocorico\ReportBundle\Repository\ListingRepository;
use Doctrine\ORM\Query\Expr;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class ThreadAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'thread';
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

    /** @inheritdoc */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'bookingText',
                null,
                array(
                    'label' => 'admin.thread.type.label',
                    'template' => 'CocoricoMessageBundle:Admin:thread_type.html.twig'
                )
            )
            ->add(
                'booking',
                null,
                array(
                    'label' => 'admin.thread.booking.label',
                    'associated_tostring' => 'getId'
                )
            )
            ->add(
                'listing',
                null,
                array(
                    'label' => 'admin.thread.listing.label',
                )
            )
            ->add(
                'createdBy',
                null,
                array(
                    'label' => 'admin.thread.from.label',
                    'associated_tostring' => 'getName'
                )
            )
            ->add(
                'listing.user',
                null,
                array(
                    'label' => 'admin.thread.to.label',
                    'associated_tostring' => 'getName'
                )
            )
            ->add(
                'createdAt',
                null,
                array(
                    'label' => 'admin.thread.createdAt.label'
                )
            )
            ->add(
                'viewThread',
                null,
                array(
                    'label' => 'admin.thread.view.label',
                    'template' => 'CocoricoMessageBundle:Admin:view_thread.html.twig'
                )
            );


        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'edit' => array(),
                )
            )
        );
    }

    /** @inheritdoc */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Thread $thread */
        $thread = $this->getSubject();

        $listing = null;
        if ($thread) {
            if ($thread->getListing()) {
                $listing = $thread->getListing();
            } elseif ($thread->getBooking()) {
                $listing = $thread->getBooking()->getListing();
            }

            if ($listing) {
                /** @var ListingRepository $listingRepository */
                $listingRepository = $this->modelManager->getEntityManager('CocoricoCoreBundle:Listing')
                    ->getRepository('CocoricoCoreBundle:Listing');

                $listingQuery = $listingRepository->getFindOneByIdAndLocaleQuery(
                    $listing->getId(),
                    $this->request ? $this->getRequest()->getLocale() : 'fr'
                );

                $formMapper
                    ->add(
                        'listing',
                        'sonata_type_model',
                        array(
                            'query' => $listingQuery,
                            'disabled' => true,
                            'label' => 'admin.review.listing.label',
                        )
                    );
            }
        }


        $formMapper
            ->add(
                'messages',
                'sonata_type_collection',
                array(
                    // IMPORTANT!: Disable this field otherwise if child form has all its fields disabled
                    // then the child entities will be removed while saving
                    'disabled' => true,
                    'type_options' => array(
                        // Prevents the "Delete" option from being displayed
                        'delete' => false,
                    )
                ),
                array(
                    'edit' => 'inline',
                    'delete' => 'false',
                    'inline' => 'table',
                    'sortable' => 'position'
                )
            )
            ->end();
    }

    public function getFormTheme()
    {
        return array_merge(
            parent::getFormTheme(),
            array('CocoricoMessageBundle:Admin:message_body.html.twig')
        );
    }

    /** @inheritdoc */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'bookingType',
                'doctrine_orm_callback',
                array(
                    'callback' => array($this, 'getMessageTypeFilter'),
                    'field_type' => 'choice',
                    'label' => 'admin.thread.type.label'
                ),
                ChoiceType::class,
                array(
                    'choices' => array(
                        'Reservation Message' => 'booking',
                        'Message' => 'message'
                    ),
                    'placeholder' => 'admin.thread.type.label',
                    'translation_domain' => 'SonataAdminBundle',
                )
            )
            ->add(
                'fromName',
                'doctrine_orm_callback',
                array(
                    'callback' => array($this, 'getFromNameFilter'),
                    'field_type' => 'text',
                    'operator_type' => 'hidden',
                    'operator_options' => array(),
                    'label' => 'admin.thread.from.label'
                )
            )
            ->add(
                'toName',
                'doctrine_orm_callback',
                array(
                    'callback' => array($this, 'getToNameFilter'),
                    'field_type' => 'text',
                    'operator_type' => 'hidden',
                    'operator_options' => array(),
                    'label' => 'admin.thread.to.label'
                )
            )
            ->add(
                'createdAt',
                'doctrine_orm_callback',
                array(
                    'label' => 'admin.thread.created_at.label',
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
                'messages.body',
                null,
                array('label' => 'admin.thread.search.label')
            );
    }

    public function getMessageTypeFilter($queryBuilder, $alias, $field, $value)
    {
        if (!$value['value']) {
            return false;
        }
        if ($value['value'] == 'message') {
            $queryBuilder->andWhere($alias . '.booking IS NULL');
        }
        if ($value['value'] == 'booking') {
            $queryBuilder->andWhere($alias . '.booking IS NOT NULL');
        }

        return true;
    }

    public function getFromNameFilter($queryBuilder, $alias, $field, $value)
    {
        if (!$value['value']) {
            return false;
        }

        $exp = new Expr();
        $queryBuilder
            ->join('o.createdBy', 'bu')
            ->andWhere(
                $exp->orX(
                    $exp->like('bu.firstName', $exp->literal('%' . $value['value'] . '%')),
                    $exp->like('bu.lastName', $exp->literal('%' . $value['value'] . '%')),
                    $exp->like(
                        $exp->concat(
                            'bu.firstName',
                            $exp->concat($exp->literal(' '), 'bu.lastName')
                        ),
                        $exp->literal('%' . $value['value'] . '%')
                    )
                )
            );

        return true;
    }

    public function getToNameFilter($queryBuilder, $alias, $field, $value)
    {
        if (!$value['value']) {
            return false;
        }

        $exp = new Expr();
        $queryBuilder
            ->join('o.listing', 'l')
            ->join('l.user', 'lu')
            ->andWhere(
                $exp->orX(
                    $exp->like('lu.firstName', $exp->literal('%' . $value['value'] . '%')),
                    $exp->like('lu.lastName', $exp->literal('%' . $value['value'] . '%')),
                    $exp->like(
                        $exp->concat(
                            'lu.firstName',
                            $exp->concat($exp->literal(' '), 'lu.lastName')
                        ),
                        $exp->literal('%' . $value['value'] . '%')
                    )
                )
            );

        return true;
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
            //'Type of message' => 'bookingText',
            'Id Reservation' => 'booking.id',
            'Listing title' => 'listing.title',
            'From' => 'createdBy.name',
            'To' => 'listing.user.name',
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

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');

    }
}
