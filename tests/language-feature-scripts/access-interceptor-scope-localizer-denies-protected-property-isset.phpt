--TEST--
Verifies that generated access interceptors disallow protected property direct isset check
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    protected $sweets = 'candy';
}

$factory = new \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

var_dump(isset($proxy->sweets));
?>
--EXPECT--
bool(false)
