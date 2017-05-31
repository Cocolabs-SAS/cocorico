<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\DataTransformer;

use Lexik\Bundle\CurrencyBundle\Currency\Converter;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PriceTransformer implements DataTransformerInterface
{

    protected $currency;
    protected $defaultCurrency;
    protected $currencyConverter;
    protected $pricePrecision;

    /**
     * @param string    $currency
     * @param string    $defaultCurrency
     * @param Converter $currencyConverter
     * @param int       $pricePrecision
     */
    public function __construct($currency, $defaultCurrency, $currencyConverter, $pricePrecision)
    {
        $this->currency = $currency;
        $this->defaultCurrency = $defaultCurrency;
        $this->currencyConverter = $currencyConverter;
        $this->pricePrecision = $pricePrecision;
    }

    /**
     * Transform value from database
     *
     * @param  mixed $value
     * @return mixed
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if ($this->currency != $this->defaultCurrency) {
            $value = $this->currencyConverter->convert($value, $this->currency, !$this->pricePrecision);
        }

        return $value / 100;
    }

    /**
     * Transform value to database
     *
     * @param  mixed $value
     * @return mixed|null|object
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }


        if ($this->currency != $this->defaultCurrency) {
            $value = $this->currencyConverter->convert(
                $value,
                $this->defaultCurrency,
                !$this->pricePrecision,
                $this->currency
            );
        }

        if (null === $value) {
            throw new TransformationFailedException();
        }

        return strval($value * 100);
    }

}
