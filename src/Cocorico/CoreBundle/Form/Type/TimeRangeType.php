<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type;

use Cocorico\CoreBundle\Form\DataTransformer\TimeRangeViewTransformer;
use Cocorico\CoreBundle\Model\DateRange;
use Cocorico\CoreBundle\Model\TimeRange;
use Cocorico\CoreBundle\Validator\TimeRangeValidator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class TimeRangeType extends AbstractType
{
    protected $timeUnit;
    protected $timeUnitIsDay;
    protected $timesMax;

    /**
     * @param int $timeUnit in minute
     * @param int $timesMax
     */
    public function __construct($timeUnit, $timesMax)
    {
        $this->timeUnit = $timeUnit;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
        $this->timesMax = $timesMax;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->timeUnitIsDay) {
            throw new \Exception("Time ranges are only available for time unit not in day mode");
        }

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {
                $form = $event->getForm();

                //Times display mode: range or duration
                $timeEndType = 'time';
                $widgetType = 'choice';
                if ($options['display_mode'] == "duration") {
                    $timeEndType = 'time_hidden';
                    $widgetType = 'hidden';

                    if ($this->timesMax > 1) { //Create times unit choice list limited to timesMax
                        $nbMinutes = null;
                        if (isset($options['start_options']['data']) && isset($options['end_options']['data'])) {
                            $timeRange = new TimeRange(
                                $options['start_options']['data'],
                                $options['end_options']['data']
                            );
                            $nbMinutes = $timeRange->getDuration($this->timeUnit) * $this->timeUnit;
                        }

                        /** @var DateRange $dateRange */
                        $form
                            ->add(
                                'nb_minutes',
                                'choice',
                                array(
                                    'choices' => array_combine(
                                    //from one time unit to timesMax * timeUnit
                                        range($this->timeUnit, $this->timesMax * $this->timeUnit, $this->timeUnit),
                                        range(1, $this->timesMax)
                                    ),
                                    'data' => $nbMinutes,
                                    /** @Ignore */
                                    'empty_value' => '',
                                    'attr' => array(
                                        'class' => 'no-scroll no-arrow'
                                    )
                                )
                            );
                    } else {//One time unit. $this->timesMax = 1
                        $form
                            ->add(
                                'nb_minutes',
                                'hidden',
                                array(
                                    'data' => $this->timeUnit
                                )
                            );
                    }
                }

                $form
                    ->add(
                        'start',
                        'time',
                        array_merge(
                            array(
                                'label' => 'time_range.start',
                                'property_path' => 'start',
                                'empty_value' => '',
                                'widget' => 'choice',
                                'input' => 'datetime',
                                'model_timezone' => 'UTC',
                                'view_timezone' => 'UTC',
                                'attr' => array(
                                    'data-type' => 'start',
                                ),
                            ),
                            $options['start_options']
                        )
                    )->add(
                        'end',
                        $timeEndType,
                        array_merge(
                            array(
                                'label' => 'time_range.end',
                                'property_path' => 'end',
                                'empty_value' => '',
                                'widget' => $widgetType,
                                'input' => 'datetime',
                                'model_timezone' => 'UTC',
                                'view_timezone' => 'UTC',
                                'attr' => array(
                                    'data-type' => 'end',
                                ),
                            ),
                            $options['end_options']
                        )
                    );
            }
        );

        $builder->addViewTransformer($options['transformer']);
        $builder->addEventSubscriber($options['validator']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Model\TimeRange',
                'end_options' => array(),
                'start_options' => array(),
                'transformer' => null,
                'validator' => null,
                'translation_domain' => 'cocorico_listing',
                'display_mode' => 'range',
            )
        );

        $resolver->setAllowedTypes(
            array(
                'transformer' => 'Symfony\Component\Form\DataTransformerInterface',
                'validator' => 'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            )
        );

        // Those normalizers lazily create the required objects, if none given.
        $resolver->setNormalizers(
            array(
                'transformer' => function (Options $options, $value) {
                    if (!$value) {
                        $value = new TimeRangeViewTransformer(new OptionsResolver());
                    }

                    return $value;
                },
                'validator' => function (Options $options, $value) {
                    if (!$value) {
                        $value = new TimeRangeValidator(
                            new OptionsResolver(), array(
                                'required' => $options["required"]
                            )
                        );
                    }

                    return $value;
                },
            )
        );
    }

    public function getName()
    {
        return 'time_range';
    }
}
