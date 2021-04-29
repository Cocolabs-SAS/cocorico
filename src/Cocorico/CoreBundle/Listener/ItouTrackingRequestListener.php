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


        $uri = $request->getPathInfo();
        if (strpos($uri,'media/cache')) {
            // Skip if only reading cached media assets
            return;
        }
        if (strpos($uri,'_wdt')) {
            // Skip symfony debug
            return;
        }

        $payload = [
        ];

        if (in_array('cmp', $request->query->keys())) {
            // Add campaign marker
            $campaign_id = $request->query->get('cmp');
            $payload['cmp'] = $campaign_id; 
        }

        $client_context = [
            'referer' => $request->headers->get('referer'),
            'user_agent' => $request->headers->get('User-Agent'),
        ];

        $this->tracker->track(
            $request->getPathInfo(),
            'load',
            $payload,
            $session,
            $client_context,
        );
    }
}
