<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Factory\AccessInterceptorValueHolderFactory;

class Foo
{
    public function doFoo()
    {
        echo "Foo!\n";
    }
}

$factory = new AccessInterceptorValueHolderFactory();

$proxy = $factory->createProxy(
    new Foo(),
    ['doFoo' => function () { echo "pre-foo!\n"; }],
    ['doFoo' => function () { echo "post-foo!\n"; }]
);

$proxy->doFoo();
