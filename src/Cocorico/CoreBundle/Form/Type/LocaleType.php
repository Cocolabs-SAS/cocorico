<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleType extends AbstractType
{
    protected $locales = array();

    public function __construct($locales = array())
    {
        foreach ($locales as $locale) {
            $this->locales['cocorico.' . $locale] = $locale;
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'mapped' => false,
                'choices' => $this->locales,
                'choices_as_values' => true,
                'translation_domain' => 'cocorico_user',
                /** @Ignore */
                'label' => false
            )
        );
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'choice';
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
        return 'locale_choice';
    }

}
