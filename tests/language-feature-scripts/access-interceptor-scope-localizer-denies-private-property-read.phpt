--TEST--
Verifies that generated access interceptors disallow private property direct read
--SKIPIF--
<?php
if (! method_exists('Closure', 'bind')) {
    echo 'skip PHP 5.4+ is needed to localize private properties';
}
?>
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets;
}

$factory = new \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

$proxy->sweets;
?>
--EXPECTF--
%SFatal error: Cannot access private property %s::$sweets in %s on line %d
