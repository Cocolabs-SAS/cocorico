<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ContactBundle\Form\Type\Frontend;

use Cocorico\ContactBundle\Entity\Contact;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactNewType extends AbstractType implements TranslationContainerInterface
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstName',
                null,
                array(
                    'label' => 'contact.form.first_name.label'
                )
            )
            ->add(
                'lastName',
                null,
                array(
                    'label' => 'contact.form.last_name.label'
                )
            )
            ->add(
                'email',
                null,
                array(
                    'label' => 'contact.form.email.label'
                )
            )
            ->add(
                'phone',
                null,
                array(
                    'label' => 'contact.form.phone.label'
                )
            )
            ->add(
                'subject',
                null,
                array(
                    'label' => 'contact.form.subject.label'
                )
            )
            ->add(
                'message',
                null,
                array(
                    'label' => 'contact.form.message.label'
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
                'data_class' => 'Cocorico\ContactBundle\Entity\Contact',
                'translation_domain' => 'cocorico_contact',
                'cascade_validation' => true,
                'validation_groups' => array('CocoricoContact'),
            )
        );
    }

    /**
     * BC
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'contact_new';
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages[] = new Message("entity.contact.status.new", 'cocorico_contact');
        $messages[] = new Message("entity.contact.status.read", 'cocorico_contact');

        $messages[] = new Message("cocorico_contact.first_name.blank", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.first_name.short", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.first_name.long", 'cocorico_contact');

        $messages[] = new Message("cocorico_contact.last_name.blank", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.last_name.short", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.last_name.long", 'cocorico_contact');

        $messages[] = new Message("cocorico_contact.email.invalid", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.email.blank", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.email.short", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.email.long", 'cocorico_contact');

        $messages[] = new Message("cocorico_contact.phone.short", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.phone.long", 'cocorico_contact');

        $messages[] = new Message("cocorico_contact.subject.blank", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.subject.short", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.subject.long", 'cocorico_contact');

        $messages[] = new Message("cocorico_contact.message.blank", 'cocorico_contact');
        $messages[] = new Message("cocorico_contact.message.short", 'cocorico_contact');

        return $messages;
    }
}
