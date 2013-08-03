--TEST--
Verifies that generated lazy loading ghost objects disallow private property direct isset check
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets = 'candy';

    /** Defined to force magic methods generation */
    public $accessible;
}

$factory = new \ProxyManager\Factory\LazyLoadingGhostFactory($configuration);

$proxy = $factory->createProxy('Kitchen', function () {});

var_dump(isset($proxy->sweets));
?>
--EXPECT--
bool(false)