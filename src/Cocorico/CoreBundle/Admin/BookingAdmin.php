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
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Repository\ListingRepository;
use Cocorico\UserBundle\Repository\UserRepository;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class BookingAdmin extends Admin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'booking';
    protected $locales;
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $bookingExpirationDelay;
    protected $bookingAcceptationDelay;
    protected $includeVat;
    protected $bundles;

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

    public function setBookingExpirationDelay($bookingExpirationDelay)
    {
        $this->bookingExpirationDelay = $bookingExpirationDelay;//in minutes
    }

    public function setBookingAcceptationDelay($bookingAcceptationDelay)
    {
        $this->bookingAcceptationDelay = $bookingAcceptationDelay;//in minutes
    }

    public function setBundlesEnabled($bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * @param bool $includeVat
     */
    public function setIncludeVat($includeVat)
    {
        $this->includeVat = $includeVat;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Booking $booking */
        $booking = $this->getSubject();

        $askerQuery = $offererQuery = $listingQuery = null;
        if ($booking) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->modelManager->getEntityManager('CocoricoUserBundle:User')
                ->getRepository('CocoricoUserBundle:User');

            $askerQuery = $userRepository->getFindOneQueryBuilder($booking->getUser()->getId());
            $offererQuery = $userRepository->getFindOneQueryBuilder($booking->getListing()->getUser()->getId());

            /** @var ListingRepository $listingRepository */
            $listingRepository = $this->modelManager->getEntityManager('CocoricoCoreBundle:Listing')
                ->getRepository('CocoricoCoreBundle:Listing');

            $listingQuery = $listingRepository->getFindOneByIdAndLocaleQuery(
                $booking->getListing()->getId(),
                $this->request ? $this->getRequest()->getLocale() : 'fr'
            );
        }

        $formMapper
            ->with('admin.booking.title')
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
                'listing.user',
                'sonata_type_model',
                array(
                    'query' => $offererQuery,
                    'disabled' => true,
                    'label' => 'admin.booking.offerer.label',
                )
            )
            ->add(
                'listing',
                'sonata_type_model',
                array(
                    'query' => $listingQuery,
                    'disabled' => true,
                    'label' => 'admin.listing.label',
                )
            )
            ->add(
                'amountExcludingFees',
                'price',
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.amount_excl_fees.label',
                    'include_vat' => true,
                    'scale' => 2
                )
            )
            ->add(
                'amountFeeAsAsker',
                'price',
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.amount_fee_as_asker.label',
                    'include_vat' => true,
                    'scale' => 2
                )
            )
            ->add(
                'amountFeeAsOfferer',
                'price',
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.amount_fee_as_offerer.label',
                    'include_vat' => true,
                    'scale' => 2
                )
            );

        if (array_key_exists("CocoricoVoucherBundle", $this->bundles)) {
            $formMapper
                ->add(
                    'codeVoucher',
                    null,
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.code_voucher.label',
                    )
                )
                ->add(
                    'discountVoucher',
                    'integer',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.discount_voucher.label',
                        'help' => '%, €',
                    )
                )->add(
                    'amountDiscountVoucher',
                    'price',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.amount_discount_voucher.label',
                        'include_vat' => true,
                        'scale' => 2
                    )
                );
        }


        $formMapper
            ->add(
                'amountToPayByAsker',
                'price',
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.amount_to_pay_by_asker.label',
                    'include_vat' => true,
                    'scale' => 2
                )
            )
            ->add(
                'amountToPayToOfferer',
                'price',
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.amount_to_pay_to_offerer.label',
                    'include_vat' => true,
                    'scale' => 2
                )
            )
            ->add(
                'status',
                'choice',
                array(
                    'choices' => array_flip(Booking::$statusValues),
                    'empty_value' => 'admin.booking.status.label',
                    'disabled' => true,
                    'label' => 'admin.booking.status.label',
                    'translation_domain' => 'cocorico_booking',
                    'choices_as_values' => true
                )
            )
            ->add(
                'listing.cancellationPolicy',
                'choice',
                array(
                    'choices' => array_flip(Listing::$cancellationPolicyValues),
                    'empty_value' => 'admin.listing.cancellation_policy.label',
                    'disabled' => true,
                    'label' => 'admin.listing.cancellation_policy.label',
                    'translation_domain' => 'cocorico_listing',
                    'choices_as_values' => true
                )
            )
            ->add(
                'validated',
                null,
                array(
                    'label' => 'admin.booking.validated.label',
                    'disabled' => true,
                )
            )
            ->add(
                'alertedExpiring',
                null,
                array(
                    'label' => 'admin.booking.alerted_expiring.label',
                    'required' => false,
                    'disabled' => true,
                )
            )
            ->add(
                'alertedImminent',
                null,
                array(
                    'label' => 'admin.booking.alerted_imminent.label',
                    'required' => false,
                    'disabled' => true,
                )
            )
            ->add(
                'start',
                'date',
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.start.label',
                )
            )
            ->add(
                'end',
                'date',
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.end.label',
                )
            );

        if (!$this->timeUnitIsDay) {
            $formMapper
                ->add(
                    'startTime',
                    'time',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.start_time.label',
                    )
                )
                ->add(
                    'endTime',
                    'time',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.end_time.label',
                    )
                );
        }

        $formMapper
            ->add(
                'newBookingAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.new_booking_at.label',
                )
            )
            ->add(
                'payedBookingAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.payed_booking_at.label',
                )
            )
            ->add(
                'refusedBookingAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.refused_booking_at.label',
                )
            )
            ->add(
                'canceledAskerBookingAt',
                null,
                array(
                    'disabled' => true,
                    'label' => 'admin.booking.canceled_asker_booking_at.label',
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


        if (array_key_exists("CocoricoDeliveryBundle", $this->bundles)) {
            $formMapper
                ->with('admin.booking.delivery')
                ->add(
                    'deliveryAddress',
                    null,
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.delivery_address.label'
                    )
                )
                ->add(
                    'amountDelivery',
                    'price',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.delivery_amount.label',
                        'scale' => 2
                    )
                )
                ->end();
        } elseif (array_key_exists("CocoricoCarrierBundle", $this->bundles)) {
            $formMapper
                ->with('admin.booking.delivery')
                ->add(
                    'listing.location.completeAddress',
                    'text',
                    array(
                        'disabled' => true,
                        'label' => 'admin.listing.location.label'
                    )
                )
                ->add(
                    'pallets',
                    'number',
                    array(
                        'label' => 'listing.form.pallets',
                        'required' => true,
                        'constraints' => array(
                            new NotBlank(),
                            new Range(array('min' => 1, 'max' => 33))
                        )
                    ),
                    array(
                        'translation_domain' => 'cocorico_carrier_listing',
                    )
                )
                ->add(
                    'deliveryAddress',
                    null,
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.delivery_address.label'
                    )
                )
                ->add(
                    'amountDelivery',
                    'price',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.delivery_amount.label',
                        'include_vat' => true,
                        'scale' => 2
                    )
                )
                ->add(
                    'hatchback',
                    'checkbox',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.hatchback.label',
                    )
                )
                ->add(
                    'amountHatchback',
                    'price',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.hatchback_amount.label',
                        'include_vat' => true,
                        'scale' => 2
                    )
                )
                ->end();
        }


        if (array_key_exists("CocoricoListingOptionBundle", $this->bundles)) {
            $formMapper
                ->with('Options')
                ->add(
                    'amountOptions',
                    'price',
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.amount_options.title',
                        'include_vat' => $this->includeVat,
                        'scale' => 2
                    )
                )
                ->add(
                    'options',
                    'sonata_type_collection',
                    array(
                        'type_options' => array(
                            'delete' => false,
                            'delete_options' => array(
                                // You may otherwise choose to put the field but hide it
                                'type' => 'hidden',
                                // In that case, you need to fill in the options as well
                                'type_options' => array(
                                    'mapped' => false,
                                    'required' => false,
                                )
                            )
                        ),
                    ),
                    array(
                        'edit' => 'inline',
                        'inline' => 'table',
                    )
                )
                ->end();
        }

        if (array_key_exists("CocoricoMangoPayBundle", $this->bundles)) {
            $formMapper
                ->with('Mangopay')
                ->add(
                    'mangopayCardId',
                    null,
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.mangopay_card_id.label',
                    )
                )
                ->add(
                    'mangopayCardPreAuthId',
                    null,
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.mangopay_card_pre_auth_id.label',
                    )
                )
                ->add(
                    'mangopayPayinPreAuthId',
                    null,
                    array(
                        'disabled' => true,
                        'label' => 'admin.booking.mangopay_payin_pre_auth_id.label',
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
                    'choices' => array_flip(Booking::$statusValues),
                    'label' => 'admin.booking.status.label',
                    'translation_domain' => 'cocorico_booking',
                    'choices_as_values' => true
                )
            )
            ->add(
                'listing.id',
                null,
                array('label' => 'admin.booking.listing_id.label')
            )
            ->add(
                'listing.translations.title',
                'doctrine_orm_string',
                array('label' => 'admin.booking.listing_title.label')
            )
            ->add(
                'user.email',//todo: search by first name and last name
                null,
                array('label' => 'admin.booking.asker.label')
            )
            ->add(
                'listing.user.email',//todo: search by first name and last name
                null,
                array('label' => 'admin.booking.offerer.label')
            )
            ->add(
                'expireAt',
                'doctrine_orm_callback',
                array(
                    'label' => 'admin.booking.expire_at.label',
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        /** @var \DateTime $date */
                        $date = $value['value'];
                        if (!$date) {
                            return false;
                        }

                        $date->sub(new \DateInterval('PT' . $this->bookingExpirationDelay . 'M'));

                        $queryBuilder
                            ->andWhere("$alias.status IN (:status)")
                            ->andWhere("DATE_FORMAT($alias.newBookingAt,'%Y-%m-%d') = :dateExpiring")
                            ->setParameter('status', array(Booking::STATUS_NEW))
                            ->setParameter('dateExpiring', $date->format('Y-m-d'));

                        return true;
                    },
                    'field_type' => 'sonata_type_date_picker',
                    'field_options' => array('format' => 'dd/MM/yyyy'),
                ),
                null
            )
            ->add(
                'updatedAt',
                'doctrine_orm_callback',
                array(
                    'label' => 'admin.booking.updated_at.label',
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        /** @var \DateTime $date */
                        $date = $value['value'];
                        if (!$date) {
                            return false;
                        }

                        $queryBuilder
                            ->andWhere("DATE_FORMAT($alias.updatedAt,'%Y-%m-%d') = :updatedAt")
                            ->setParameter('updatedAt', $date->format('Y-m-d'));

                        return true;
                    },
                    'field_type' => 'sonata_type_date_picker',
                    'field_options' => array('format' => 'dd/MM/yyyy'),
                ),
                null
            )
            ->add(
                'amountMin',
                'doctrine_orm_callback',
                array(
                    'callback' => array($this, 'getAmountMinFilter'),
                    'field_type' => 'text',
                    'operator_type' => 'choice',
                    'operator_options' => array(
                        'choices' => array(
                            NumberType::TYPE_GREATER_THAN => '>=',
                        )
                    ),
                    'label' => 'admin.booking.amount_min.label'
                )
            )
            ->add(
                'amountMax',
                'doctrine_orm_callback',
                array(
                    'callback' => array($this, 'getAmountMaxFilter'),
                    'field_type' => 'text',
                    'operator_type' => 'choice',
                    'operator_options' => array(
                        'choices' => array(
                            NumberType::TYPE_LESS_EQUAL => '<=',
                        )
                    ),
                    'label' => 'admin.booking.amount_max.label'
                )
            );

        if (array_key_exists("CocoricoVoucherBundle", $this->bundles)) {
            $datagridMapper
                ->add(
                    'codeVoucher',
                    null,
                    array('label' => 'admin.booking.code_voucher.label')
                );
        }
    }

    public function getAmountMinFilter($queryBuilder, $alias, $field, $value)
    {
        if (!$value['type']) {
            $value['type'] = NumberType::TYPE_GREATER_EQUAL;
        }

        return $this->getAmountFilter($queryBuilder, $alias, $field, $value);
    }

    public function getAmountMaxFilter($queryBuilder, $alias, $field, $value)
    {
        if (!$value['type']) {
            $value['type'] = NumberType::TYPE_LESS_EQUAL;
        }

        return $this->getAmountFilter($queryBuilder, $alias, $field, $value);
    }

    public function getAmountFilter($queryBuilder, $alias, $field, $value)
    {
        if (!$value['value']) {
            return false;
        }

        $value['value'] = $value['value'] * 100;

        if ($value['type'] === NumberType::TYPE_GREATER_EQUAL) {
            $queryBuilder
                ->andWhere($alias . '.amountTotal >= :valueMin')
                ->setParameter('valueMin', $value['value']);

            return true;
        }


        if ($value['type'] === NumberType::TYPE_LESS_EQUAL) {
            $queryBuilder
                ->andWhere($alias . '.amountTotal <= :valueMax')
                ->setParameter('valueMax', $value['value']);

            return true;
        }

        return false;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'listing.id',
                null,
                array(
                    'label' => 'admin.booking.listing_id.label'
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
                'validated',
                null,
                array(
                    'label' => 'admin.booking.validated.label',
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
                'listing.user',
                null,
                array(
                    'label' => 'admin.booking.offerer.label',
                )
            )
            ->add(
                'listing',
                null,
                array(
                    'label' => 'admin.listing.label'
                )
            )
            ->add(
                'amountToPayByAskerDecimal',
                null,
                array(
                    'label' => 'admin.booking.amount_to_pay_by_asker.label',
                )
            )
            ->add(
                'start',
                'date',
                array(
                    'label' => 'admin.booking.start.label',
                    'format' => 'd/m/Y'
                )
            )
            ->add(
                'end',
                'date',
                array(
                    'label' => 'admin.booking.end.label',
                    'format' => 'd/m/Y'
                )
            );

        if (!$this->timeUnitIsDay) {
            $listMapper
                ->add(
                    'startTime',
                    'time',
                    array(
                        'label' => 'admin.booking.start_time.label',
                    )
                )
                ->add(
                    'endTime',
                    'time',
                    array(
                        'label' => 'admin.booking.end_time.label',
                    )
                );
        }

        $listMapper
            ->add(
                'expiration',
                null,
                array(
                    'template' => 'CocoricoSonataAdminBundle::list_booking_expiration_date.html.twig',
                    'label' => 'admin.booking.expire_at.label',
                    'bookingExpirationDelay' => $this->bookingExpirationDelay,
                    'bookingAcceptationDelay' => $this->bookingAcceptationDelay,
                )
            );
//            ->add(
//                'updatedAt',
//                null,
//                array(
//                    'label' => 'admin.booking.updated_at.label',
//                    'format' => 'd/m/Y'
//                )
//            );


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
            'Listing Id' => 'listing.id',
            'Status' => 'statusText',
            'Validated' => 'validated',
            'Asker' => 'user.fullname',
            'Offerer' => 'listing.user.fullname',
            'Listing' => 'listing.title',
            'Amount' => 'amountTotalDecimal',
            'Booking from' => 'start',
            'Booking to' => 'end',
            'Expire At' => 'endTime',
            'Updated At' => 'updatedAt'
        );
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
