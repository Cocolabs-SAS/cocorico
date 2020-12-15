<?php
namespace Cocorico\CoreBundle\Form\Type\Frontend;

use Cocorico\CoreBundle\Entity\Directory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DirectoryFilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sector', TextType::class)
            ->add('postalCode', TextType::class)
            ->add('structureType', TextType::class)
            ->add('prestaType', TextType::class);
            // ->add('save', SubmitType::class, ['label' => 'Filtrer'])
            // ->add(
            //     'status',
            //     ChoiceType::class,
            //     array(
            //         'mapped' => false,
            //         /** @Ignore */
            //         'label' => false,
            //         'choices' => array_flip(Quote::getVisibleStatusValues()),
            //         'placeholder' => 'admin.quote.status.label',
            //         'translation_domain' => 'cocorico_quote',
            //     )
            // );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'translation_domain' => 'cocorico_quote',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'directory_filter';
    }
}
