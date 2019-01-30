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
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class BookingEditType extends AbstractType implements TranslationContainerInterface
{
    public static $tacError = 'listing.form.tac.error';

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Booking $booking */
        //$booking = $builder->getData();
        $builder
            ->add(
                "tac",
                "checkbox",
                array(
                    'label' => 'booking.form.tac',
                    'mapped' => false,
                    'constraints' => new IsTrue(
                        array(
                            "message" => self::$tacError
                        )
                    ),
                )
            )
            ->add(
                'message',
               TextareaType::class,
                array(
                    'mapped' => false,
                    'label' => 'booking.form.message',
                    'required' => true,
                    'constraints' => new NotBlank(),
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
                'data_class' => 'Cocorico\CoreBundle\Entity\Booking',
                'csrf_token_id' => 'booking_edit',
                'translation_domain' => 'cocorico_booking',
                'constraints' => new Valid(),
//                'validation_groups' => array('edit', 'default'),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'booking_edit';
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        $messages[] = new Message(self::$tacError, 'cocorico');

        return $messages;
    }
}
