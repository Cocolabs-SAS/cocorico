<?php

namespace Cocorico\CoreBundle\Utils;
use Symfony\Component\HttpFoundation\Request;

const VERSION = 1;

/*
 * This is an ongoing attempt at implementing a decent PHP tracker
 * But php being what it is, it's not that simple.
 * Symfony does not help
 */

class Tracker
{
    private $env;
    private $host;
    private $order;
    
    public function __construct($env, $host)
    {
        $this->env = $env;
        $this->host = $host;
        $this->order = 0;
    }
    

    /**
     * Track event
     *
     * @param string $page
     * @param string $action
     * @param array|null $meta
     * @return string|null
     */
    public function track($page, $action, $meta=array())
    {
    $data = array(
        '_v' => VERSION,
        'timestamp' => date('Y-m-d\TH:i:s.Z\Z', time()),
        'order' => $this->order++,
        'env' => $this->env,
        'session_id' => 'NOT_YET_SET',
        'page' => $page,
        'action' => $action,
        'meta' => array('source' => 'symfony'),
        'client_context' => array(),
        'server_context' => array(),
    );
    }
}
