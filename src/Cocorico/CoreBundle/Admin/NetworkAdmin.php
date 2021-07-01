<?php

namespace Cocorico\CoreBundle\Admin;

use Cocorico\CoreBundle\Entity\Network;
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

class NetworkAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'network';
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
        /** @var Network $network */
        $Network = $this->getSubject();

        $formMapper
            ->with('admin.network.title')
            ->add(
                'name',
                null,
                array(
                    'label' => 'Raison sociale',
                    'disabled' => false,
                )
            )
            ->add(
                'brand',
                null,
                array(
                    'label' => 'Enseigne',
                    'disabled' => false,
                )
            )            
            ->add(
                'accronym',
                null,
                array(
                    'label' => 'Sigle',
                    'disabled' => false,
                )
            )            
            ->add(
                'website',
                null,
                array(
                    'label' => 'Site Web',
                    'disabled' => false,
                )
            )            
            ->add(
                'siret',
                null,
                array(
                    'label' => 'Siret',
                    'disabled' => false,
                )
            );

        $formMapper->end();
    }

    /** @inheritdoc */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('siret')
            ->add('name')
            ->add('brand')
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
            ->add( 'name', null, array( 'label' => 'Nom',))
            ->add( 'brand', null, array( 'label' => 'Enseigne'))
            ->add( 'accronym', null, array( 'label' => 'Sigle'))
            ->add( 'siret', null, array( 'label' => 'siret'));

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    # 'view' => [
                    #     'template' => 'CocoricoSonataAdminBundle::network_action_list_user_view.html.twig',
                    # ],
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
        // $collection->remove('create');
        $collection->remove('delete');
    }

}
