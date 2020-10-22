<?php

namespace Cocorico\CoreBundle\Admin;

use Cocorico\CoreBundle\Entity\Quote;
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

class QuoteAdmin extends AbstractAdmin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'quote';
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
        /** @var Quote $quote */
        $Quote = $this->getSubject();

        $askerQuery = $offererQuery = $listingQuery = null;
        if ($Quote) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->modelManager->getEntityManager('CocoricoUserBundle:User')
                ->getRepository('CocoricoUserBundle:User');

            $askerQuery = $userRepository->getFindOneQueryBuilder($Quote->getUser()->getId());
            $offererQuery = $userRepository->getFindOneQueryBuilder($Quote->getListing()->getUser()->getId());

            /** @var ListingRepository $listingRepository */
            $listingRepository = $this->modelManager->getEntityManager('CocoricoCoreBundle:Listing')
                ->getRepository('CocoricoCoreBundle:Listing');

            $listingQuery = $listingRepository->getFindOneByIdAndLocaleQuery(
                $Quote->getListing()->getId(),
                $this->request ? $this->getRequest()->getLocale() : 'fr'
            );
        }

        $formMapper
            ->with('admin.quote.title')
            ->add(
                'user',
                'sonata_type_model',
                array(
                    'query' => $askerQuery,
                    'disabled' => true,
                    'label' => 'admin.quote.asker.label'
                )
            )
            ->add(
                'listing.user',
                'sonata_type_model',
                array(
                    'query' => $offererQuery,
                    'disabled' => true,
                    'label' => 'admin.quote.offerer.label',
                )
            )
            ->add(
                'listing',
                'sonata_type_model',
                array(
                    'query' => $listingQuery,
                    'disabled' => true,
                    'label' => 'admin.listing.label',
                )
            )
            ->add(
                'status',
                ChoiceType::class,
                array(
                    'choices' => array_flip(Quote::$statusValues),
                    'placeholder' => 'admin.quote.status.label',
                    'disabled' => true,
                    'label' => 'admin.quote.status.label',
                    'translation_domain' => 'cocorico_quote',
                )
            )
            ->add(
                'validated',
                null,
                array(
                    'label' => 'admin.quote.validated.label',
                    'disabled' => true,
                )
            );

        $formMapper->end();
    }

    /** @inheritdoc */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add(
                'status',
                'doctrine_orm_string',
                array(),
                ChoiceType::class,
                array(
                    'choices' => array_flip(Quote::$statusValues),
                    'label' => 'admin.quote.status.label',
                    'translation_domain' => 'cocorico_quote',
                )
            )
            ->add(
                'listing.id',
                null,
                array('label' => 'admin.quote.listing_id.label')
            )
            ->add(
                'listing.translations.title',
                'doctrine_orm_string',
                array('label' => 'admin.quote.listing_title.label')
            )
            ->add(
                'user.email',//todo: search by first name and last name
                null,
                array('label' => 'admin.quote.asker.label')
            )
            ->add(
                'listing.user.email',//todo: search by first name and last name
                null,
                array('label' => 'admin.quote.offerer.label')
            )
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
                'listing.id',
                null,
                array(
                    'label' => 'admin.quote.listing_id.label'
                )
            )
            ->add(
                'statusText',
                null,
                array(
                    'label' => 'admin.quote.status.label',
                    'template' => 'CocoricoSonataAdminBundle::list_field_value_translated.html.twig',
                    'data_trans' => 'cocorico_quote'
                )
            )
            ->add(
                'validated',
                null,
                array(
                    'label' => 'admin.quote.validated.label',
                )
            )
            ->add(
                'user',
                null,
                array(
                    'label' => 'admin.quote.asker.label',
                )
            )
            ->add(
                'listing.user',
                null,
                array(
                    'label' => 'admin.quote.offerer.label',
                )
            )
            ->add(
                'listing',
                null,
                array(
                    'label' => 'admin.listing.label'
                )
            );

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
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
