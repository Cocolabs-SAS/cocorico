<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ListingDepositBundle\Admin;

use Cocorico\ListingDepositBundle\Entity\BookingDepositRefund;
use Cocorico\UserBundle\Repository\UserRepository;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class BookingDepositRefundAdmin extends Admin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'booking-deposit-refund';
    protected $locales;
    protected $bundles;
    protected $currency;

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    public function setBundlesEnabled($bundles)
    {
        $this->bundles = $bundles;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }


    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var BookingDepositRefund $bookingDepositRefund */
        $bookingDepositRefund = $this->getSubject();

        $askerQuery = $offererQuery = null;
        if ($bookingDepositRefund) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->modelManager->getEntityManager('CocoricoUserBundle:User')
                ->getRepository('CocoricoUserBundle:User');

            $askerQuery = $userRepository->getFindOneQueryBuilder($bookingDepositRefund->getAsker()->getId());

            $offererQuery = $userRepository->getFindOneQueryBuilder($bookingDepositRefund->getOfferer()->getId());
        }

        $formMapper
            ->tab(
                'admin.booking_deposit_refund.title',
                array(
                    'description' => 'admin.deposit_refund_actions_help',
                )
            )
            ->with('')
            ->add(
                'asker',
                'sonata_type_model',
                array(
                    'query' => $askerQuery,
                    'disabled' => true,
                    'label' => 'admin.booking.asker.label'
                )
            )
            ->add(
                'offerer',
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
            )
            ->add(
                'amount',
                'price',
                array(
                    'disabled' => true,
                    'label' => 'admin.booking_deposit_refund.amount.label',
                    'include_vat' => true,
                    'scale' => 2,
                )
            )
            ->add(
                'amountAsker',
                'price',
                array(
                    'disabled' =>
                        ($bookingDepositRefund && $bookingDepositRefund->getAmountAsker() != '') ? true : false,
                    'label' => 'admin.booking_deposit_refund.amount_asker.label',
                    'include_vat' => true,
                    'scale' => 2,
                )
            )
            ->add(
                'amountOfferer',
                'price',
                array(
                    'disabled' =>
                        ($bookingDepositRefund && $bookingDepositRefund->getAmountAsker() != '') ? true : false,
                    'label' => 'admin.booking_deposit_refund.amount_offerer.label',
                    'include_vat' => true,
                    'scale' => 2,
                )
            )
            ->add(
                'statusAsker',
                'choice',
                array(
                    'disabled' => true,
                    'choices' => array_flip(BookingDepositRefund::$statusValues),
                    'empty_value' => 'admin.booking.status.label',
                    'label' => 'admin.booking_deposit_refund.status_asker.label',
                    'translation_domain' => 'cocorico_listing_deposit',
                    'choices_as_values' => true
                )
            )
            ->add(
                'statusOfferer',
                'choice',
                array(
                    'disabled' => true,
                    'choices' => array_flip(BookingDepositRefund::$statusValues),
                    'empty_value' => 'admin.booking.status.label',
                    'label' => 'admin.booking_deposit_refund.status_offerer.label',
                    'translation_domain' => 'cocorico_listing_deposit',
                    'choices_as_values' => true
                )
            )
            ->add(
                'offererPayedAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking_deposit_refund.offerer_payed_at.label',
                )
            )
            ->add(
                'askerPayedAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking_deposit_refund.asker_payed_at.label',
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
            ->end()
            ->end();

        if (array_key_exists("CocoricoMangoPayBundle", $this->bundles) && is_object($bookingDepositRefund)
            && $bookingDepositRefund
        ) {
            if ($bookingDepositRefund->getAmountAsker() > 0) {//Payin refund
                $formMapper
                    ->tab(
                        'admin.mangopay_deposit_refund_asker',
                        array(
                            'description' => 'admin.deposit_refund_mangopay_asker_help',
                        )
                    )
                    ->with('')
                    ->add(
                        'tagAsker',
                        'text',
                        array(
                            'disabled' => true,
                            'sonata_help' => 'Tag',
                            'mapped' => false,
                            'data' => $bookingDepositRefund->getId(),
                            'label' => 'admin.booking_deposit_refund.asker_tag.label',
                        )
                    )
                    ->add(
                        'booking.mangopayPayinPreAuthId',
                        null,
                        array(
                            'disabled' => true,
                            'sonata_help' => 'PayIn ID',
                            'label' => 'admin.booking.mangopay_payin_pre_auth_id.label',
                        )
                    )
                    ->add(
                        'asker.mangopayId',
                        null,
                        array(
                            'disabled' => true,
                            'sonata_help' => 'Author ID',
                            'label' => 'admin.booking_deposit_refund.asker_mangopay_id.label',
                        )
                    )
                    ->add(
                        'amountAskerDecimal',
                        'number',
                        array(
                            'disabled' => true,
                            'help' => 'Debited funds (' . $this->currency . ')',
                            'label' => 'admin.booking_deposit_refund.amount_asker.label',
                            'scale' => 2,
                        )
                    )
                    ->add(
                        'feesAsker',
                        'text',
                        array(
                            'disabled' => true,
                            'mapped' => false,
                            'label' => 'admin.booking_deposit_refund.fees_asker.label',
                            'sonata_help' => 'Fees',
                            'data' => 0,
                        )
                    )->add(
                        'mangopayRefundId',
                        null,
                        array(
                            'disabled' => true,
                        )
                    )
                    ->end()
                    ->end();
            }

            if ($bookingDepositRefund->getAmountOfferer() > 0) {//Payout
                $formMapper
                    ->tab(
                        'admin.mangopay_deposit_refund_offerer',
                        array(
                            'description' => 'admin.deposit_refund_mangopay_offerer_help',
                        )
                    )
                    ->with('')
                    ->add(
                        'tagOfferer',
                        'text',
                        array(
                            'disabled' => true,
                            'sonata_help' => 'Tag',
                            'mapped' => false,
                            'data' => $bookingDepositRefund->getId(),
                            'label' => 'admin.booking_deposit_refund.offerer_tag.label',
                        )
                    )
                    ->add(
                        'offerer.mangopayId',
                        null,
                        array(
                            'disabled' => true,
                            'sonata_help' => 'Author ID',
                            'label' => 'admin.booking_deposit_refund.offerer_mangopay_id.label',
                        )
                    )
                    ->add(
                        'amountOffererDecimal',
                        'number',
                        array(
                            'disabled' => true,
                            'label' => 'admin.booking_deposit_refund.amount_offerer.label',
                            'help' => 'Debited funds (' . $this->currency . ')',
                            'scale' => 2,
                        )
                    )
                    ->add(
                        'feesOfferer',
                        'number',
                        array(
                            'disabled' => true,
                            'mapped' => false,
                            'label' => 'admin.booking_deposit_refund.fees_offerer.label',
                            'data' => 0,
                            'help' => 'Fees'
                        )
                    )
                    ->add(
                        'asker.mangopayWalletId',
                        null,
                        array(
                            'disabled' => true,
                            'sonata_help' => 'Debited Wallet ID',
                            'label' => 'admin.booking_deposit_refund.wallet_id_asker.label',
                        )
                    )
                    ->add(
                        'wireReference',
                        'text',
                        array(
                            'disabled' => true,
                            'sonata_help' => 'Wire Reference',
                            'mapped' => false,
                            'data' => "Deposit offerer refunding for booking id :" .
                                $bookingDepositRefund->getBooking()->getId(),
                        )
                    )
                    ->add(
                        'offerer.mangopayBankAccountId',
                        null,
                        array(
                            'disabled' => true,
                            'label' => 'admin.booking_deposit_refund.bank_account_id_offerer.label',
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
                    ->end()
                    ->end();
            }

        }

//                    $formMapper
//                        ->end()
//                ->with('tab1', array('tab' => true))
//                ->with('group1')
//                ->end()
//                ->with('group2', array('collapsed' => true))
//                ->end()
//                ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add(
                'statusAsker',
                'doctrine_orm_string',
                array(),
                'choice',
                array(
                    'choices' => array_flip(BookingDepositRefund::$statusValues),
                    'label' => 'admin.booking_deposit_refund.status_asker.label',
                    'translation_domain' => 'cocorico_listing_deposit',
                    'choices_as_values' => true
                )
            )
            ->add(
                'statusOfferer',
                'doctrine_orm_string',
                array(),
                'choice',
                array(
                    'choices' => array_flip(BookingDepositRefund::$statusValues),
                    'label' => 'admin.booking_deposit_refund.status_offerer.label',
                    'translation_domain' => 'cocorico_listing_deposit',
                    'choices_as_values' => true
                )
            )
            ->add(
                'booking.id',
                null,
                array('label' => 'admin.booking_bank_wire.booking_id.label')
            )
            ->add(
                'asker.email',
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
                'statusAskerText',
                null,
                array(
                    'label' => 'admin.booking_deposit_refund.status_asker.label',
                    'template' => 'CocoricoSonataAdminBundle::list_field_value_translated.html.twig',
                    'data_trans' => 'cocorico_listing_deposit'
                )
            )
            ->add(
                'statusOffererText',
                null,
                array(
                    'label' => 'admin.booking_deposit_refund.status_offerer.label',
                    'template' => 'CocoricoSonataAdminBundle::list_field_value_translated.html.twig',
                    'data_trans' => 'cocorico_listing_deposit'
                )
            )
            ->add(
                'asker',
                null,
                array(
                    'label' => 'admin.booking.asker.label',
                )
            )
            ->add(
                'offerer',
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
                'createdAt',
                null,
                array(
                    'label' => 'admin.booking.created_at.label',
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
                    'label' => 'admin.booking_deposit_refund.amount.label'
                )
            );
//            ->add(
//                'offererPayedAt',
//                null,
//                array(
//                    'label' => 'admin.booking_deposit_refund.offerer_payed_at.label',
//                    'format' => 'd/m/Y'
//                )
//            )
//            ->add(
//                'askerPayedAt',
//                null,
//                array(
//                    'label' => 'admin.booking_deposit_refund.asker_payed_at.label',
//                    'format' => 'd/m/Y'
//                )
//            );

        if (array_key_exists("CocoricoMangoPayBundle", $this->bundles)) {
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
            'Status Asker' => 'statusAskerText',
            'Status Offerer' => 'statusOffererText',
            'Asker' => 'asker',
            'Offerer' => 'offerer',
            'Booking Listing' => 'booking.listing',
            'Booking Start' => 'booking.start',
            'Booking End' => 'booking.end',
            'Created At' => 'createdAt',
            'Booking Amount Pay By Asker' => 'booking.amountToPayByAskerDecimal',
            'Deposit Amount' => 'amountDecimal',
            'Deposit Amount for asker' => 'amountAskerDecimal',
            'Asker Deposit payed At' => 'askerPayedAt',
            'Deposit Amount for offerer' => 'amountOffererDecimal',
            'Offerer Deposit payed At' => 'offererPayedAt',
        );

        if (array_key_exists("CocoricoMangoPayBundle", $this->bundles)) {
            $mangopayFields = array(
                'Asker Mangopay Id' => 'asker.mangopayId',
                'Asker Mangopay Wallet Id' => 'asker.mangopayWalletId',
                'Asker Mangopay Payin PreAuth Id' => 'asker.mangopayPayinPreAuthId',
                'Offerer Mangopay Id' => 'offerer.mangopayId',
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
                'mangopay_deposit_refund'//See Cocorico/SonataAdminBundle/Resources/views/CRUD/base_edit_form.html.twig
            );
        }
    }
}
