<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Frontend;

use Cocorico\CoreBundle\Event\ListingSearchFormBuilderEvent;
use Cocorico\CoreBundle\Event\ListingSearchFormEvents;
use Cocorico\CoreBundle\Form\Type\PriceRangeType;
use Cocorico\CoreBundle\Model\ListingSearchRequest;
use Cocorico\CoreBundle\Repository\ListingCategoryRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListingSearchResultType extends AbstractType
{

    protected $session;
    protected $currency;
    protected $entityManager;
    protected $request;
    protected $locale;
    protected $timeUnit;
    protected $timeUnitFlexibility;
    protected $timeUnitIsDay;
    protected $allowSingleDay;
    protected $endDayIncluded;
    protected $daysDisplayMode;
    protected $timesDisplayMode;
    protected $minStartDelay;
    protected $dispatcher;

    /**
     * @param Session                  $session
     * @param string                   $defaultCurrency
     * @param EntityManager            $entityManager
     * @param RequestStack             $requestStack
     * @param int                      $timeUnit
     * @param boolean                  $timeUnitFlexibility
     * @param boolean                  $allowSingleDay
     * @param boolean                  $endDayIncluded
     * @param string                   $daysDisplayMode
     * @param string                   $timesDisplayMode
     * @param int                      $minStartDelay
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        Session $session,
        $defaultCurrency,
        EntityManager $entityManager,
        RequestStack $requestStack,
        $timeUnit,
        $timeUnitFlexibility,
        $allowSingleDay,
        $endDayIncluded,
        $daysDisplayMode,
        $timesDisplayMode,
        $minStartDelay,
        EventDispatcherInterface $dispatcher
    ) {
        $this->session = $session;
        $this->currency = $this->session->has('currency') ? $this->session->get('currency') : $defaultCurrency;
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->locale = $this->request->getLocale();
        $this->timeUnit = $timeUnit;
        $this->timeUnitFlexibility = $timeUnitFlexibility;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
        $this->allowSingleDay = $allowSingleDay;
        $this->endDayIncluded = $endDayIncluded;
        $this->daysDisplayMode = $daysDisplayMode;
        $this->timesDisplayMode = $timesDisplayMode;
        $this->minStartDelay = $minStartDelay;
        $this->dispatcher = $dispatcher;;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $builder->getData();

        $builder
            ->add(
                'location',
                new ListingLocationSearchType()
            );

        //CATEGORIES
        /** @var ListingCategoryRepository $categoryRepository */
        $categoryRepository = $this->entityManager->getRepository("CocoricoCoreBundle:ListingCategory");
        $categories = $categoryRepository->findCategoriesByIds(
            $listingSearchRequest->getCategories(),
            $this->locale
        );

        $builder
            ->add(
                'categories',
                'listing_category',
                array(
                    'label' => 'listing_search.form.categories',
                    'mapped' => false,
                    'data' => $categories,
                    'block_name' => 'listing_categories',
                    'multiple' => true,
                    'empty_value' => 'listing_search.form.categories.empty_value',
                )
            );

        //DATE RANGE
        $dateRange = $listingSearchRequest->getDateRange();
        $dateRangeStart = $dateRangeEnd = null;
        if ($dateRange && $dateRange->getStart() && $dateRange->getEnd()) {
            $dateRangeStart = $dateRange->getStart();
            $dateRangeEnd = $dateRange->getEnd();
        }

        $builder
            ->add(
                'date_range',
                'date_range',
                array(
                    'start_options' => array(
                        'label' => 'listing_search.form.start',
                        'data' => $dateRangeStart
                    ),
                    'end_options' => array(
                        'label' => 'listing_search.form.end',
                        'data' => $dateRangeEnd
                    ),
                    'allow_single_day' => $this->allowSingleDay,
                    'end_day_included' => $this->endDayIncluded,
                    'required' => false,
                    /** @Ignore */
                    'label' => false,
                    'block_name' => 'date_range',
                    'display_mode' => $this->daysDisplayMode,
                    'min_start_delay' => $this->minStartDelay
                )
            )
            ->add(
                'price_range',
                new PriceRangeType($this->currency),
                array(
                    /** @Ignore */
                    'label' => false
                )
            );

        //CHARACTERISTICS
        $characteristics = $listingSearchRequest->getCharacteristics();
        $builder
            ->add(
                'characteristics',
                'listing_characteristic',
                array(
                    'mapped' => false,
                    'data' => $characteristics
                )
            )
            ->add(
                'sort_by',
                'choice',
                array(
                    'choices' => array_flip(ListingSearchRequest::$sortByValues),
                    'empty_value' => 'listing_search.form.sort_by.empty_value',
                    'choices_as_values' => true
                )
            )
            ->add(
                'page',
                'hidden'
            );

        //If time unit is not day then we add time in search engine
        if (!$this->timeUnitIsDay) {
            $timeRange = $listingSearchRequest->getTimeRange();
            $timeRangeStart = $timeRangeEnd = null;
            if ($timeRange && $timeRange->getStart() && $timeRange->getEnd()) {
                $timeRangeStart = $timeRange->getStart();
                $timeRangeEnd = $timeRange->getEnd();
            }

            $builder->add(
                'time_range',
                'time_range',
                array(
                    'start_options' => array(
                        'label' => 'listing_search.form.start_time',
                        'data' => $timeRangeStart
                    ),
                    'end_options' => array(
                        'label' => 'listing_search.form.end_time',
                        'data' => $timeRangeEnd
                    ),
                    'required' => false,
                    /** @Ignore */
                    'label' => false,
                    'block_name' => 'time_range',
                    'display_mode' => $this->timesDisplayMode
                )
            );
        }

        //If there is time flexibility
        if ($this->timeUnitFlexibility) {
            $builder->add(
                'flexibility',
                'choice',
                array(
                    'label' => 'listing_search.form.flexibility',
                    'empty_value' => 'listing_search.form.flexibility',
                    'choices' => array_combine(
                        range(1, $this->timeUnitFlexibility),
                        range(1, $this->timeUnitFlexibility)
                    ),
                    'required' => false,
                    'choices_as_values' => true
                )
            );
        }

        //Dispatch LISTING_SEARCH_RESULT_FORM_BUILD Event. Listener listening this event can add fields and validation
        //Used for example to add fields to listing search form
        $this->dispatcher->dispatch(
            ListingSearchFormEvents::LISTING_SEARCH_RESULT_FORM_BUILD,
            new ListingSearchFormBuilderEvent($builder, $listingSearchRequest)
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'data_class' => 'Cocorico\CoreBundle\Model\ListingSearchRequest',
                'translation_domain' => 'cocorico_listing',
            )
        );
    }

    /**
     * BC
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_search_result';
    }
}
