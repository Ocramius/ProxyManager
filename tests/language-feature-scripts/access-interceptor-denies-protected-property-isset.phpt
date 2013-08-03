--TEST--
Verifies that generated access interceptors disallow protected property direct isset check
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    protected $sweets = 'candy';

    /** Defined to force magic methods generation */
    public $accessible;
}

$factory = new \ProxyManager\Factory\AccessInterceptorValueHolderFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

var_dump(isset($proxy->sweets));
?>
--EXPECT--
bool(false)