<?php

namespace Cocorico\CoreBundle\Model\Manager;


use Cocorico\CoreBundle\Entity\Directory;
use Cocorico\CoreBundle\Repository\DirectoryRepository;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DirectoryManager extends BaseManager
{
    protected $em;
    protected $dispatcher;
    public $maxPerPage;

    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $dispatcher,
        $parameters
    ) {
        //Parameters
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $parameters = $parameters["parameters"];
        $this->maxPerPage = $parameters["directory_max_per_page"];
    }


    public function listSome($page)
    {
        // Fixme: use max_per_page
        $perpage = $this->maxPerPage;
        $qB = $this->getRepository()->getSome($perpage, (($page - 1) * $perpage));
        $query = $qB->getQuery();
        return new Paginator($query);
    }

    public function findByForm($page, $type=False)
    {
        $perpage = $this->maxPerPage;
        $qB = $this->getRepository()->getSome($perpage, (($page - 1) * $perpage));
        if ($type) {
            $qB
                ->where('d.kind = :type')
                ->setParameter('type', $type);
        }
        $query = $qB->getQuery();
        return new Paginator($query);
    }

    public function listByForm($type=False)
    {
        $qB = $this->getRepository()->getAll()->setMaxResults(10);
        if ($type) {
            $qB
                ->where('d.kind = :type')
                ->setParameter('type', $type);
        }
        $query = $qB->getQuery();
        return $query->getResult(Query::HYDRATE_ARRAY);
    }

    public function listColumns()
    {
        return $this->em->getClassMetaData(Directory::class)->getColumnNames();
    }

    /**
     *
     * @return DirectoryRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('CocoricoCoreBundle:Directory');
    }



}
?>
