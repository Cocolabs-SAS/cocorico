<?php
namespace Cocorico\CoreBundle\Form\Type\Dashboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Cocorico\CoreBundle\Entity\Network;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

# See here :
# https://symfony.com/doc/current/reference/forms/types/entity.html#using-a-custom-query-for-the-entities
class DirectoryEditNetworksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'networks',
                EntityType::class,
                array(
                    'class' => Network::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => true,
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
        return 'directory_edit_networks';
    }

}
