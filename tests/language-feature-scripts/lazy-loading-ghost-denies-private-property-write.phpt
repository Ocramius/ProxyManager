--TEST--
Verifies that generated lazy loading ghost objects disallow private property direct write
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets;
}

$factory = new \ProxyManager\Factory\LazyLoadingGhostFactory($configuration);

$proxy = $factory->createProxy(Kitchen::class, function () {});

$proxy->sweets = 'stolen';
?>
--EXPECTF--
%SFatal error:%sCannot access %s property%S in %a