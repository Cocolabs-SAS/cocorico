<?php


namespace Cocorico\UserBundle\Listener;

use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Event\UserEvent;
use Cocorico\UserBundle\Event\UserEvents;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Doctrine ORM listener
 *
 */
class UserEntityListener implements EventSubscriber
{

    private $dispatcher;
    private $session;
    private $timezone;

    /**
     * UserEntityListener constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param Session                  $session
     * @param string                   $timezone
     */
    public function __construct(EventDispatcherInterface $dispatcher, Session $session, $timezone)
    {
        $this->dispatcher = $dispatcher;
        $this->session = $session;
        $this->timezone = $timezone;
    }

    public function getSubscribedEvents()
    {
        return array(
//            Events::prePersist,
            Events::postPersist,
            Events::preUpdate,
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->handleEvent($args);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->handleEvent($args);
    }

    private function handleEvent(LifecycleEventArgs $args)
    {
        /** @var User $user */
        $user = $args->getEntity();
        if ($user instanceof UserInterface) {
            $phoneHasChanged = $this->handlePhoneChange($user, $args);

            if ($phoneHasChanged) {
                $user->setPhoneVerified(false);
                $event = new UserEvent($user);

                $this->dispatcher->dispatch(UserEvents::USER_PHONE_CHANGE, $event);
            }

            //Session
            $this->handleTimezoneChange($user, $args);
        }
    }

    /**
     * @param UserInterface|User $user
     * @param LifecycleEventArgs $args
     * @return bool
     */
    private function handlePhoneChange(UserInterface $user, LifecycleEventArgs $args)
    {
        $phoneHasChanged = false;

        if ($args instanceof PreUpdateEventArgs) {
            //Check if phone has changed
            $phoneHasChanged =
                (
                    $args->hasChangedField('phone') &&
                    $args->getNewValue('phone') != $args->getOldValue('phone')
                ) ||
                (
                    $args->hasChangedField('phonePrefix') &&
                    $args->getNewValue('phonePrefix') != $args->getOldValue('phonePrefix')
                );

            // We are doing a update, so we must force Doctrine to update the
            // change set in case we changed something above
            $em = $args->getEntityManager();
            $uow = $em->getUnitOfWork();
            $meta = $em->getClassMetadata(get_class($user));
            $uow->recomputeSingleEntityChangeSet($meta, $user);
        } else {
            if ($user->getPhone()) {
                $phoneHasChanged = true;
            }
        }


        return $phoneHasChanged;
    }

    /**
     * @param UserInterface|User $user
     * @param LifecycleEventArgs $args
     */
    private function handleTimezoneChange(UserInterface $user, LifecycleEventArgs $args)
    {
        $timezone = $user->getTimeZone() ? $user->getTimeZone() : $this->timezone;
        $this->session->set('timezone', $timezone);
    }
}
