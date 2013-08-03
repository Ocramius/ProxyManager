--TEST--
Verifies that generated access interceptors disallow protected property direct isset check
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    protected $sweets;
}

$factory = new \ProxyManager\Factory\AccessInterceptorValueHolderFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

isset($proxy->sweets);
?>
--EXPECTF--
Fatal error: Cannot access protected property %s::$sweets in %s on line %d