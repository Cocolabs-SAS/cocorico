<?php

namespace Cocorico\CoreBundle\Admin;

use Cocorico\CoreBundle\Entity\Directory;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Repository\ListingRepository;
use Cocorico\UserBundle\Repository\UserRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;


class DirectoryAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'directory';
    protected $locales;
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $bundles;
    protected $timezone;

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    public function setTimeUnit($timeUnit)
    {
        $this->timeUnit = $timeUnit;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
    }

    public function setBundlesEnabled($bundles)
    {
        $this->bundles = $bundles;
    }

    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /** @inheritdoc */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Directory $quote */
        $Directory = $this->getSubject();

        $askerQuery = $offererQuery = $listingQuery = null;

        $formMapper
            ->with('Données Structure SIAE')
            ->add('isDelisted', null, [ 'label' => 'Masqué' ])
            ->add('isActive', null, [ 'label' => 'Actif' ])
            ->add('isFirstPage', null, [ 'label' => 'A la une' ])
            ->add( 'name', null, [ 'label' => 'Raison sociale', 'disabled' => true])
            ->add( 'brand', null, ['label' => 'Enseigne', 'disabled' => true])
            ->add( 'siret', null, ['label' => 'Siret', 'disabled' => true])
            ->add( 'naf', null, ['label' => 'Naf', 'disabled' => true])
            ->add( 'nature', null, [ 'label' => 'Type', 'disabled' => true])
            ->add( 'c1Id', null, ['label' => 'Identifiant C1', 'disabled' => true])
            ->add( 'city', null, [ 'label' => 'Ville', 'disabled' => true])
            ->add( 'postCode', null, [ 'label' => 'Code postal', 'disabled' => true])
            //->add( 'latitude', null, [ 'label' => 'Latitude', 'disabled' => true])
            //->add( 'longitude', null, [ 'label' => 'Longitude', 'disabled' => true])
            ->add('employees', null, [ 'label' => 'Employés' ])
            ->add('range', null, [ 'label' => 'Périmètre Geographique' ])
            ->add('polRange', null, [ 'label' => 'Périmètre Politique' ])
            ->add('description', null, [ 'label' => 'Description' ])
            ->add('users', null, [ 'label' => 'Gestionnaires' ])
            ->add('labels', null, [ 'label' => 'Labels' ])
            ->add('offers', null, [ 'label' => 'Offres' ])
            ->add('networks', null, [ 'label' => 'Réseaux' ])
            ;


        /*
         * TODO: Legacy code, scrap on next iteration
         */
        if ($Directory->getC4Id()) {
            $userRepository = $this->modelManager->getEntityManager('CocoricoUserBundle:User')
                ->getRepository('CocoricoUserBundle:User');

            $userQuery = $userRepository->getFindOneQueryBuilder($Directory->getC4Id());

            $formMapper->add('c4Id', null, ['label'=> 'User C4 Synchro', 'disabled'=>true]);

            /*
            $formMapper->add(
                'user',
                'sonata_type_model',
                array(
                    'query' => $userQuery,
                    'disabled' => true,
                    'label' => 'Utilisateur'
                )
            );
             */
        }
        /*
        */
        $formMapper->end();
    }

    /** @inheritdoc */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('siret')
            ->add('name', null, ['label'=>'raison sociale'])
            ->add('kind', 'doctrine_orm_string', ['label'=>'type'], 'choice', ['choices'=> array_flip(Directory::$kindFullString)])
            ->add('isDelisted', null, ['label'=> 'Masqué'])
            ->add('isActive', null, ['label'=> 'Actif'])
            ->add('isFirstPage', null, [ 'label' => 'A la une'])
            //->add(
            //    'updatedAt',
            //    'doctrine_orm_callback',
            //    array(
            //        'label' => 'admin.quote.updated_at.label',
            //        'callback' => function ($queryBuilder, $alias, $field, $value) {
            //            /** @var \DateTime $date */
            //            $date = $value['value'];
            //            if (!$date) {
            //                return false;
            //            }

            //            $queryBuilder
            //                ->andWhere("DATE_FORMAT($alias.updatedAt,'%Y-%m-%d') = :updatedAt")
            //                ->setParameter('updatedAt', $date->format('Y-m-d'));

            //            return true;
            //        },
            //        'field_type' => 'sonata_type_date_picker',
            //        'field_options' => array('format' => 'dd/MM/yyyy'),
            //    ),
            //    null
            //)
            ;

    }

    /** @inheritdoc */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add( 'name', null, [ 'label' => 'Raison sociale'])
            ->add( 'siret', null, [ 'label' => 'Siret'])
            ->add( 'kind', null, [ 'label' => 'Type'])
            ->add( 'isActive', null, [ 'label' => 'Actif'])
            ->add( 'isDelisted', null, [ 'label' => 'Masqué'])
            ->add( 'users', null, [ 'label' => 'Gestionnaires'])
        ;

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'view' => [
                        'template' => 'CocoricoSonataAdminBundle::directory_action_list_user_view.html.twig',
                    ],
                    'edit' => array(),
                )
            )
        );
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions["delete"]);

        return $actions;
    }

    public function getExportFields()
    {
        return array(
            'Id' => 'id',
            'Listing Id' => 'listing.id',
            'Status' => 'statusText',
            'Validated' => 'validated',
            'Asker' => 'user.fullname',
            'Offerer' => 'listing.user.fullname',
            'Listing' => 'listing.title',
        );
    }

    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        $dataSourceIt = $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
        $dataSourceIt->setDateTimeFormat('d M Y');

        return $dataSourceIt;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
    }
}
