<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ReviewBundle\Form\Type\Dashboard;

use Cocorico\ReviewBundle\Form\Type\StarRatingType;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ReviewType extends AbstractType implements TranslationContainerInterface
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'rating',
                StarRatingType::class,
                array(
                    /** @Ignore */
                    'choices' =>
                        array(
                            "1" => 1,
                            "2" => 2,
                            "3" => 3,
                            "4" => 4,
                            "5" => 5
                        ),
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'review.form.rating.label',
                    'required' => true,
                )
            )
            ->add(
                'comment',
                TextareaType::class,
                array(
                    'label' => 'review.form.comment.label',
                    'required' => true,
                )
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\ReviewBundle\Entity\Review',
                'translation_domain' => 'cocorico_review',
                'constraints' => new Valid(),
                'validation_groups' => array('new'),
                'csrf_token_id' => 'review_new',
            )
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'review_new';
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages[] = new Message("cocorico_review.rating.not_blank", 'cocorico_review');
        $messages[] = new Message("cocorico_review.comment.not_blank", 'cocorico_review');

        return $messages;
    }
}
