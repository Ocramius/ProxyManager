--TEST--
Verifies that generated lazy loading value holders disallow protected property direct isset check
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    protected $sweets = 'candy';
}

$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$proxy = $factory->createProxy('Kitchen', function (& $wrapped, $proxy, $method, array $parameters, & $initializer) {
    $initializer = null;
    $wrapped     = new Kitchen();
});

var_dump(isset($proxy->sweets));
?>
--EXPECT--
bool(false)