<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ConfigBundle\DependencyInjection\Compiler;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;
use Symfony\Component\DependencyInjection\ContainerBuilder as BaseContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ContainerBuilder extends BaseContainerBuilder
{
    /**
     * @var Connection $connection
     */
    protected $connection;

    /**
     * Constructor
     *
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        parent::__construct($parameterBag);

        if (class_exists('ProxyManager\Configuration')) {
            $this->setProxyInstantiator(new RuntimeInstantiator());
        }
    }

    /**
     * Compiles the container.
     *
     * This method adds database parameters before compiler passes
     */
    public function compile()
    {
        try {
            $this->connect();
            $this->setDBParameters();
            $this->close();
        } catch (\PDOException $e) {
            parent::compile();

            return;
        }

        parent::compile();
    }

    /**
     * Establishes the connection with the database
     */
    protected function connect()
    {
        $configs = $this->getExtensionConfig('doctrine');

        $mergedConfig = array();
        foreach ($configs as $config) {
            $mergedConfig = array_merge($mergedConfig, $config);
        }
        $mergedConfig = $this->getParameterBag()->resolveValue($mergedConfig);
        $params = $mergedConfig['dbal'];

        $connectionFactory = new ConnectionFactory(array());
        $this->connection = $connectionFactory->createConnection($params);
        $this->connection->connect();
    }

    /**
     * Close database connection
     */
    protected function close()
    {
        if ($this->connection->isConnected()) {
            $this->connection->close();
        }
    }

    /**
     * Check if table exists
     *
     * @param string $table name
     *
     * @return bool
     */
    protected function tableExist($table)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('*');
        $queryBuilder->from($table, 't');

        try {
            $this->connection->query($queryBuilder);
        } catch (DBALException $e) {
            return false;
        }

        return true;
    }


    /**
     * Returns the query used to get database parameters
     *
     * @return QueryBuilder
     */
    protected function createParametersQuery()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->select('p.name, p.value')
            ->from('parameter', 'p')
            ->where('p.value IS NOT NULL');

        return $queryBuilder;
    }

    /**
     * Adds database parameters to the container's parameterBag
     */
    protected function setDBParameters()
    {
        if (false === $this->tableExist('parameter')) {
            return;
        }

        //Get allowed parameters
        $configs = $this->getExtensionConfig('cocorico_config');
        $mergedConfig = array();
        foreach ($configs as $config) {
            $mergedConfig = array_merge($mergedConfig, $config);
        }
        $mergedConfig = $this->getParameterBag()->resolveValue($mergedConfig);
        $allowedParameters = $mergedConfig['parameters_allowed'];
        $allowedParameters = array_keys($allowedParameters);

        $query = $this->connection->query($this->createParametersQuery());
        $parameters = $query->fetchAll();
        foreach ($parameters as $i => $parameter) {
            $name = $parameter['name'];
            $value = $parameter['value'];
            if (in_array($name, $allowedParameters) && $this->hasParameter($name) && strlen($value)) {
                $this->setParameter($name, $value);
            }
        }
    }

}
