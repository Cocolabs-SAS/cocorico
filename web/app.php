<?php

use Symfony\Component\HttpFoundation\Request;

/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__ . '/../vendor/autoload.php';

$kernel = new AppKernel('prod', false);
$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();

// Request::setTrustedProxies(
//     // the IP address (or range) of your proxy
//     //['192.0.0.1', '10.0.0.0/8'],
//     ['127.0.0.1', 'REMOTE_ADDR'],
// 
//     // trust *all* "X-Forwarded-*" headers
//     Request::HEADER_X_FORWARDED_ALL
// 
//     // or, if your proxy instead uses the "Forwarded" header
//     // Request::HEADER_FORWARDED
// 
//     // or, if you're using AWS ELB
//     // Request::HEADER_X_FORWARDED_AWS_ELB
// );

if ($trustedProxies = $request->server->get('CC_REVERSE_PROXY_IPS')) {
    // trust *all* requests
    Request::setTrustedProxies(array_merge(['127.0.0.1'], explode(',', $trustedProxies)),

    // trust *all* "X-Forwarded-*" headers
    Request::HEADER_X_FORWARDED_ALL);
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
