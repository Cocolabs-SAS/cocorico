<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Dashboard;

use Cocorico\CoreBundle\Entity\Booking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BookingStatusFilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'status',
                'choice',
                array(
                    'mapped' => false,
                    /** @Ignore */
                    'label' => false,
                    'choices' => Booking::getVisibleStatusValues(),
                    'empty_value' => 'admin.booking.status.label',
                    'translation_domain' => 'cocorico_booking'
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'translation_domain' => 'cocorico_booking',
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'booking_status_filter';
    }

}
