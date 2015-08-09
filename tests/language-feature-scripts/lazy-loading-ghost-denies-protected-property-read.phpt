--TEST--
Verifies that generated lazy loading ghost objects disallow protected property direct read
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    protected $sweets;
}

$factory = new \ProxyManager\Factory\LazyLoadingGhostFactory($configuration);

$proxy = $factory->createProxy(Kitchen::class, function () {});

$proxy->sweets;
?>
--EXPECTF--
%SFatal error:%sCannot access protected property %s::$sweets in %a