<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\TimeBundle\Form\Type;

use Cocorico\TimeBundle\Form\DataTransformer\DateRangeViewTransformer;
use Cocorico\TimeBundle\Model\DateRange;
use Cocorico\TimeBundle\Validator\DateRangeValidator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;


class DateRangeType extends AbstractType
{
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $daysMax;

    /**
     * @param int $timeUnit in minute
     * @param int $daysMax
     */
    public function __construct($timeUnit, $daysMax)
    {
        $this->timeUnit = $timeUnit;
        $this->timeUnitIsDay = $timeUnit % 1440 ? true : false;
        $this->daysMax = $daysMax;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options["days_max"])) {
            $this->daysMax = $options["days_max"];
        }

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {
                $form = $event->getForm();

                //Days display mode: range or duration
                $dateEndType = DateType::class;
                if ($options['display_mode'] == "duration") {
                    $dateEndType = DateHiddenType::class;

                    if ($this->daysMax > 1) {
                        $endDayIncluded = isset($options["end_day_included"]) ? $options["end_day_included"] : true;
                        $nbDays = null;
                        if (isset($options['start_options']['data']) && isset($options['end_options']['data'])) {
                            $dateRange = new DateRange(
                                $options['start_options']['data'],
                                $options['end_options']['data']
                            );
                            $nbDays = $dateRange->getDuration($endDayIncluded);
                        }

                        /** @var DateRange $dateRange */
                        $form
                            ->add(
                                'nb_days',
                                ChoiceType::class,
                                array(
                                    'choices' => array_combine(range(1, $this->daysMax), range(1, $this->daysMax)),
                                    'data' => $nbDays,
                                    /** @Ignore */
                                    'placeholder' => '',
                                    'attr' => array(
                                        'class' => 'no-arrow'
                                    ),
                                    'label' => 'date_range.nb_days',
                                    'translation_domain' => 'cocorico',
                                )
                            );
                    } else {//$this->daysMax = 1
                        $form
                            ->add(
                                'nb_days',
                                HiddenType::class,
                                array(
                                    'data' => 1
                                )
                            );
                    }
                }

                $form
                    ->add(
                        'start',
                        DateType::class,
                        array_merge(
                            array(
                                'property_path' => 'start',
                                'widget' => 'single_text',
                                'format' => 'dd/MM/yyyy',
                            ),
                            $options['start_options']
                        )
                    )->add(
                        'end',
                        $dateEndType,
                        array_merge(
                            array(
                                'property_path' => 'end',
                                'widget' => 'single_text',
                                'format' => 'dd/MM/yyyy',
                                'attr' => array(
                                    'class' => 'to'
                                )
                            ),
                            $options['end_options']
                        )
                    );
            }
        );


        $builder->addViewTransformer($options['transformer']);
        $builder->addEventSubscriber($options['validator']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\TimeBundle\Model\DateRange',
                'end_options' => array(),
                'start_options' => array(),
//                'transformer' => null,
//                'validator' => null,
                'allow_single_day' => true,
                'end_day_included' => true,
                'display_mode' => 'range',
                'min_start_time_delay' => 0,
                'days_max' => $this->daysMax,
                'allow_end_in_past' => false,
            )
        );

        // Those normalizers lazily create the required objects, if none given.
        $resolver
            ->setDefault('transformer', null)
            ->setNormalizer(
                'transformer',
                function (Options $options, $value) {
                    if (!$value) {
                        $value = new DateRangeViewTransformer(
                            new OptionsResolver(), array('end_day_included' => $options['end_day_included'])
                        );
                    }

                    return $value;
                }
            )
            ->setAllowedTypes(
                'transformer',
                array('Symfony\Component\Form\DataTransformerInterface', 'null')
            );

        // Those normalizers lazily create the required objects, if none given.
        $resolver
            ->setDefault('validator', null)
            ->setNormalizer(
                'validator',
                function (Options $options, $value) {
                    if (!$value) {
                        $value = new DateRangeValidator(
                            new OptionsResolver(), array(
                                'required' => $options["required"],
                                'allow_single_day' => $options["allow_single_day"],
                                'min_start_time_delay' => $options["min_start_time_delay"],
                                'days_max' => $options["days_max"],
                                'allow_end_in_past' => $options["allow_end_in_past"]
                            )
                        );
                    }

                    return $value;
                }
            )
            ->setAllowedTypes(
                'validator',
                array('Symfony\Component\EventDispatcher\EventSubscriberInterface', 'null')
            );

    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'date_range';
    }
}
