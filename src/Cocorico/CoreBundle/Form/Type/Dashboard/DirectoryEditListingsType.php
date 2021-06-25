<?php
namespace Cocorico\CoreBundle\Form\Type\Dashboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Repository\ListingRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class DirectoryEditListingsType extends AbstractType
{
    // private $UserId;

    // public function __construct($UserId)
    // {
    //     $this->UserId = $UserId;
    // }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $user_id = $options['user_id'];
        $builder
            ->add(
                'listings',
                EntityType::class,
                array(
                    'class' => Listing::class,
                    'choice_label' => function($listing) {
                        return $listing->getTitle();
                    },
                    'query_builder' => function (ListingRepository $er) use (&$user_id) {
                        return $er->getFindByOwnerQuery(
                            $user_id,
                            'fr',
                            Listing::$visibleStatus);
                    },
                    'multiple' => true,
                    'expanded' => true,
                    'choice_attr' =>  function($choice, $key, $value) {
                        return [ 'class' => 'dir_multi_choice' ];
                    }
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
                'user_id' => false,
                'constraints' => new Valid(),//To have error on collection item field,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'directory_edit_listings';
    }

}
