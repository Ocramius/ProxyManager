<?php

declare(strict_types=1);

namespace Foo;

use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;
use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Factory\NullObjectFactory;
use ProxyManager\Factory\RemoteObject\AdapterInterface;
use ProxyManager\Factory\RemoteObjectFactory;
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\Proxy\LazyLoadingInterface;

require_once __DIR__ . '/vendor/autoload.php';

class MyProxiedClass
{
    /** @return string */
    public function sayHello()
    {
        return 'Hello!';
    }
}

echo (new AccessInterceptorScopeLocalizerFactory())
    ->createProxy(
        new MyProxiedClass(),
        [
            'sayHello' => static function (
                object $proxy,
                MyProxiedClass $realInstance,
                string $method,
                array $parameters,
                bool & $returnEarly
            ) {
                echo 'pre-';
            },
        ],
        [
            'sayHello' =>
            /** @param mixed $returnValue */
                static function (
                    object $proxy,
                    MyProxiedClass $realInstance,
                    string $method,
                    array $parameters,
                    & $returnValue,
                    bool & $overrideReturnValue
                ) {
                    echo 'post-';
                },
        ]
    )
    ->sayHello();

$localizedAccessInterceptor = (new AccessInterceptorScopeLocalizerFactory())
    ->createProxy(new MyProxiedClass());

$localizedAccessInterceptor->setMethodPrefixInterceptor(
    'sayHello',
    static function (
        object $proxy,
        MyProxiedClass $realInstance,
        string $method,
        array $parameters,
        bool & $returnEarly
    ) {
        echo 'pre-';
    }
);

$localizedAccessInterceptor->setMethodSuffixInterceptor(
    'sayHello',
    /** @param mixed $returnValue */
    static function (
        object $proxy,
        MyProxiedClass $realInstance,
        string $method,
        array $parameters,
        & $returnValue,
        bool & $returnEarly
    ) {
        echo 'post-';
    }
);

echo $localizedAccessInterceptor->sayHello();

echo (new AccessInterceptorValueHolderFactory())
    ->createProxy(
        new MyProxiedClass(),
        [
            'sayHello' => static function (
                object $proxy,
                MyProxiedClass $realInstance,
                string $method,
                array $parameters,
                bool & $returnEarly
            ) {
                echo 'pre-';
            },
        ],
        [
            'sayHello' =>
            /** @param mixed $returnValue */
                static function (
                    object $proxy,
                    MyProxiedClass $realInstance,
                    string $method,
                    array $parameters,
                    & $returnValue,
                    bool & $overrideReturnValue
                ) {
                    echo 'post-';
                },
        ]
    )
    ->sayHello();

$valueHolderInterceptor = (new AccessInterceptorValueHolderFactory())
    ->createProxy(new MyProxiedClass());

$valueHolderInterceptor->setMethodPrefixInterceptor(
    'sayHello',
    static function (
        object $proxy,
        MyProxiedClass $realInstance,
        string $method,
        array $parameters,
        bool & $returnEarly
    ) {
        echo 'pre-';
    }
);

$valueHolderInterceptor->setMethodSuffixInterceptor(
    'sayHello',
    /** @param mixed $returnValue */
    static function (
        object $proxy,
        MyProxiedClass $realInstance,
        string $method,
        array $parameters,
        & $returnValue,
        bool & $returnEarly
    ) {
        echo 'post-';
    }
);

echo $valueHolderInterceptor->sayHello();

$interceptedValue = $valueHolderInterceptor
    ->getWrappedValueHolderValue();

assert($interceptedValue !== null);

echo $interceptedValue->sayHello();

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

echo (new NullObjectFactory())
    ->createProxy(MyProxiedClass::class)
    ->sayHello();

echo (new NullObjectFactory())
    ->createProxy(new MyProxiedClass())
    ->sayHello();

$adapter = new class implements AdapterInterface
{
    public function call(string $wrappedClass, string $method, array $params = [])
    {
        return 'ohai';
    }
};

echo (new RemoteObjectFactory($adapter))
    ->createProxy(new MyProxiedClass())
    ->sayHello();

echo (new RemoteObjectFactory($adapter))
    ->createProxy(MyProxiedClass::class)
    ->sayHello();
