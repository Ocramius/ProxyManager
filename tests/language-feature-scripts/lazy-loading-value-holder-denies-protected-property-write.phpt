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

$proxy = $factory->createProxy('Kitchen', function (& $wrapped, $proxy, $method, array $parameters, & $initializer) {
    $initializer = null;
    $wrapped     = new Kitchen();
});

$proxy->sweets = 'stolen';
?>
--EXPECTF--
Fatal error: Cannot access protected property %s::$sweets in %s on line %d