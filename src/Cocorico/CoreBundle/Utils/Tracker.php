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
        'session_id' => 'ffffffff-1111-2222-3333-444444444444',
        'page' => $page,
        'action' => $action,
        'meta' => json_encode(array_merge(array('source' => 'symfony'), $meta)),
        'client_context' => array(),
        'server_context' => array(),
    );
    $payload = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $_SERVER['ITOU_HOST']+'/track');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $result = curl_exec($ch);
    if(!curl_errno($ch))
    {
        $info = curl_getinfo($ch);
        print("curl error:");
        var_dump($info);
    }
    }
}
