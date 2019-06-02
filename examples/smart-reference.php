<?php

declare(strict_types=1);

namespace ProxyManager\Example\SmartReference;

use ProxyManager\Factory\AccessInterceptorValueHolderFactory;

require_once __DIR__ . '/../vendor/autoload.php';

class Foo
{
    public function doFoo() : void
    {
        echo "Foo!\n";
    }
}

(static function () : void {
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
})();
