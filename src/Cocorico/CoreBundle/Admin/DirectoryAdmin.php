<?php

namespace Cocorico\CoreBundle\Admin;

use Cocorico\CoreBundle\Entity\Directory;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Form\Type\PriceType;
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
            ->with('admin.quote.title')
            ->add(
                'name',
                null,
                array(
                    'label' => 'Nom',
                    'disabled' => true,
                )
            )
            ->add(
                'siret',
                null,
                array(
                    'label' => 'Siret',
                    'disabled' => true,
                )
            )
            ->add(
                'sector',
                null,
                array(
                    'label' => 'Secteurs',
                    'disabled' => true,
                )
            )
            ->add(
                'c1Id',
                null,
                array(
                    'label' => 'C1 Identifier',
                    'disabled' => true,
                )
            )
            ->add(
                'c4Id',
                null,
                array(
                    'label' => 'C4 Identifier',
                    'disabled' => true,
                )
            );


        if ($Directory->getC4Id()) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->modelManager->getEntityManager('CocoricoUserBundle:User')
                ->getRepository('CocoricoUserBundle:User');

            $user = $userRepository->getFindOneQueryBuilder($Directory->getC4Id());
            $formMapper->add(
                'user',
                'sonata_type_model',
                array(
                    'query' => $user,
                    'disabled' => true,
                    'label' => 'Utilisateur'
                )
            );
        }
        $formMapper->end();
    }

    /** @inheritdoc */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add(
                'updatedAt',
                'doctrine_orm_callback',
                array(
                    'label' => 'admin.quote.updated_at.label',
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        /** @var \DateTime $date */
                        $date = $value['value'];
                        if (!$date) {
                            return false;
                        }

                        $queryBuilder
                            ->andWhere("DATE_FORMAT($alias.updatedAt,'%Y-%m-%d') = :updatedAt")
                            ->setParameter('updatedAt', $date->format('Y-m-d'));

                        return true;
                    },
                    'field_type' => 'sonata_type_date_picker',
                    'field_options' => array('format' => 'dd/MM/yyyy'),
                ),
                null
            );

    }

    /** @inheritdoc */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'c1Id',
                null,
                array(
                    'label' => 'C1 Identifier',
                )
            )
            ->add(
                'c4Id',
                null,
                array(
                    'label' => 'C4 Identifier',
                )
            )
            ->add(
                'name',
                null,
                array(
                    'label' => 'Nom',
                )
            )
            ->add(
                'kind',
                null,
                array(
                    'label' => 'Type',
                )
            )
            ->add(
                'c1Source',
                null,
                array(
                    'label' => 'Source',
                )
            )
            ->add(
                'siret',
                null,
                array(
                    'label' => 'siret',
                )
            )
            ->add(
                'isDelisted',
                null,
                array(
                    'label' => 'Delisted'
                )
            );

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
