--TEST--
Verifies that generated lazy loading ghost objects disallow private property direct unset
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets;
}

$factory = new \ProxyManager\Factory\LazyLoadingGhostFactory($configuration);

$proxy = $factory->createProxy(Kitchen::class, function () {});

unset($proxy->sweets);
?>
--EXPECTF--
%SFatal error:%sCannot %s property %a