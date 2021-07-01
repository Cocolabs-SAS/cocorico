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
use Cocorico\CoreBundle\Entity\DirectoryClientImage;
use Cocorico\CoreBundle\Form\Type\ImageType;
use Cocorico\CoreBundle\Form\Type\DirectoryClientImageType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DirectoryEditClientImagesType extends DirectoryEditType
{
    /**
     * @var array|string uploaded files
     */
    protected $uploaded;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'clientImage',
                ImageType::class
            )
            ->add(
                'clientImages',
                CollectionType::class,
                array(
                    'allow_delete' => true,
                    'by_reference' => false,
                    'entry_type' => DirectoryClientImageType::class,
                    /** @Ignore */
                    'label' => false
                )
            );


        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                $data = $data ?: array();
                if (array_key_exists('uploaded', $data["clientImage"])) {
                    // capture uploaded files and store them for onSubmit event
                    $this->uploaded = $data["clientImage"]['uploaded'];
                }
            }
        );


        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var Directory $directory */
                $directory = $event->getData();

                if ($this->uploaded) {
                    $nbImages = $directory->getClientImages()->count();
                    //Add new images
                    $imagesUploadedArray = explode(",", trim($this->uploaded, ","));
                    foreach ($imagesUploadedArray as $i => $image) {
                        $directoryImage = new DirectoryClientImage();
                        $directoryImage->setDirectory($directory);
                        $directoryImage->setName($image);
                        $directoryImage->setPosition($nbImages + $i + 1);
                        $directory->addClientImage($directoryImage);
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
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'directory_edit_client_images';
    }
}
