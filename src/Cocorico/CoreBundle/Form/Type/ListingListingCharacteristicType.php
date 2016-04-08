<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type;

use Cocorico\CoreBundle\Entity\ListingListingCharacteristic;
use Cocorico\CoreBundle\Repository\ListingCharacteristicValueRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingListingCharacteristicType extends AbstractType
{
    protected $locale;

    /**
     * @param   $locale
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                /** @var ListingListingCharacteristic $llc */
                $llc = $event->getData();

                $form->add(
                    'listingCharacteristicValue',
                    'entity',
                    array(
                        'query_builder' => function (ListingCharacteristicValueRepository $lcvr) use ($llc) {
                            $lct = $llc->getListingCharacteristic()->getListingCharacteristicType();

                            return $lcvr->getFindAllTranslatedQueryBuilder(
                                $lct,
                                $this->locale
                            );
                        },
                        'empty_value' => 'listing.form.characteristic.choose',
                        'property' => 'translations[' . $this->locale . '].name',
                        'class' => 'Cocorico\CoreBundle\Entity\ListingCharacteristicValue',
                    )
                );
            }
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Entity\ListingListingCharacteristic',
                'translation_domain' => 'cocorico_listing'
            )
        );
    }

    /**
     * BC
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_listing_characteristic';
    }
}
