--TEST--
Verifies that lazy loading value holder proxies for interfaces disallow public property read attempts
--FILE--
<?php

require_once __DIR__ . '/init.php';

interface MyInterface
{
}

$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$proxy = $factory
    ->createProxy(MyInterface::class, function (& $wrapped, $proxy, $method, array $parameters, & $initializer) : bool {
        $initializer = null;
        $wrapped     = new class implements MyInterface {
        };

        return true;
    });

echo $proxy->someDynamicProperty;

?>
--EXPECTF--
Notice: Undefined property: MyInterface::$someDynamicProperty %s
