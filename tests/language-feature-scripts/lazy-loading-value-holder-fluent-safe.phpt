--TEST--
Verifies that generated lazy loading value holders can be fluent-safe
--FILE--
<?php

require_once __DIR__ . '/init.php';

class FluentClass
{
    public function foo()
    {
        return $this;
    }
}

$fluentObject = new FluentClass();

$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$init = function (& $wrapped, $proxy, $method, $parameters, & $initializer) use ($fluentObject) {
    $wrapped = $fluentObject;
    $initializer = null;
};

$proxy = $factory->createProxy(FluentClass::class, $init, ['fluentSafe' => true]);
echo $proxy->foo() === $proxy;

$proxy = $factory->createProxy(FluentClass::class, $init);
echo $proxy->foo() === $fluentObject;
?>
--EXPECT--
11
