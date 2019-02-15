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

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingImage;
use Cocorico\CoreBundle\Form\Type\ImageType;
use Cocorico\CoreBundle\Form\Type\ListingImageType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingEditImagesType extends ListingEditType
{
    /**
     * @var array|string uploaded files
     */
    protected $uploaded;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'image',
                ImageType::class
            )
            ->add(
                'images',
                CollectionType::class,
                array(
                    'allow_delete' => true,
                    'entry_type' => ListingImageType::class,
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
                /** @var Listing $listing */
                $listing = $event->getData();

                if ($this->uploaded) {
                    $nbImages = $listing->getImages()->count();
                    //Add new images
                    $imagesUploadedArray = explode(",", trim($this->uploaded, ","));
                    foreach ($imagesUploadedArray as $i => $image) {
                        $listingImage = new ListingImage();
                        $listingImage->setListing($listing);
                        $listingImage->setName($image);
                        $listingImage->setPosition($nbImages + $i + 1);
                        $listing->addImage($listingImage);
                    }

                    $event->setData($listing);
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
        return 'listing_edit_images';
    }
}
