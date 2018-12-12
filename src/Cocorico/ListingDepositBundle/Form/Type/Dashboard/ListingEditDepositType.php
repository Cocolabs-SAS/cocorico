<?php

namespace Cocorico\ListingDepositBundle\Form\Type\Dashboard;

use Cocorico\CoreBundle\Entity\Listing;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ListingEditDepositType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'amountDeposit',
                'price',
                array(
                    'label' => 'listing_edit.form.deposit',
                    'translation_domain' => 'cocorico_listing_deposit',
                    'required' => false
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
                'data_class' => 'Cocorico\CoreBundle\Entity\Listing',
                'translation_domain' => 'cocorico_listing_deposit',
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'listing_edit_deposit';
    }

}
