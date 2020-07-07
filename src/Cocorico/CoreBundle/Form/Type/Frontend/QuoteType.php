<?php

namespace Cocorico\CoreBundle\Form\Type\Frontend;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class QuoteType extends AbstractType
{
    public function __construct()
    {
        // Nothing yet
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options 
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $quote = $builder->getData();

        $builder
            ->add('budget', NumberType::class)
            ->add('prestaStartDate',
                DateType::class,
                array_merge(
                    array(
                        'property_path' => 'prestaStartDate',
                        'widget' => 'single_text',
                        'format' => 'dd/MM/yyyy',
                    )
                ))
            # ->add('frequency_period', ChoiceType::class, ['choices' => ['month', 'week']])
            # ->add('surface_m2', NumberType::class)
            # ->add('surface_type', ChoiceType::class, ['choices' => ['wood', 'concrete']])
            ->add('communication', TextareaType::class);
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Entity\Quote',
                'csrf_token_id' => 'quote',
                'translation_domain' => 'cocorico_quote',
                'constraints' => new Valid(),
                'validation_groups' => array('new'),
//                'error_bubbling' => false,//To prevent errors bubbling up to the parent form
//                //To map errors of all unmapped properties (date_range) to a particular field (date_range)
//                'error_mapping' => array(
//                    '.' => 'date_range',
//                ),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'quote';
    }
}
