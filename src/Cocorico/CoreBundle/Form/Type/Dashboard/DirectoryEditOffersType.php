<?php
namespace Cocorico\CoreBundle\Form\Type\Dashboard;

use Symfony\Component\Form\AbstractType;
use Cocorico\CoreBundle\Form\Type\DirectoryOfferType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class DirectoryEditOffersType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // parent::buildForm($builder, $options);
        $builder->add(
                'offers',
                CollectionType::class,
                array(
                    'allow_delete' => true,
                    'allow_add' => true,
                    'by_reference' => false,
                    'entry_type' => DirectoryOfferType::class,
                    'entry_options' => [
                        'attr' => ['class' => 'dir-label'],
                    ],
                )
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Entity\Directory',
                'csrf_token_id' => 'directory_edit',
                'translation_domain' => 'cocorico_directory',
                'constraints' => new Valid(),//To have error on collection item field,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'directory_edit_offers';
    }

}
