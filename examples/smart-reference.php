<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Configuration;
use ProxyManager\Factory\AccessInterceptorValueHolderFactory;

class Foo
{
    public function doFoo()
    {
        echo "Foo!\n";
    }
}

$config = new Configuration();
$factory = new AccessInterceptorValueHolderFactory($config);

$proxy = $factory->createProxy(
    new Foo(),
    array('doFoo' => function () { echo "pre-foo!\n"; }),
    array('doFoo' => function () { echo "post-foo!\n"; })
);

$proxy->doFoo();
