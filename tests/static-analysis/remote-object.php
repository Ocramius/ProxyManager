<?php

namespace StaticAnalysis\RemoteObject;

use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Factory\NullObjectFactory;
use ProxyManager\Factory\RemoteObject\AdapterInterface;
use ProxyManager\Factory\RemoteObjectFactory;
use ProxyManager\Proxy\LazyLoadingInterface;

require_once __DIR__ . '/../../vendor/autoload.php';

class MyProxiedClass
{
    public function sayHello() : string
    {
        return 'Hello!';
    }
}

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
