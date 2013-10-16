<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Factory\RemoteObject\AdapterInterface;
use ProxyManager\Factory\RemoteObjectFactory;

class FooClientSide
{
    public function bar()
    {
        return 'bar';
    }
}

class CustomAdapter implements AdapterInterface
{
    public function call($wrappedClass, $method, array $params = array())
    {
        // build your service name
        $serviceName = $wrappedClass . '.' . $method;
        
        // do your server request
        require __DIR__ . '/remote-proxy/remote-proxy-server.php';
        
        // return server result
        return $result;
    }
}

$factory = new RemoteObjectFactory(new CustomAdapter());
$proxy = $factory->createProxy('FooClientSide');

var_dump($proxy->bar()); // bar remote !
