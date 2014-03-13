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

$proxy = $factory->createProxy('Kitchen', function () {});

$proxy->sweets;
?>
--EXPECTF--
%SFatal error: Cannot access private property %s::$sweets in %s on line %d