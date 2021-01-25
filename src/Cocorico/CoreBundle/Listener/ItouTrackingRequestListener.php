<?php

namespace Cocorico\CoreBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Cocorico\CoreBundle\Utils\Tracker;

class ItouTrackingRequestListener
{

    protected $tracker;
    public function __construct()
    {
        $this->tracker = new Tracker($_SERVER['ITOU_ENV'], "test");
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        $payload = [
        ];

        $uri = $request->getPathInfo();
        if (strpos($uri,'media/cache')) {
            // Skip if only reading cached media assets
            return;
        }

        $this->tracker->track($request->getPathInfo(), 'load', $payload, $session);
    }
}
