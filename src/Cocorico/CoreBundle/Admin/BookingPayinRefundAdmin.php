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

use Cocorico\CoreBundle\Entity\BookingPayinRefund;
use Cocorico\CoreBundle\Form\Type\PriceType;
use Cocorico\UserBundle\Repository\UserRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BookingPayinRefundAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'booking-payin-refund';
    protected $locales;
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $bundles;
    protected $timezone;

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    public function setTimeUnit($timeUnit)
    {
        $this->timeUnit = $timeUnit;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
    }

    public function setBundlesEnabled($bundles)
    {
        $this->bundles = $bundles;
    }

    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /** @inheritdoc */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var BookingPayinRefund $bookingPayinRefund */
        $bookingPayinRefund = $this->getSubject();

        $askerQuery = null;
        if ($bookingPayinRefund) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->modelManager->getEntityManager('CocoricoUserBundle:User')
                ->getRepository('CocoricoUserBundle:User');

            $askerQuery = $userRepository->getFindOneQueryBuilder($bookingPayinRefund->getUser()->getId());
        }

        $formMapper
            ->with('admin.booking_payin_refund.title')
            ->add(
                'user',
                'sonata_type_model',
                array(
                    'query' => $askerQuery,
                    'disabled' => true,
                    'label' => 'admin.booking.asker.label'
                )
            )
            ->add(
                'booking',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.label',
                )
            )
            ->add(
                'amount',
                PriceType::class,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking_payin_refund.amount.label',
                    'include_vat' => true,
                    'scale' => 2,
                )
            )
            ->add(
                'status',
                ChoiceType::class,
                array(
                    'disabled' => true,
                    'choices' => array_flip(BookingPayinRefund::$statusValues),
                    'placeholder' => 'admin.booking.status.label',
                    'label' => 'admin.booking.status.label',
                    'translation_domain' => 'cocorico_booking',
                )
            )
            ->add(
                'payedAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking_payin_refund.payed_at.label',
                    'view_timezone' => $this->timezone
                )
            )
            ->add(
                'createdAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.created_at.label',
                    'view_timezone' => $this->timezone
                )
            )
            ->add(
                'updatedAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.updated_at.label',
                    'view_timezone' => $this->timezone
                )
            )
            ->end();

        if (array_key_exists("CocoricoMangoPayBundle", $this->bundles)) {
            $formMapper
                ->with('Mangopay')
                ->add(
                    'mangopayRefundId',
                    null,
                    array(
                        'disabled' => true,
                    )
                )
                ->add(
                    'user.mangopayId',
                    null,
                    array(
                        'disabled' => true,
                    )
                )
                ->add(
                    'amountDecimal',
                    'number',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking_payin_refund.amount.label',
                        'scale' => 2,
                    )
                )
                ->add(
                    'booking.mangopayPayinPreAuthId',
                    null,
                    array(
                        'label' => 'admin.booking.mangopay_payin_pre_auth_id.label',
                        'disabled' => true,
                    )
                )
                ->add(
                    'user.mangopayWalletId',
                    null,
                    array(
                        'disabled' => true,
                    )
                )
                ->end();
        }

    }

    /** @inheritdoc */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add(
                'status',
                'doctrine_orm_string',
                array(),
                ChoiceType::class,
                array(
                    'choices' => array_flip(BookingPayinRefund::$statusValues),
                    'label' => 'admin.booking.status.label',
                    'translation_domain' => 'cocorico_booking',
                )
            )
            ->add(
                'booking.id',
                null,
                array('label' => 'admin.booking_bank_wire.booking_id.label')
            )
            ->add(
                'user.email',
                null,
                array('label' => 'admin.booking.asker.label')
            )
            ->add(
                'createdAt',
                'doctrine_orm_callback',
                array(
                    'label' => 'admin.booking.created_at.label',
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
                'booking',
                null,
                array(
                    'label' => 'admin.booking_bank_wire.booking.label'
                )
            )
            ->add(
                'statusText',
                null,
                array(
                    'label' => 'admin.booking.status.label',
                    'template' => 'CocoricoSonataAdminBundle::list_field_value_translated.html.twig',
                    'data_trans' => 'cocorico_booking'
                )
            )
            ->add(
                'user',
                null,
                array(
                    'label' => 'admin.booking.asker.label',
                )
            )
            ->add(
                'booking.listing',
                null,
                array(
                    'label' => 'admin.listing.label'
                )
            )
            ->add(
                'booking.start',
                'date',
                array(
                    'label' => 'admin.booking.start.label',
                )
            )
            ->add(
                'booking.end',
                'date',
                array(
                    'label' => 'admin.booking.end.label',
                )
            )
            ->add(
                'createdAt',
                null,
                array(
                    'label' => 'admin.booking.created_at.label',
                )
            )
            ->add(
                'booking.amountToPayByAskerDecimal',
                null,
                array(
                    'label' => 'admin.booking.amount.label'
                )
            )
            ->add(
                'amountDecimal',
                null,
                array(
                    'label' => 'admin.booking_payin_refund.amount.label'
                )
            )
            ->add(
                'payedAt',
                null,
                array(
                    'label' => 'admin.booking_payin_refund.payed_at.label',
                )
            );

        if (array_key_exists("CocoricoMangoPayBundle", $this->bundles)) {
            $listMapper
                ->add(
                    'user.mangopayId',
                    null
                )
                ->add(
                    'user.mangopayBankAccountId',
                    null
                );

            $listMapper->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'edit' => array()
                    )
                )
            );
        }


    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions["delete"]);

        return $actions;
    }

    public function getExportFields()
    {
        $fields = array(
            'Id' => 'id',
            'Booking' => 'booking',
            'Status' => 'statusText',
            'User' => 'user',
            'Booking Listing' => 'booking.listing',
            'Booking Start' => 'booking.start',
            'Booking End' => 'booking.end',
            'Created At' => 'createdAt',
            'Booking Amount Pay By Asker' => 'booking.amountToPayByAskerDecimal',
            'Amount' => 'amountDecimal',
            'Payed At' => 'payedAt'
        );

        if (array_key_exists("CocoricoMangoPayBundle", $this->bundles)) {
            $mangopayFields = array(
                'User Mangopay Id' => 'user.mangopayId',
                'User Mangopay Wallet Id' => 'user.mangopayWalletId',
                'User Mangopay Bank Account Id' => 'user.mangopayBankAccountId',
            );

            $fields = array_merge($fields, $mangopayFields);
        }

        return $fields;
    }

    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $dataSourceIt = $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
        $dataSourceIt->setDateTimeFormat('d M Y');

        return $dataSourceIt;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
    }
}
