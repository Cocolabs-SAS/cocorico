<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Form\Type\Frontend;

use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form type for a reply
 *
 *
 */
class ReplyMessageFormType extends AbstractType implements TranslationContainerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'body',
            'textarea',
            array(
                /** @Ignore */
                'label' => false,
                'required' => true,
                'constraints' => array(
                    new NotBlank(array('message' => NewThreadMessageFormType::$messageError))
                )
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'intention' => 'reply',
            )
        );
    }

    public function getName()
    {
        return 'fos_message_reply_message';
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        return NewThreadMessageFormType::getTranslationMessages();
    }

}
