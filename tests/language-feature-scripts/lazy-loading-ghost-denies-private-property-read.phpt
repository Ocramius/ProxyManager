--TEST--
Verifies that generated lazy loading ghost objects disallow private property direct read
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets;
}

$factory = new \ProxyManager\Factory\LazyLoadingGhostFactory($configuration);

$proxy = $factory->createProxy(Kitchen::class, function () {});

$proxy->sweets;
?>
--EXPECTF--
%SFatal error:%sCannot access private property %s::$sweets in %a