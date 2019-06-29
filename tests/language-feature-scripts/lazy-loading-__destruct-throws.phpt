--TEST--
Verifies that generated lazy loading value holders disallow protected property direct unset
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Destructable
{
    public function __destruct()
    {
        # code...
    }
}

$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$proxy = $factory->createProxy(Destructable::class, function (& $wrapped, $proxy, $method, array $parameters, & $initializer) {
    $initializer = null;
    $wrapped     = new Destructable();
});

unset($proxy->sweets);
?>
--EXPECTF--
%SUncaught Error:%sCall to a member function __destruct() on null in %a