<?php

namespace Cocorico\CurrencyBundle\Adapter;

use Doctrine\ORM\EntityManager;
use Lexik\Bundle\CurrencyBundle\Adapter\AbstractCurrencyAdapter;
use Lexik\Bundle\CurrencyBundle\Entity\Currency;

//todo: remove when https://github.com/lexik/LexikCurrencyBundle/commit/a4c08e0 will be released
class DoctrineCurrencyAdapter extends AbstractCurrencyAdapter
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * {@inheritdoc}
     */
    public function attachAll()
    {
        // nothing here
    }

    /**
     * Return identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return 'doctrine';
    }

    /**
     * @param EntityManager $manager
     */
    public function setManager(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($index)
    {
        if (!$this->isInitialized()) {
            $this->initialize();
        }

        return parent::offsetExists($index);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($index)
    {
        if (!$this->isInitialized()) {
            $this->initialize();
        }

        return parent::offsetGet($index);
    }

    /**
     * @return bool
     */
    private function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * @throws \Exception
     */
    private function initialize()
    {
        if (!isset($this->manager)) {
            throw new \RuntimeException('No ObjectManager set on DoctrineCurrencyAdapter.');
        }

        $currencies = $this->manager
            ->getRepository($this->currencyClass)
            ->findAll();

        /** @var Currency $currency */
        foreach ($currencies as $currency) {
            $this[$currency->getCode()] = $currency;
        }

        $this->initialized = true;
    }
}
