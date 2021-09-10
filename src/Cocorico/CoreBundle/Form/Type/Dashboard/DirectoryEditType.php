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
use Cocorico\CoreBundle\Entity\DirectoryLabel;
use Cocorico\CoreBundle\Form\Type\DirectoryLabelType;
use Cocorico\CoreBundle\Entity\DirectoryImage;
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
use Cocorico\CoreBundle\Form\Type\DirectoryClientImageType;
use Cocorico\CoreBundle\Form\Type\DirectoryImageType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Cocorico\CoreBundle\Form\Type\ImageType;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
                'siret',
                TextType::class,
                array(
                    'disabled' => 'disabled',
                )
            )
            ->add(
                'brand',
                TextType::class,
                array(
                    'disabled' => 'disabled',
                )
            )
            ->add(
                'name',
                TextType::class,
                array(
                    'disabled' => 'disabled',
                )
            )
            ->add(
                'kind',
                TextType::class,
                array(
                    'disabled' => 'disabled',
                )
            )
            ->add(
                'region',
                TextType::class,
                array(
                    'disabled' => 'disabled',
                )
            )
            ->add(
                'postCode',
                TextType::class,
                array(
                    'disabled' => 'disabled',
                )
            )
            ->add(
                'description',
                TextareaType::class,
                array(
                    'label' => 'directory.form.description',
                    'required' => false,
                    'empty_data' => '',
                    'attr' => ['rows'=> 10]
                )
            )
            ->add(
                'isCoContracting',
                ChoiceType::class,
                array(
                    'expanded' => true,
                    'multiple' => false,
                    'required' => false,
                    'choices' => [
                        'Oui' => true,
                        'Non' => false,
                    ],
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
            )
            ->add(
                'website',
                UrlType::class,
                array(
                    'label' => 'directory.form.url',
                    'required' => false,
                )
            )
            ->add(
                'range',
                IntegerType::class,
                array(
                    'label' => 'directory.form.range',
                    'required' => false,
                )
            )
            ->add(
                'polRange',
                ChoiceType::class,
                array(
                    'choices' => array_flip(Directory::$polRangeValues),
                    'label' => 'Périmètre intervention',
                    'translation_domain' => 'cocorico_directory',
                    'expanded' => true,
                    'required' => true
                )
            )
            ->add(
                'image',
                ImageType::class
            )
            ->add(
                'labels',
                CollectionType::class,
                array(
                    'allow_delete' => true,
                    'allow_add' => true,
                    'by_reference' => false,
                    'entry_type' => DirectoryLabelType::class,
                    'entry_options' => [
                        'attr' => ['class' => 'dir-label'],
                    ],
                )
            )
            ->add(
                'images',
                CollectionType::class,
                array(
                    'allow_delete' => true,
                    'by_reference' => false,
                    'entry_type' => DirectoryImageType::class,
                    /** @Ignore */
                    'label' => false
                )
            );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                $data = $data ?: array();
                if (array_key_exists('uploaded', $data["image"])) {
                    // capture uploaded files and store them for onSubmit event
                    $this->uploaded = $data["image"]['uploaded'];
                }
            }
        );


        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var Directory $directory */
                $directory = $event->getData();

                if ($this->uploaded) {
                    $nbImages = $directory->getImages()->count();
                    //Add new images
                    $imagesUploadedArray = explode(",", trim($this->uploaded, ","));
                    foreach ($imagesUploadedArray as $i => $image) {
                        $directoryImage = new DirectoryImage();
                        $directoryImage->setDirectory($directory);
                        $directoryImage->setName($image);
                        $directoryImage->setPosition($nbImages + $i + 1);
                        $directory->addImage($directoryImage);
                    }

                    $event->setData($directory);
                }
            }
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
