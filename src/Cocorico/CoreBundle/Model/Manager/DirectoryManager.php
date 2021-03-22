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
        // Hack : by default to siege
        $qB->andWhere('d.nature = :nature')
           ->setParameter('nature', 'siege');

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

    public function findByUserId($c4Id)
    {
        $qB = $this->getRepository()->getFindByC4Id($C4Id);
        $query = $qB->getQuery();
        $resp =  $query->getResult();
        if ($resp){
            return $resp[0];
        } else {
            return False;
        }
    }

    public function findByOwner($UserId, $page)
    {
        $qB = $this->getRepository()->getFindByUserId($UserId);

        //Pagination
        $qB->setFirstResult(($page - 1) * $this->maxPerPage)
           ->setMaxResults($this->maxPerPage);

        //Query
        $query = $qB->getQuery();

        return new Paginator($query);
    }


    public function listByForm($params=[])
    {
        $qB = $this->getRepository()->getAll();
        $qB = $this->applyParams($qB, $params);
        $query = $qB->getQuery();
        return $query->getResult();
    }

    private function applyParams($qB, $params)
    {
        // Filter on type
        if ($params['type'] != false) {
            $value = $params['type'];
            $kindName = Directory::$kindValues[$value];
            $qB->andWhere('d.kind = :type')
               ->setParameter('type', $kindName);
        }

        // Filter on postal code
        if ($params['postalCode'] != false) {
            $value = $params['postalCode'];
            $qB->andWhere('d.postCode like :pcode')
               ->setParameter('pcode', addcslashes($value, '%_').'%');
        }

        // Filter on prestation type
        if ($params['prestaType'] != false) {
            $value = $params['prestaType'];
            $prestaName = Directory::$prestaTypeValues[$value];
            $qB->andWhere('d.prestaType = :prestatype')
               ->setParameter('prestatype', $prestaName);
        }

        // Filter on sector
        if ($params['sector'] != false) {
            $sector = $params['sector'];
            $sectorName = Directory::$sectorValues[$sector];
            $qB->andWhere('d.sector like :sector')
               ->setParameter('sector', '%'.$sectorName.'%');
        }

        // Filter on sector
        if ($params['region'] != false) {
            $region = $params['region'];
            $regionName = Directory::$regions[$region];
            $qB->andWhere('d.region = :region')
               ->setParameter('region', $regionName);
        }

        // Include antennas
        if ($params['withAntenna'] == false) {
            $qB->andWhere('d.nature = :nature')
               ->setParameter('nature', 'siege');
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


    /**
     * @param  Directory $directory
     * @return Directory
     */
    public function save(Directory $directory)
    {
        $this->persistAndFlush($directory);

        // This is a hack, leave it !

        /** @var DirectoryOptionInterface $option */
        // if ($directory->getOptions()) {
        //     foreach ($directory->getOptions() as $option) {
        //         $option->mergeNewTranslations();
        //         $this->persistAndFlush($option);
        //     }
        // }

        $this->em->flush();
        $this->em->refresh($directory);

        return $directory;
    }




}
?>
