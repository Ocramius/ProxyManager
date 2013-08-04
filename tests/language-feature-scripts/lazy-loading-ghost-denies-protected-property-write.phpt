--TEST--
Verifies that generated lazy loading ghost objects disallow protected property direct write
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    protected $sweets;
}

$factory = new \ProxyManager\Factory\LazyLoadingGhostFactory($configuration);

$proxy = $factory->createProxy('Kitchen', function () {});

$proxy->sweets = 'stolen';
?>
--EXPECTF--
Fatal error: Cannot access protected property %s::$sweets in %s on line %d