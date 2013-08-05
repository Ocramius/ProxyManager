--TEST--
Verifies that generated access interceptors disallow protected property direct write
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    protected $sweets;
}

$factory = new \ProxyManager\Factory\AccessInterceptorValueHolderFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

$proxy->sweets = 'stolen';
?>
--EXPECTF--
Fatal error: Cannot access protected property %s::$sweets in %s on line %d