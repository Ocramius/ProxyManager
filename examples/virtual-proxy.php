<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

class Foo
{
    public function __construct()
    {
        sleep(5);
    }

    public function doFoo()
    {
        echo "Foo!";
    }
}

$startTime = microtime(true);
$config    = new Configuration();
$factory   = new LazyLoadingValueHolderFactory($config);

for ($i = 0; $i < 1000; $i += 1) {
    $proxy = $factory->createProxy(
        'Foo',
        function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) {
            $initializer   = null;
            $wrappedObject = new Foo();

            return true;
        }
    );
}

var_dump('time after 1000 instantiations: ' . (microtime(true) - $startTime));

$proxy->doFoo();

var_dump('time after single call to doFoo: ' . (microtime(true) - $startTime));
