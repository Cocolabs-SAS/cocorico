<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Frontend;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ListingLocationSearchType extends AbstractType implements TranslationContainerInterface
{

    public static $locationCountryError = 'listing.search.form.country.not_blank';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'address',
                SearchType::class,
                array(
                    'label' => 'listing.search.form.address'
                )
            )
            ->add('lat', HiddenType::class)
            ->add('lng', HiddenType::class)
            ->add('viewport', HiddenType::class)
            ->add(
                'country',
                HiddenType::class,
                array(
                    'constraints' => array(
                        new NotBlank(
                            array(
                                "message" => self::$locationCountryError
                            )
                        ),
                    ),
                )
            )
            ->add('area', HiddenType::class)
            ->add('department', HiddenType::class)
            ->add('city', HiddenType::class)
            ->add('zip', HiddenType::class)
            ->add('route', HiddenType::class)
            ->add('streetNumber', HiddenType::class)
            ->add('addressType', HiddenType::class);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'data_class' => 'Cocorico\CoreBundle\Model\ListingLocationSearchRequest',
                'translation_domain' => 'cocorico_listing',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_location_search';
    }


    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        $messages[] = new Message(self::$locationCountryError, 'cocorico');

        return $messages;
    }
}
