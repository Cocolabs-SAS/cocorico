<?php

namespace Cocorico\CoreBundle\Model\Manager;


use Cocorico\CoreBundle\Entity\Directory;
use Cocorico\CoreBundle\Repository\DirectoryRepository;
use Cocorico\CoreBundle\Entity\DirectoryListingCategory;
use Cocorico\CoreBundle\Entity\DirectoryImage;
use Cocorico\CoreBundle\Entity\DirectoryClientImage;
use Cocorico\CoreBundle\Model\DirectorySearchRequest;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\Query\ResultSetMapping;
use Exception;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Cocorico\CoreBundle\Utils\ZPaginator;
use Doctrine\ORM\Tools\Pagination\CountWalker;

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
        $qB = $this->getRepository()->getSome($perpage, (($page - 1) * $perpage), true);
        // Hack : by default to siege
        // $qB->andWhere('d.nature = :nature')
        //    ->setParameter('nature', 'siege');

        // Hack : force same return format as geo sort
        $qB->addSelect('1 AS distance');

        $query = $qB->getQuery();
        $query->setHint(CountWalker::HINT_DISTINCT, false);
        return new Paginator($query);
    }

    public function findByForm(DirectorySearchRequest $directorySearchRequest, $page, $params=[])
    {
        // FIXME: Remove params, use directory search request
        $perpage = $this->maxPerPage;
        $qB = $this->getRepository()->getSome($perpage, (($page - 1) * $perpage));


        if ($directorySearchRequest->getSearchType() == 'city') {
            $qB = $this->applyFilters($qB, $directorySearchRequest);
            $qB = $this->applyGeo($qB, $directorySearchRequest);
        } else if ($directorySearchRequest->getSearchType() == 'country') {
            // Can't use country code (FR, RE, YT, ...)
            $qB = $this->applyFilters($qB, $directorySearchRequest);

            if ($directorySearchRequest->getCountry() != null)
                {
                $qB = $this->applyCountryGeo($qB, $directorySearchRequest);
                }

            $qB->addSelect('1 AS distance');
        } else {
            $qB = $this->applyFilters($qB, $directorySearchRequest, true);
            #FIXME : Bad doctrine ORM hack
            $qB->addSelect('1 AS distance');
        }

        $query = $qB->getQuery();
        $query->setHydrationMode(Query::HYDRATE_OBJECT);
        $query->setHint(CountWalker::HINT_DISTINCT, false);
        return new Paginator($query);
    }

    public function findWithPerimeter($page, $params=[])
    {
        // FIXME: Make this work somehow ?
        // $conn = $this->em->getCononection();

        $rsm = new ResultSetMappingBuilder($this->em);
        $rsm->addRootEntityFromClassMetadata('Cocorico\CoreBundle\Entity\Directory', 'd');
        $rsm->addJoinedEntityFromClassMetadata(
                'Cocorico\CoreBundle\Entity\DirectoryListingCategory'
                , 'dlcat', 'd', 'directoryListingCategories', array('id'=>'dlcat_id'));
        # $rsm->addJoinedEntityFromClassMetadata(
        #         'Cocorico\CoreBundle\Entity\ListingCategory'
        #         , 'ca', 'd', 'directoryListingCategories', array('id'=>'ca_id'));

        $selectClause = $rsm->generateSelectClause(array(
            'd' => 'd',
            'dlcat' => 'dlcat',
            'ca' => 'ca',
        ));

        # -> get those of the same department if department
        # -> get those of the region if region
        # -> get those of the country if country
        # -> get those in correct distance

        $sql = "SELECT " . $selectClause ." FROM directory d
            LEFT JOIN directory_listing_category dlcat ON d.id = dlcat.directory_id
            LEFT JOIN listing_category ca on dlcat.listing_category_id = ca.id
            ORDER BY name ASC";

        $query = $this->em->createNativeQuery($sql, $rsm);

        // Paging
        // $perpage = $this->maxPerPage;
        // $offset = ($page * $perpage) - $perpage;
        // $query->setFirstResult($offset);
        // $query->setMaxResults($perpage);

        $paginator = new ZPaginator($query, $page, $this->maxPerPage );
        return $paginator;
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

    public function findBySiretn($siretn, $retry=true)
    {
        $siretn = preg_replace('/\D+/', '', $siretn);

        if (strlen($siretn) < 1) {
            return [];
        }

        $qB = $this->getRepository()->getFindBySiretSiren($siretn);
        $query = $qB->getQuery();
        $resp =  $query->getResult();
        if ($resp) {
            return $resp;
        }

        # Note : Retrying search with siret only, because
        # some users have bad siret information
        if ($retry and strlen($siretn > 9)) {
            return $this->findBySiretn(substr($siretn, 0, 9), false);
        }

        return [];
    }

    public function findByOwner($User, $page)
    {
        $qB = $this->getRepository()->getFindByUser($User);

        //Pagination
        $qB->setFirstResult(($page - 1) * $this->maxPerPage)
           ->setMaxResults($this->maxPerPage);

        //Query
        $query = $qB->getQuery();

        return new Paginator($query);
    }


    public function listByForm(directorySearchRequest $req, $params=[])
    {
        $qB = $this->getRepository()->getAll();

        if ($req->getSearchType() == 'city') {
            $qB = $this->applyFilters($qB, $req);
            $qB = $this->applyGeo($qB, $req);
        } else {
            $qB = $this->applyFilters($qB, $req, true);
            #FIXME : Bad doctrine ORM hack
            $qB->addSelect('1 AS distance');
            $qB->orderBy('d.name', 'ASC');
        }
        $query = $qB->getQuery();
        return $query->getResult();
    }

    private function applyFilters($qB, $req, $geo=False) {
        // Filter on type
        if ($req->getStructureType() != null) {
            $kindName = Directory::$kindValues[$req->getStructureType()];
            $qB->andWhere('d.kind = :type')
               ->setParameter('type', $kindName);
        }

        // Filter on prestation type
        if ($req->getPrestaType() > 1) {
            $qB->andWhere('BIT_AND(d.prestaType, :prestatype) > 0')
               ->setParameter('prestatype', $req->getPrestaType());
        }

        // Filter on sector
        if (count($req->getSectors()) > 0) {
            $qB->andWhere('dlcat.category IN (:sectors)')
               ->setParameter('sectors', $req->getSectors());
        }

        // Include antennas
        if ($req->getWithAntenna() == false) {
            $qB->andWhere('d.nature = \'siege\'');
        }

        if ($geo) {
            // Filter on sector
            if ($req->getSearchType() == 'region') {
                $region = $req->getRegion();
                $regionName = Directory::$regions[$region];
                $qB->andWhere('d.region = :region')
                   ->setParameter('region', $regionName);
            }
            // Else Filter on postal code
            else if ($req->getPostalCode() != false) {
                $qB->andWhere('d.postCode like :pcode')
                   ->setParameter('pcode', addcslashes($req->getPostalCode(), '%_').'%');
            }

        }

        return $qB;
    }

    private function applyGeo($qB, $request) {
        $qB->addSelect('GEO_DISTANCE(d.latitude = :lat, d.longitude = :lng) AS distance')
           ->setParameter('lat', $request->getLat())
           ->setParameter('lng', $request->getLng());

        $qB //->where('distance < (case when l.polRange = 2 then 100 when l.polRange = 2 then 400 when l.polRange = 3 then 1000 else l.range end)');
            ->andwhere('GEO_DISTANCE(d.latitude = :lat, d.longitude = :lng) < (
                case
                    when d.polRange = 1 then 125
                    when d.polRange = 2 then 450
                    when d.polRange = 3 then 3000
                    else d.range 
                end)');

        $qB->orderBy("distance", "ASC");
        return $qB;
    }

    private function applyCountryGeo($qB, $request) {
        // $qB->addSelect('GEO_DISTANCE(d.latitude = :lat, d.longitude = :lng) AS distance')
        //    ->setParameter('lat', $request->getLat())
        //    ->setParameter('lng', $request->getLng());

        $qB->andwhere('GEO_DISTANCE(d.latitude = :lat, d.longitude = :lng) < 2000')
           ->setParameter('lat', $request->getLat())
           ->setParameter('lng', $request->getLng());
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
        // foreach ($directory->getLabels() as $label) {
        //     $label->setDirectory($directory);
        // }

        $directory->prestaTypeToInt();
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

    /**
     * Create categories and field values while directory adopted.
     *
     * @param  Directory $directory
     * @param  array   $categories Id(s) of ListingCategory(s) selected
     * @param  array   $values     Value(s) of ListingCategoryFieldValue(s) of the ListingCategory(s) selected
     *
     * @return Directory
     */
    public function addCategories(Directory $directory, array $categories, array $values)
    {
        foreach ($categories as $i => $category) {
            $listingCategory = $this->em->getRepository('CocoricoCoreBundle:ListingCategory')->findOneById(
                $category
            );

            //Create the corresponding ListingListingCategory
            $directoryListingCategory = new DirectoryListingCategory();
            $directoryListingCategory->setDirectory($directory);
            $directoryListingCategory->setCategory($listingCategory);

            $directory->addDirectoryListingCategory($directoryListingCategory);
        }

        return $directory;
    }


    /**
     * @param  Directory $directory
     * @param  array   $images
     * @param bool     $persist
     * @return Directory
     * @throws AccessDeniedException
     */
    public function addImages(Directory $directory, array $images, $persist = false)
    {
        //@todo : see why user is anonymous and not authenticated
        if (true || $directory && $directory->getUser() == $this->securityTokenStorage->getToken()->getUser()) {
            //Start new positions value
            $nbImages = $directory->getImages()->count();

            foreach ($images as $i => $image) {
                $directoryImage = new DirectoryImage();
                $directoryImage->setDirectory($directory);
                $directoryImage->setName($image);
                $directoryImage->setPosition($nbImages + $i + 1);
                $directory->addImage($directoryImage);
            }

            if ($persist) {
                $this->em->persist($directory);
                $this->em->flush();
                $this->em->refresh($directory);
            }

        } else {
            throw new AccessDeniedException();
        }

        return $directory;
    }
    /**
     * @param  Directory $directory
     * @param  array   $clientImages
     * @param bool     $persist
     * @return Directory
     * @throws AccessDeniedException
     */
    public function addClientImages(Directory $directory, array $clientImages, $persist = false)
    {
        //@todo : see why user is anonymous and not authenticated
        if (true || $directory && $directory->getUser() == $this->securityTokenStorage->getToken()->getUser()) {
            //Start new positions value
            $nbImages = $directory->getClientImages()->count();

            foreach ($clientImages as $i => $image) {
                $directoryImage = new DirectoryClientImage();
                $directoryImage->setDirectory($directory);
                $directoryImage->setName($image);
                $directoryImage->setPosition($nbImages + $i + 1);
                $directory->addClientImage($directoryImage);
            }

            if ($persist) {
                $this->em->persist($directory);
                $this->em->flush();
                $this->em->refresh($directory);
            }

        } else {
            throw new AccessDeniedException();
        }

        return $directory;
    }





}
?>
