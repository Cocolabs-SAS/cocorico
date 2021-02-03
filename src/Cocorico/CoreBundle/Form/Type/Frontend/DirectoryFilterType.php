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
            ->add('format',
                ChoiceType::class,
                array(
                    'mapped' => false,
                    'expanded' => false,
                    'empty_data' => 'xlsx',
                    'choices' => [
                        'Excel' => 'xlsx',
                        'CSV' => 'csv',
                        'Open Office' => 'ods',
                    ],
                )
            )
            ->add('sector', 
                ChoiceType::class,
                array(
                    'expanded' => false,
                    'empty_data' => '',
                    'choices' => array_flip(Directory::$sectorValues),
                )
            )
            ->add('postalCode', TextType::class)
            ->add('structureType',
                ChoiceType::class,
                array(
                    'expanded' => false,
                    'empty_data' => '',
                    'choices' => array_flip(Directory::$kindValues),
                )
            )
            ->add('prestaType',
                ChoiceType::class,
                array(
                    'expanded' => false,
                    'empty_data' => '',
                    'choices' => array_flip(Directory::$prestaTypeValues),
                )
            );
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
