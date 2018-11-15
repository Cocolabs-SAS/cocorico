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

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\BookingBankWire;
use Cocorico\CoreBundle\Model\Manager\BookingBankWireManager;
use Cocorico\UserBundle\Repository\UserRepository;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class BookingBankWireAdmin extends Admin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'booking-bank-wire';
    protected $locales;
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $currency;
    /** @var  BookingBankWireManager $bookingBankWireManager */
    protected $bookingBankWireManager;
    protected $bundles;

    // setup the default sort column and order
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

    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    public function setBookingBankWireManager(BookingBankWireManager $bookingBankWireManager)
    {
        $this->bookingBankWireManager = $bookingBankWireManager;
    }

    public function setBundlesEnabled($bundles)
    {
        $this->bundles = $bundles;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var BookingBankWire $bookingBankWire */
        $bookingBankWire = $this->getSubject();

        $offererQuery = null;
        if ($bookingBankWire) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->modelManager->getEntityManager('CocoricoUserBundle:User')
                ->getRepository('CocoricoUserBundle:User');

            $offererQuery = $userRepository->getFindOneQueryBuilder($bookingBankWire->getUser()->getId());
        }


        $formMapper
            ->with('admin.booking_bank_wire.title')
            ->add(
                'user',
                'sonata_type_model',
                array(
                    'query' => $offererQuery,
                    'disabled' => true,
                    'label' => 'admin.booking.offerer.label'
                )
            )
            ->add(
                'booking',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.label',
                )
            )->add(
                'booking.status',
                'choice',
                array(
                    'disabled' => true,
                    'choices' => array_flip(Booking::$statusValues),
                    'empty_value' => 'admin.booking.status.label',
                    'label' => 'admin.booking_bank_wire.booking.status.label',
                    'translation_domain' => 'cocorico_booking',
                    'choices_as_values' => true
                )
            );

        if (array_key_exists("CocoricoVoucherBundle", $this->bundles)) {
            if ($bookingBankWire) {
                $bankWireAmounts = $this->bookingBankWireManager->getAmountAndRemainingAmountToWire($bookingBankWire);
                $amountToWire = $bankWireAmounts["amountToWire"];
                $remainingAmount = $bankWireAmounts["remainingAmount"];
            }

            $formMapper
                ->add(
                    'amount',
                    'price',
                    array(
                        'scale' => 2,
                        'disabled' => true,
                        'label' => 'admin.booking_bank_wire.amount_excl_discount_voucher.label',
                        'include_vat' => true
                    )
                )
                ->add(
                    'booking.amountDiscountVoucher',
                    'price',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.amount_discount_voucher.label',
                        'include_vat' => true
                    )
                )
                ->add(
                    'amountToWire',
                    'price',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.amount_to_wire.label',
                        'mapped' => false,
                        'data' => isset($amountToWire) ? $amountToWire : null,
                        'include_vat' => true
                    )
                )
                ->add(
                    'remainingAmount',
                    'price',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.remaining_amount_to_pay_to_offerer.label',
                        'mapped' => false,
                        'data' => isset($remainingAmount) ? $remainingAmount : null,
                        'include_vat' => true
                    )
                )
                ->add(
                    'booking.codeVoucher',
                    null,
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.code_voucher.label',
                    )
                )
                ->add(
                    'booking.discountVoucher',
                    'integer',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.discount_voucher.label',
                        'help' => '%, €',
                    )
                );
        } else {
            $formMapper
                ->add(
                    'amount',
                    'price',
                    array(
                        'scale' => 2,
                        'disabled' => true,
                        'label' => 'admin.booking_bank_wire.amount.label',
                        'include_vat' => true
                    )
                );
        }

        $formMapper
            ->add(
                'status',
                'choice',
                array(
                    'choices' => array_flip(BookingBankWire::$statusValues),
                    'empty_value' => 'admin.booking.status.label',
                    'label' => 'admin.booking.status.label',
                    'translation_domain' => 'cocorico_booking',
                    'help' => 'admin.booking_bank_wire.status.help',
                    'choices_as_values' => true
                )
            )
            ->add(
                'payedAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking_bank_wire.payed_at.label',
                )
            )
            ->add(
                'createdAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.created_at.label',
                )
            )
            ->add(
                'updatedAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.updated_at.label',
                )
            )
            ->end();

        if (array_key_exists("CocoricoMangoPayBundle", $this->bundles)) {
            $formMapper
                ->with('Mangopay')
                ->add(
                    'mangopayTransferId',
                    null,
                    array(
                        'disabled' => true,
                        'sonata_help' => 'Tag'
                    )
                )
                ->add(
                    'user.mangopayId',
                    null,
                    array(
                        'disabled' => true,
                        'sonata_help' => 'Author ID'
                    )
                );


            if (array_key_exists("CocoricoVoucherBundle", $this->bundles)) {
                $formMapper
                    ->add(
                        'amountToWireDecimal',
                        'text',
                        array(
                            'disabled' => true,
                            'label' => 'admin.booking.amount_to_wire.label',
                            'mapped' => false,
                            'data' => isset($amountToWire) ? number_format($amountToWire / 100, 2, ".", "") : null,
                            'help' => 'Debited funds (' . $this->currency . ')'
                        )
                    );
            } else {
                $formMapper
                    ->add(
                        'amountDecimal',
                        'number',
                        array(
                            'scale' => 2,
                            'disabled' => true,
                            'label' => 'admin.booking_bank_wire.amount.label',
                            'help' => 'Debited funds (' . $this->currency . ')'
                        )
                    );
            }

            $formMapper
                ->add(
                    'fees',
                    'number',
                    array(
                        'disabled' => true,
                        'mapped' => false,
                        'data' => 0,
                        'help' => 'Fees'
                    )
                )
                ->add(
                    'user.mangopayWalletId',
                    null,
                    array(
                        'disabled' => true,
                        'sonata_help' => 'Debited Wallet ID'
                    )
                )
                ->add(
                    'wireReference',
                    'text',
                    array(
                        'disabled' => true,
                        'sonata_help' => 'Wire Reference',
                        'mapped' => false,
                        'data' => "CBId:" . (
                            (is_object($bookingBankWire) && $bookingBankWire)
                                ? $bookingBankWire->getBooking()->getId()
                                : null
                            ),
                    )
                )
                ->add(
                    'user.mangopayBankAccountId',
                    null,
                    array(
                        'disabled' => true,
                        'sonata_help' => 'Bank account ID'
                    )
                )
                ->add(
                    'mangopayPayoutId',
                    null,
                    array(
                        'disabled' => true,
                    )
                )
                ->end();
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add(
                'status',
                'doctrine_orm_string',
                array(),
                'choice',
                array(
                    'choices' => array_flip(BookingBankWire::$statusValues),
                    'label' => 'admin.booking.status.label',
                    'translation_domain' => 'cocorico_booking',
                    'choices_as_values' => true
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
                array('label' => 'admin.booking.offerer.label')
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


    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'statusText',
                null,
                array(
                    'label' => 'admin.booking_bank_wire.status.label',
                    'template' => 'CocoricoSonataAdminBundle::list_field_value_translated.html.twig',
                    'data_trans' => 'cocorico_booking'
                )
            )
            ->add(
                'booking',
                null,
                array(
                    'label' => 'admin.booking_bank_wire.booking.label'
                )
            )
            ->add(
                'booking.statusText',
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
                    'label' => 'admin.booking.offerer.label',
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
                    'format' => 'd/m/Y'
                )
            )
            ->add(
                'booking.end',
                'date',
                array(
                    'label' => 'admin.booking.end.label',
                    'format' => 'd/m/Y'
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
                    /** @Ignore */
                    'label' =>
                        (
                        array_key_exists("CocoricoVoucherBundle", $this->bundles) ?
                            'admin.booking_bank_wire.amount_excl_discount_voucher.label' :
                            'admin.booking_bank_wire.amount.label'
                        )
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
        }

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'edit' => array(),
//                    'do_bank_wire' => array(
//                        'template' => 'CocoricoMangoPayBundle::Admin/list_action_do_bank_wire.html.twig'
//                    )
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
        $fields = array(
            'Id' => 'id',
            'Status' => 'statusText',
            'Booking' => 'booking',
            'Booking Status' => 'booking.statusText',
            'User' => 'user',
            'Booking Listing' => 'booking.listing',
            'Booking Start' => 'booking.start',
            'Booking End' => 'booking.end',
            'Booking Amount Pay To Offerer' => 'booking.amountToPayToOffererDecimal',
            'Amount' => 'amountDecimal',
        );

        if (array_key_exists("CocoricoMangoPayBundle", $this->bundles)) {
            $mangopayFields = array(
                'User Mangopay Id' => 'user.mangopayId',
                'User Mangopay Bank Account Id' => 'user.mangopayBankAccountId'
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

        if (array_key_exists("CocoricoMangoPayBundle", $this->bundles)) {
            $collection->add(
                'mangopay_withdraw'//See Cocorico/SonataAdminBundle/Resources/views/CRUD/base_edit_form.html.twig
            );
        }
    }
}
