<?php

namespace StaticAnalysis\NullObject;

use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Factory\NullObjectFactory;
use ProxyManager\Proxy\LazyLoadingInterface;

require_once __DIR__ . '/../../vendor/autoload.php';

class MyProxiedClass
{
    public function sayHello() : ?string
    {
        return 'Hello!';
    }
}

echo (new NullObjectFactory())
    ->createProxy(MyProxiedClass::class)
    ->sayHello();

echo (new NullObjectFactory())
    ->createProxy(new MyProxiedClass())
    ->sayHello();
