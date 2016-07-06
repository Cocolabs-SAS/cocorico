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

use Cocorico\CoreBundle\Model\TimeRange;
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
        $resolver->setDefaults(
            array(
                'required' => true,
                'display_mode' => 'range',
            )
        );

        $resolver->setAllowedValues('required', array(true, false));
        $resolver->setAllowedValues('display_mode', array('range', 'duration'));
    }

    public function onPostBind(FormEvent $event)
    {
        $form = $event->getForm();

        /* @var $timeRange TimeRange */
        $timeRange = $form->getNormData();

        if ($timeRange && $timeRange->start && $timeRange->end) {
            if ($this->options['required'] && (!$timeRange->start || !$timeRange->end)) {
                $form->addError(new FormError('time_range.invalid.required'));
            }

            if ($timeRange->start > $timeRange->end) {
                $form->addError(new FormError('time_range.invalid.end_before_start'));
            }

            if (($timeRange->start->format('H:i') === $timeRange->end->format('H:i'))
                && ($timeRange->start->format('H:i') != '00:00')
            ) {
                $form->addError(new FormError('time_range.invalid.single_time'));
            }
        } elseif ($timeRange && (($timeRange->start && !$timeRange->end) || (!$timeRange->start && $timeRange->end))) {
            $form->addError(new FormError('time_range.invalid.required'));
        } elseif ($timeRange && (!$timeRange->start && !$timeRange->end)) {
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
