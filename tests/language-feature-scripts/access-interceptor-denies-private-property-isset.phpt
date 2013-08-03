--TEST--
Verifies that generated access interceptors disallow private property direct isset check
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets = 'candy';

    /** Defined to force magic methods generation */
    public $accessible;
}

$factory = new \ProxyManager\Factory\AccessInterceptorValueHolderFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

var_dump(isset($proxy->sweets));
?>
--EXPECT--
bool(false)