<?php
namespace Cocorico\CoreBundle\Utils;

use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Paginator\Paginator;
use Zend\Paginator\ScrollingStyle\Sliding;


/**
 * Paginate native doctrine 2 queries 
 */
class ZPaginator implements AdapterInterface
{
    /**
     * @var Doctrine\ORM\NativeQuery
     */
    protected $query;
    protected $count;
    protected $limit;
    protected $offset;
    protected $results;

    /**
     * @param Doctrine\ORM\NativeQuery $query 
     */
    public function __construct($query, $page=0, $limit=10)
    {
        $this->query = $query;
        $this->limit = $limit;
        $this->offset = $page * $limit;
    }

    /**
     * Returns the total number of rows in the result set.
     *
     * @return integer
     */
    public function count()
    {
        if(!$this->count)
        {
            $this->count = count($this->getAllItems());
        }

        return $this->count;
    }

    /**
     * Returns an collection of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $cloneQuery = clone $this->query;
        $cloneQuery->setParameters($this->query->getParameters());

        foreach($this->query->getHints() as $name => $value)
        {
            $cloneQuery->setHint($name, $value);
        }

        //add on limit and offset
        $sql = $cloneQuery->getSQL();
        $sql .= " LIMIT $itemCountPerPage OFFSET $offset";
        $cloneQuery->setSQL($sql);

        return $cloneQuery->getResult();
    }

    /**
     * Returns an collection of items for a page.
     *
     * @return array
     */
    public function getPageItems()
    {
        $results = $this->getAllItems();
        return array_slice($results, $this->offset, $this->limit);
    }

    private function getAllItems()
    {
        if(!$this->results) {
            $this->results = $this->query->getResult();
        }
        return $this->results;
    }

}
