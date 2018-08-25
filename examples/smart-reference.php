<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Factory\AccessInterceptorValueHolderFactory;

class Foo
{
    public function doFoo() : void
    {
        echo "Foo!\n";
    }
}

$factory = new AccessInterceptorValueHolderFactory();

$proxy = $factory->createProxy(
    new Foo(),
    [
        'doFoo' => function () : void {
            echo "pre-foo!\n";
        },
    ],
    [
        'doFoo' => function () : void {
            echo "post-foo!\n";
        },
    ]
);

$proxy->doFoo();
