--TEST--
Verifies that generated access interceptors disallow private property direct access
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Foo
{
    private $sweets;
}

$factory = new \ProxyManager\Factory\AccessInterceptorValueHolderFactory($configuration);

$proxy = $factory->createProxy(new Foo());

$proxy->sweets;
?>
--EXPECTF--
Fatal error: Cannot access private property Foo::$sweets in %s on line %d