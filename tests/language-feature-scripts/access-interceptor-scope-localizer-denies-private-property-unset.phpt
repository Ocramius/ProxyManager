--TEST--
Verifies that generated access interceptors disallow private property direct unset
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

unset($proxy->sweets);
?>
--EXPECTF--
%SFatal error: Cannot %s property%sin %s on line %d
