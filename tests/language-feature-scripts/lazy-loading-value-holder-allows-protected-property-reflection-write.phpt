--TEST--
Verifies that generated access interceptors allow protected property write via Reflection
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    protected $sweets;
}

$factory = new \ProxyManager\Factory\AccessInterceptorValueHolderFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

$reflO = new \ReflectionObject($proxy);
$reflP = $reflO->getProperty('sweets');

$reflP->setAccessible(true);
$reflP->setValue($proxy, 'stolen');

echo $proxy->sweets;
?>
--EXPECTF--
stolen
