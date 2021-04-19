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

use Cocorico\CoreBundle\Entity\Directory;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\Valid;

class DirectoryEditType extends AbstractType implements TranslationContainerInterface
//class DirectoryEditType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // parent::buildForm($builder, $options);

        $builder
            ->add(
                'brand',
                TextType::class,
                array(
                    'label' => 'directory.form.brand',
                    'required' => false,
                )
            )
            ->add(
                'description',
                TextareaType::class,
                array(
                    'label' => 'directory.form.description',
                    'required' => false,
                    'attr' => ['rows'=> 10]
                )
            )
            ->add(
                'prestaType_disp',
                CheckBoxType::class,
                array(
                    'label' => 'Mise à disposition du personnel / Interim',
                    'required' => false,
                )
            )
            ->add(
                'prestaType_prest',
                CheckBoxType::class,
                array(
                    'label' => 'Prestation de service',
                    'required' => false,
                )
            )
            ->add(
                'prestaType_build',
                CheckBoxType::class,
                array(
                    'label' => 'Fabrication et commercialisation de biens',
                    'required' => false,
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
                'constraints' => new Valid(),
            )
        );

    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'directory_edit';
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        $messages[] = new Message("directory.form.title.en", 'cocorico_directory');
        $messages[] = new Message("directory.form.title.fr", 'cocorico_directory');
        $messages[] = new Message("directory.form.description.en", 'cocorico_directory');
        $messages[] = new Message("directory.form.description.fr", 'cocorico_directory');
        $messages[] = new Message("directory.form.rules.en", 'cocorico_directory');
        $messages[] = new Message("directory.form.rules.fr", 'cocorico_directory');
        $messages[] = new Message("cocorico.en", 'cocorico_directory');
        $messages[] = new Message("cocorico.fr", 'cocorico_directory');
        $messages[] = new Message("directory_translations_en_title_placeholder", 'cocorico_directory');
        $messages[] = new Message("directory_translations_fr_title_placeholder", 'cocorico_directory');
        $messages[] = new Message("directory_translations_en_description_placeholder", 'cocorico_directory');
        $messages[] = new Message("directory_translations_fr_description_placeholder", 'cocorico_directory');
        $messages[] = new Message("directory_translations_en_rules_placeholder", 'cocorico_directory');
        $messages[] = new Message("directory_translations_fr_rules_placeholder", 'cocorico_directory');

        return $messages;
    }

}
