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

use Cocorico\CoreBundle\Form\Type\LanguageFilteredType;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingEditDescriptionType extends ListingEditType implements TranslationContainerInterface
{

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        //Translations fields
        $titles = $descriptions = $rules = array();
        foreach ($this->locales as $i => $locale) {
            $titles[$locale] = array(
                /** @Ignore */
                'label' => "listing.form.title.$locale"
            );
            $descriptions[$locale] = array(
                /** @Ignore */
                'label' => "listing.form.description.$locale"
            );
            $rules[$locale] = array(
                /** @Ignore */
                'label' => "listing.form.rules.$locale"
            );
        }

        $builder
            ->add(
                'translations',
                'a2lix_translations',
                array(
                    'required_locales' => array($this->locale),
                    'fields' => array(
                        'title' => array(
                            'field_type' => 'text',
                            'locale_options' => $titles
                        ),
                        'description' => array(
                            'field_type' => 'textarea',
                            'locale_options' => $descriptions
                        ),
                        'rules' => array(
                            'field_type' => 'textarea',
                            'locale_options' => $rules
                        ),
                        'slug' => array(
                            'field_type' => 'hidden'
                        )
                    ),
                    /** @Ignore */
                    'label' => false
                )
            )
            ->add(
                'fromLang',
                'language_filtered',
                array(
                    'mapped' => false,
                    'label' => 'cocorico.from',
                    'data' => $this->locale,
                    'translation_domain' => 'cocorico_user'
                )
            )
            ->add(
                'toLang',
                'language_filtered',
                array(
                    'mapped' => false,
                    'label' => 'cocorico.to',
                    'data' => LanguageFilteredType::getLocaleTo($this->locales, $this->locale),
                    'translation_domain' => 'cocorico_user'
                )
            );

        //Status field already added
        //$builder->remove('status');
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
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
        return 'listing_edit_description';
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages[] = new Message("listing.form.title.en", 'cocorico_listing');
        $messages[] = new Message("listing.form.title.fr", 'cocorico_listing');
        $messages[] = new Message("listing.form.description.en", 'cocorico_listing');
        $messages[] = new Message("listing.form.description.fr", 'cocorico_listing');
        $messages[] = new Message("listing.form.rules.en", 'cocorico_listing');
        $messages[] = new Message("listing.form.rules.fr", 'cocorico_listing');
        $messages[] = new Message("cocorico.en", 'cocorico_listing');
        $messages[] = new Message("cocorico.fr", 'cocorico_listing');
        $messages[] = new Message("listing_translations_en_title_placeholder", 'cocorico_listing');
        $messages[] = new Message("listing_translations_fr_title_placeholder", 'cocorico_listing');
        $messages[] = new Message("listing_translations_en_description_placeholder", 'cocorico_listing');
        $messages[] = new Message("listing_translations_fr_description_placeholder", 'cocorico_listing');
        $messages[] = new Message("listing_translations_en_rules_placeholder", 'cocorico_listing');
        $messages[] = new Message("listing_translations_fr_rules_placeholder", 'cocorico_listing');

        return $messages;
    }

}
