<?php

namespace Cocorico\CoreBundle\Form\Type\Frontend;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('frequency_hours', NumberType::class)
            ->add('frequency_period', ChoiceType::class, ['choices' => ['month', 'week']])
            ->add('surface_m2', NumberType::class)
            ->add('surface_type', ChoiceType::class, ['choices' => ['wood', 'concrete']])
            ->add('communication', TextareaType::class);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        // Nothing yet
    }
}
