<?php

namespace StaticAnalysis\NullObject;

use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Factory\NullObjectFactory;
use ProxyManager\Proxy\LazyLoadingInterface;

require_once __DIR__ . '/../../vendor/autoload.php';

class MyProxiedClass
{
    /** @return string|null return type cannot be enforced on a null object - nothing is ever returned */
    public function sayHello()
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
