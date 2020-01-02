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
use Cocorico\CoreBundle\Form\Type\ListingCategoryType;
use Cocorico\CoreBundle\Form\Type\ListingCharacteristicType;
use Cocorico\CoreBundle\Form\Type\PriceRangeType;
use Cocorico\CoreBundle\Model\ListingSearchRequest;
use Cocorico\CoreBundle\Repository\ListingCategoryRepository;
use Cocorico\TimeBundle\Form\Type\DateRangeType;
use Cocorico\TimeBundle\Form\Type\TimeRangeType;
use Cocorico\TimeBundle\Model\DateRange;
use Cocorico\TimeBundle\Model\TimeRange;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ListingSearchResultType extends AbstractType
{
    protected $entityManager;
    protected $request;
    protected $dispatcher;
    protected $currency;
    protected $locale;
    protected $timeUnitIsDay;
    protected $timeUnitFlexibility;
    protected $daysDisplayMode;
    protected $timesDisplayMode;
    protected $allowSingleDay;
    protected $endDayIncluded;
    protected $minStartTimeDelay;

    /**
     * @param EntityManager            $entityManager
     * @param RequestStack             $requestStack
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $parameters
     */
    public function __construct(
        EntityManager $entityManager,
        RequestStack $requestStack,
        EventDispatcherInterface $dispatcher,
        $parameters
    ) {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->dispatcher = $dispatcher;

        $this->locale = $this->request->getLocale();

        $parameters = $parameters["parameters"];
        $this->timeUnitIsDay = ($parameters['cocorico_time_unit'] % 1440 == 0) ? true : false;
        $this->timeUnitFlexibility = $parameters['cocorico_time_unit_flexibility'];
        $this->daysDisplayMode = $parameters['cocorico_days_display_mode'];
        $this->timesDisplayMode = $parameters['cocorico_times_display_mode'];
        $this->allowSingleDay = $parameters['cocorico_booking_allow_single_day'];
        $this->endDayIncluded = $parameters['cocorico_booking_end_day_included'];
        $this->minStartTimeDelay = $parameters['cocorico_booking_min_start_time_delay'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $builder->getData();

        $builder
            ->add('location', ListingLocationSearchType::class);

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
                ListingCategoryType::class,
                array(
                    'label' => 'listing_search.form.categories',
                    'mapped' => false,
                    'data' => $categories,
                    'block_name' => 'listing_categories',
                    'multiple' => true,
                    'placeholder' => 'listing_search.form.categories.empty_value',
                )
            );

        //DATE RANGE
        $dateRange = $listingSearchRequest->getDateRange();
        $builder
            ->add(
                'date_range',
                DateRangeType::class,
                array(
                    'start_options' => array(
                        'label' => 'listing_search.form.start',
                        'data' => $dateRange && $dateRange->getStart() ? $dateRange->getStart() : null
                    ),
                    'end_options' => array(
                        'label' => 'listing_search.form.end',
                        'data' => $dateRange && $dateRange->getEnd() ? $dateRange->getEnd() : null
                    ),
                    'allow_single_day' => $this->allowSingleDay,
                    'end_day_included' => $this->endDayIncluded,
                    'required' => false,
                    /** @Ignore */
                    'label' => false,
                    'block_name' => 'date_range',
                    'display_mode' => $this->daysDisplayMode,
                )
            )
            ->add(
                'price_range',
                PriceRangeType::class,
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
                ListingCharacteristicType::class,
                array(
                    'mapped' => false,
                    'data' => $characteristics
                )
            )
            ->add(
                'sort_by',
                ChoiceType::class,
                array(
                    'choices' => array_flip(ListingSearchRequest::$sortByValues),
                    'placeholder' => 'listing_search.form.sort_by.empty_value',
                )
            )
            ->add(
                'page',
                HiddenType::class
            );

        //If time unit is not day then we add time in search engine
        if (!$this->timeUnitIsDay) {
            $timeRange = $listingSearchRequest->getTimeRange();

            $builder->add(
                'time_range',
                TimeRangeType::class,
                array(
                    'start_options' => array(
                        'label' => 'listing_search.form.start_time',
                        'data' => $timeRange && $timeRange->getStart() ? $timeRange->getStart() : null
                    ),
                    'end_options' => array(
                        'label' => 'listing_search.form.end_time',
                        'data' => $timeRange && $timeRange->getEnd() ? $timeRange->getEnd() : null
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
                ChoiceType::class,
                array(
                    'label' => 'listing_search.form.flexibility',
                    'placeholder' => 'listing_search.form.flexibility',
                    'choices' => array_combine(
                        range(1, $this->timeUnitFlexibility),
                        range(1, $this->timeUnitFlexibility)
                    ),
                    'required' => false,
                )
            );
        }

        //Sync date and time
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var ListingSearchRequest $searchRequest */
                $searchRequest = $event->getData();
                $form = $event->getForm();
                /** @var DateRange $dateRange */
                $dateRange = $form->get('date_range')->getData();
                $searchRequest->setDateRange($dateRange);

                if (!$this->timeUnitIsDay) {
                    /** @var TimeRange $timeRange */
                    $timeRange = $form->get('time_range')->getData();
                    $searchRequest->setTimeRange($timeRange);
                }
                $event->setData($searchRequest);
            }
        );

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
                'constraints' => array(
                    new Callback(array('callback' => array($this, 'checkDate'))),
                ),
            )
        );
    }

    /**
     * Check if search date is correct according to minStartTimeDelay
     *
     * @param ListingSearchRequest      $listingSearchRequest
     * @param ExecutionContextInterface $context
     */
    public function checkDate($listingSearchRequest, ExecutionContextInterface $context)
    {
        $minStartTime = new DateTime();
        $minStartTime->add(new DateInterval('PT'.$this->minStartTimeDelay.'M'));

        $start = false;
        if ($this->timeUnitIsDay) {
            $dateRange = $listingSearchRequest->getDateRange();
            if ($dateRange && $dateRange->getStart()) {
                $start = $listingSearchRequest->getDateRange()->getStart();
            }
        } else {
            $timeRange = $listingSearchRequest->getTimeRange();
            if ($timeRange && $timeRange->getStart()) {
                $start = $listingSearchRequest->getTimeRange()->getStart();
            }
        }

        if ($start && $start->format('Ymd H:i') < $minStartTime->format('Ymd H:i')) {
            $minStartTime->setTimezone(new DateTimeZone($this->request->getSession()->get('timezone')));
            $context->buildViolation('time_range.invalid.min_start {{ min_start_time }}')
                ->atPath('date_range')
                ->setParameters(array('{{ min_start_time }}' => $minStartTime->format('d/m/Y H:i')))
                ->setTranslationDomain('cocorico')
                ->addViolation();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_search_result';
    }
}
