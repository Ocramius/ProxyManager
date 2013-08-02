--TEST--
Verifies that generated access interceptors disallow private property direct access
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets;
}

$factory = new \ProxyManager\Factory\AccessInterceptorValueHolderFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

$proxy->sweets;
?>
--EXPECTF--
Fatal error: Cannot access private property Kitchen::$sweets in %s on line %d