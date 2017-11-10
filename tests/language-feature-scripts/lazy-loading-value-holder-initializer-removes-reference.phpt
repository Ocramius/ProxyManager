--TEST--
Verifies that lazy loading value holder cannot be turned to a reference
?>
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Proxied
{
    public $publicProperty;
}

$dummy = new stdClass();
$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$initializer = function (& $wrapped, $proxy, $method, array $parameters, & $initializer) use ($dummy) {
    $initializer = null;
    $wrapped     = new Proxied();
    $dummy->reference = & $wrapped;
};

$proxy = $factory->createProxy(Proxied::class, $initializer);

$proxy->publicProperty = 123;

$clone = clone $proxy;
$clone->publicProperty = 234;

echo $proxy->publicProperty, "\n";
echo $clone->publicProperty, "\n";

?>
--EXPECT--
123
234
