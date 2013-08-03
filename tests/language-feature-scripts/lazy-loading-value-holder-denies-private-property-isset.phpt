--TEST--
Verifies that generated lazy loading value holders disallow private property direct isset check
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets = 'candy';
}

$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$proxy = $factory->createProxy('Kitchen', function () {});

var_dump(isset($proxy->sweets));
?>
--EXPECT--
bool(false)