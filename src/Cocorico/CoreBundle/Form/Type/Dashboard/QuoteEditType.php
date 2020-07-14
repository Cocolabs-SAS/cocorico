<?php

namespace Cocorico\CoreBundle\Form\Type\Dashboard;

use Cocorico\CoreBundle\Entity\Quote;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class QuoteEditType extends AbstractType implements TranslationContainerInterface
{
    public static $tacError = 'listing.form.tac.error';

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Quote $quote */
        //$quote = $builder->getData();
        $builder
            ->add(
                'message',
               TextareaType::class,
                array(
                    'mapped' => false,
                    'label' => 'quote.form.message',
                    'required' => false,
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
                'data_class' => 'Cocorico\CoreBundle\Entity\Quote',
                'csrf_token_id' => 'quote_edit',
                'translation_domain' => 'cocorico_quote',
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
        return 'quote_edit';
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
