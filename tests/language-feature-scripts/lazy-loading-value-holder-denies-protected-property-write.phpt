--TEST--
Verifies that generated lazy loading value holders disallow protected property direct write
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    protected $sweets;
}

$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$proxy = $factory->createProxy(Kitchen::class, function (& $wrapped, $proxy, $method, array $parameters, & $initializer) {
    $initializer = null;
    $wrapped     = new Kitchen();
});

$proxy->sweets = 'stolen';
?>
--EXPECTF--
%SFatal error:%sCannot %s property%sin %a