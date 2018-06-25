--TEST--
Verifies that lazy loading value holder factory can generate proxy for interfaces.
--FILE--
<?php

require_once __DIR__ . '/init.php';

interface MyInterface
{
    public function do();
}

class MyClass implements MyInterface
{
    public function do()
    {
        echo 'Hello';
    }
}

$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$proxy = $factory
    ->createProxy(MyInterface::class, function (& $wrapped, $proxy, $method, array $parameters, & $initializer) {
        $initializer = null;
        $wrapped     = new MyClass();
    });

$proxy->do();
$proxy->someDynamicProperty = ' World';
echo $proxy->someDynamicProperty;

?>
--EXPECTF--
Hello
Notice: Undefined property: MyInterface::$someDynamicProperty %s
 World
