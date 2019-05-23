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
    public function sayHello() : string
    {
        return 'Hello!';
    }
}

echo (new AccessInterceptorScopeLocalizerFactory())
    ->createProxy(
        new MyProxiedClass(),
        ['sayHello' => static function (string $foo) { 'ha'; }]
    )
    ->sayHello();

echo (new AccessInterceptorValueHolderFactory())
    ->createProxy(new MyProxiedClass())
    ->sayHello();

echo (new LazyLoadingGhostFactory())
    ->createProxy(MyProxiedClass::class,
        $initializer = function (
            GhostObjectInterface $proxy,
            string $method,
            array $parameters,
            & $initializer,
            array $properties
        ) {
            $initializer = null; // disable initialization
        })
    ->sayHello();

echo (new LazyLoadingValueHolderFactory())
    ->createProxy(MyProxiedClass::class, static function (
        & $wrappedObject, LazyLoadingInterface $proxy, $method, array $parameters, & $initializer
    ) : bool {
        $initializer   = null; // disable initialization
        $wrappedObject = new MyProxiedClass();

        return true;
    })
    ->sayHello();

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
