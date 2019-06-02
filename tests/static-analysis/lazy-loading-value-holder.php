<?php

namespace StaticAnalysis\LazyLoadingValueHolder;

use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;

require_once __DIR__ . '/../../vendor/autoload.php';

class MyProxiedClass
{
    public function sayHello() : string
    {
        return 'Hello!';
    }
}

(static function () : void {
    echo (new LazyLoadingValueHolderFactory())
        ->createProxy(
            MyProxiedClass::class,
            static function (
                ?object & $instance,
                LazyLoadingInterface $proxy,
                string $method,
                array $parameters,
                ?\Closure & $initializer
            ) : bool {
                $instance    = new MyProxiedClass();
                $initializer = null; // disable initialization

                return true;
            }
        )
        ->sayHello();

    $valueHolder = (new LazyLoadingValueHolderFactory())
        ->createProxy(MyProxiedClass::class, static function (
            ?object & $wrappedObject,
            LazyLoadingInterface $proxy,
            string $method,
            array $parameters,
            ?\Closure & $initializer
        ) : bool {
            $initializer   = null; // disable initialization
            $wrappedObject = new MyProxiedClass();

            return true;
        });

    $valueHolder->initializeProxy();

    $wrappedValue = $valueHolder->getWrappedValueHolderValue();

    assert(null !== $wrappedValue);

    echo $wrappedValue->sayHello();

    $valueHolder->setProxyInitializer(static function (
        ?object & $instance,
        LazyLoadingInterface $proxy,
        string $method,
        array $parameters,
        ?\Closure & $initializer
    ) : bool {
        $instance    = new MyProxiedClass();
        $initializer = null; // disable initialization

        return true;
    });
})();
