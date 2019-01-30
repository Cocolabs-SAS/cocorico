<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\TimeBundle\Validator;

use Cocorico\TimeBundle\Model\TimeRange;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimeRangeValidator implements EventSubscriberInterface, TranslationContainerInterface
{
    protected $options = array();

    public function __construct(OptionsResolver $resolver, array $options = array())
    {
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $hoursAvailable = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23);
        $resolver->setDefaults(
            array(
                'required' => true,
                'display_mode' => 'range',
                'hours_available' => $hoursAvailable,
            )
        );

        $resolver->setAllowedValues('required', array(true, false));
        $resolver->setAllowedValues('display_mode', array('range', 'duration'));
        $resolver->setAllowedValues(
            'hours_available',
            function ($value) use ($hoursAvailable) {
                if (!is_array($value) || count(array_intersect($value, $hoursAvailable)) != count($value)) {
                    return false;
                } else {
                    return true;
                }
            }
        );
    }

    public function onPostBind(FormEvent $event)
    {
        $hoursAvailable = $this->options['hours_available'];

        $form = $event->getForm();

        /* @var TimeRange $timeRange */
        $timeRange = $form->getNormData();

        if ($timeRange && $timeRange->start && $timeRange->end) {
            $start = $timeRange->start;
            $end = $timeRange->end;

            if (!in_array(intval($start->format('H')), $hoursAvailable) || !$end) {
                $form->addError(new FormError('time_range.invalid.required'));
            }

//            if ($start > $end && $end->format('H:i') != '00:00') {
//                $form->addError(new FormError('time_range.invalid.end_before_start'));
//            }

            if (($start->format('H:i') === $end->format('H:i')) && ($start->format('H:i') != '00:00')) {
                $form->addError(new FormError('time_range.invalid.single_time'));
            }
        } elseif ($timeRange && (($timeRange->start && !$timeRange->end) || (!$timeRange->start && $timeRange->end))) {
            $form->addError(new FormError('time_range.invalid.required'));
        } elseif ($timeRange && (!$timeRange->start && !$timeRange->end) && $this->options['required'] == true) {
            $form->addError(new FormError('time_range.invalid.required'));
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
        $messages = array();
        $messages[] = new Message("time_range.invalid.required", 'cocorico');
        $messages[] = new Message("time_range.invalid.end_before_start", 'cocorico');
        $messages[] = new Message("time_range.invalid.single_time", 'cocorico');
        $messages[] = new Message("time_range.invalid.duration", 'cocorico');
        $messages[] = new Message("time_range.invalid.min_start {{ min_start_time }}", 'cocorico');

        return $messages;
    }
}
