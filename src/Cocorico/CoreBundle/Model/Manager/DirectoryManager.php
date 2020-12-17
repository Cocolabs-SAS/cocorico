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

    public function findByForm($page, $params=[])
    {
        $perpage = $this->maxPerPage;
        $qB = $this->getRepository()->getSome($perpage, (($page - 1) * $perpage));

        $qB = $this->applyParams($qB, $params);

        $query = $qB->getQuery();
        return new Paginator($query);
    }

    public function listByForm($params=[])
    {
        $qB = $this->getRepository()->getAll()->setMaxResults(10);
        $qB = $this->applyParams($qB, $params);
        $query = $qB->getQuery();
        return $query->getResult(Query::HYDRATE_ARRAY);
    }

    private function applyParams($qB, $params)
    {
        // Filter on type
        if (in_array('type', $params) and $params['type'] != false) {
            $value = $params['type'];
            $kindName = Directory::$kindValues[$value];
            $qB->andWhere('d.kind = :type')
               ->setParameter('type', $kindName);
        }

        // Filter on sector
        if (in_array('sector', $params) and $params['sector'] != false) {
            $sector = $params['sector'];
            $sectorName = Directory::$sectorValues[$sector];
            $qB->andWhere('d.sector like :sector')
               ->setParameter('sector', '%'.$sectorName.'%');
        }

        // Filter on postal code
        if (in_array('postalCode', $params) and $params['postalCode'] != false) {
            $value = $params['postalCode'];
            $qB->andWhere('d.postCode like :pcode')
               ->setParameter('pcode', addcslashes($value, '%_').'%');
        }

        // Filter on prestation type
        if (in_array('prestaType', $params) and $params['prestaType'] != false) {
            $value = $params['prestaType'];
            $prestaName = Directory::$prestaTypeValues[$value];
            $qB->andWhere('d.prestaType = :prestatype')
               ->setParameter('prestatype', $prestaName);
        
        }
        return $qB;
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
