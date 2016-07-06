<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Validator;

use Cocorico\CoreBundle\Model\DateRange;
use DateTime;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateRangeValidator implements EventSubscriberInterface, TranslationContainerInterface
{
    protected $options = array();

    public function __construct(OptionsResolver $resolver, array $options = array())
    {
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'allow_end_in_past' => false,
                'allow_single_day' => true,
                'end_day_included' => true,
                'required' => true,
                'min_start_delay' => null,//Number of days to add to the current date to consider start date as valid
                'display_mode' => 'range',
                'days_max' => 365
            )
        );

        $resolver->setAllowedValues('allow_end_in_past', array(true, false));
        $resolver->setAllowedValues('allow_single_day', array(true, false));
        $resolver->setAllowedValues('end_day_included', array(true, false));
        $resolver->setAllowedValues('required', array(true, false));
        $resolver->setAllowedValues('min_start_delay', array_merge(array(null), range(0, 30)));
        $resolver->setAllowedValues('display_mode', array('range', 'duration'));
    }

    public function onPostBind(FormEvent $event)
    {
        $form = $event->getForm();

        /* @var $dateRange DateRange */
        $dateRange = $form->getNormData();
        if (!$this->options['required'] && !$dateRange) {
            return;
        }

        //Date required
        if (!$this->options['required'] && !$dateRange->start) {
            return;
        }

        //Date required
        if (($dateRange->start && !$dateRange->end) || (!$dateRange->start && $dateRange->end)) {
            $form->addError(new FormError('date_range.invalid.required'));

            return;
        }

        if ($this->options['required'] && (!$dateRange->start || !$dateRange->end)) {
            $form->addError(new FormError('date_range.invalid.required'));

            return;
        }

        $now = new \DateTime();
        //Min start date
        if ($this->options['min_start_delay'] !== null) {
            $minStartDelay = $this->options['min_start_delay'];
            if ($minStartDelay >= 0) {
                $now->add(new \DateInterval('P' . $minStartDelay . 'D'));
            }

            if ($dateRange->start) {
                $interval = $now->diff($dateRange->start)->format('%r%a');

                if ($interval < 0) {
                    $form->addError(
                        new FormError(
                            'date_range.invalid.min_start {{ min_start_day }}',
                            'cocorico',
                            array(
                                '{{ min_start_day }}' => $now->format('d/m/Y'),
                            )
                        )
                    );
                }
            }
        }

        //Max end date
        if ($this->options['days_max'] !== null) {
            if ($dateRange->start && $dateRange->end) {
                $start = clone $dateRange->start;
                $dateEndMax = $start->add(new \DateInterval('P' . $this->options['days_max'] . 'D'));

                if ($dateRange->end > $dateEndMax) {
                    $form->addError(
                        new FormError(
                            'date_range.invalid.max_end {{ date_max }}',
                            'cocorico',
                            array(
                                '{{ date_max }}' => $dateEndMax->format('d/m/Y'),
                            )
                        )
                    );
                }
            }
        }

        //Start after End
        if ($dateRange->start > $dateRange->end) {
            $form->addError(new FormError('date_range.invalid.end_before_start'));
        }

        //Date range equal to one day not allowed
        if (!$this->options['allow_single_day'] &&
            $dateRange->start->format('Y-m-d') === $dateRange->end->format('Y-m-d')
        ) {
            $form->addError(new FormError('date_range.invalid.single_day'));
        }

        //Date range equal to one day allowed but with start = end whereas end day is not included.
        //In this case end should be equal to start + 1 day.
        if ($this->options['allow_single_day'] && !$this->options['end_day_included'] &&
            ($dateRange->start->format('Y-m-d') === $dateRange->end->format('Y-m-d'))
        ) {
            $form->addError(new FormError('date_range.invalid.single_day'));
        }


        //End date in past
        if ($dateRange->end) {
            if (!$this->options['allow_end_in_past'] and ($dateRange->end < new DateTime())) {
                $form->addError(new FormError('date_range.invalid.end_in_past'));
            }
        }

    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SUBMIT => 'onPostBind',
        );
    }


    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages[] = new Message("date_range.invalid.end_in_past", 'cocorico');
        $messages[] = new Message("date_range.invalid.min_start {{ min_start_day }}", 'cocorico');
        $messages[] = new Message("date_range.invalid.max_end {{ date_max }}", 'cocorico');
        $messages[] = new Message("date_range.invalid.single_day", 'cocorico');
        $messages[] = new Message("date_range.invalid.end_before_start", 'cocorico');
        $messages[] = new Message("date_range.invalid.required", 'cocorico');

        return $messages;
    }
}
