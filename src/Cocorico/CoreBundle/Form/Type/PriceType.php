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

use Cocorico\CoreBundle\Form\DataTransformer\PriceTransformer;
use Lexik\Bundle\CurrencyBundle\Currency\Converter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PriceType extends AbstractType
{
    protected $currency;
    protected $defaultCurrency;
    protected $pricePrecision;
    protected $currencyConverter;

    /**
     * @param string    $defaultCurrency
     * @param int       $pricePrecision
     * @param Converter $currencyConverter
     */
    public function __construct($defaultCurrency, $pricePrecision, Converter $currencyConverter)
    {
        $this->defaultCurrency = $defaultCurrency;
        $this->currency = $defaultCurrency;
        $this->pricePrecision = $pricePrecision;
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new PriceTransformer(
            $options["currency"],
            $this->defaultCurrency,
            $this->currencyConverter,
            $this->pricePrecision
        );
        $builder->addModelTransformer($transformer);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'cocorico_listing',
                'scale' => $this->pricePrecision,
                'defaultCurrency' => $this->defaultCurrency,
                'currency' => $this->defaultCurrency,
                'attr' => array(
                    'class' => 'numbers-only'
                ),
                'include_vat' => null //if true then incl tax is displayed else excl tax is displayed
            )
        );
    }


    /**
     * Pass the include_vat to the view
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (array_key_exists('include_vat', $options) && $options["include_vat"] !== null) {
            // set an "include_vat" variable that will be available when rendering this field
            $view->vars['include_vat'] = $options["include_vat"];
        }
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return MoneyType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'price';
    }
}
