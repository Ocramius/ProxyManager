<?php

namespace StaticAnalysis\LazyLoadingGhostObject;

use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\GhostObjectInterface;

require_once __DIR__ . '/../../vendor/autoload.php';

class MyProxiedClass
{
    private string $hello = 'Hello!';

    public function sayHello() : string
    {
        return $this->hello;
    }
}

(static function () : void {
    echo (new LazyLoadingGhostFactory())
        ->createProxy(
            MyProxiedClass::class,
            static function (
                GhostObjectInterface $proxy,
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
        GhostObjectInterface $proxy,
        string $method,
        array $parameters,
        ?\Closure & $initializer,
        array $properties
    ) : bool {
        $initializer = null; // disable initialization

        return true;
    });
})();
