--TEST--
Verifies that generated lazy loading ghost objects disallow private property direct unset
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets;

    /** Defined to force magic methods generation */
    public $accessible;
}

$factory = new \ProxyManager\Factory\LazyLoadingGhostFactory($configuration);

$proxy = $factory->createProxy('Kitchen', function () {});

unset($proxy->sweets);
?>
--EXPECTF--
Fatal error: Cannot access private property %s::$sweets in %s on line %d