<?php

namespace StaticAnalysis\LazyLoadingGhostObject;

use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\LazyLoadingInterface;

require_once __DIR__ . '/../../vendor/autoload.php';

class MyProxiedClass
{
    public function sayHello() : string
    {
        return 'Hello!';
    }
}

echo (new LazyLoadingGhostFactory())
    ->createProxy(
        MyProxiedClass::class,
        static function (
            ?object & $instance,
            LazyLoadingInterface $proxy,
            string $method,
            array $parameters,
            ?\Closure & $initializer,
            array $properties
        ) : bool {
            $initializer = null; // disable initialization

            return true;
        }
    )
    ->sayHello();

$lazyLoadingGhost = (new LazyLoadingGhostFactory())
    ->createProxy(
        MyProxiedClass::class,
        static function () : bool {
            return true;
        }
    );

$lazyLoadingGhost->setProxyInitializer(static function (
    ?object & $instance,
    LazyLoadingInterface $proxy,
    string $method,
    array $parameters,
    ?\Closure & $initializer,
    array $properties
) : bool {
    $initializer = null; // disable initialization

    return true;
});
