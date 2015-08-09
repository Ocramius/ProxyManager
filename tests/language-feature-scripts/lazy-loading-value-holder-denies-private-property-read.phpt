--TEST--
Verifies that generated lazy loading value holders disallow private property direct read
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets;
}

$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$proxy = $factory->createProxy(Kitchen::class, function (& $wrapped, $proxy, $method, array $parameters, & $initializer) {
    $initializer = null;
    $wrapped     = new Kitchen();
});

$proxy->sweets;
?>
--EXPECTF--
%SFatal error:%sCannot access private property %s::$sweets in %a