--TEST--
Verifies that generated lazy loading ghost objects can skip calling the proxied destructor
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Destructable
{
    public function __destruct()
    {
        echo __FUNCTION__;
    }
}

$factory = new \ProxyManager\Factory\LazyLoadingGhostFactory($configuration);

$init = function ($object, $method, $parameters, & $initializer) {
    echo 'init';
    $initializer = null;
};

$proxy = $factory->createProxy(Destructable::class, $init, ['skipDestructor' => true]);
echo "NO __destruct\n";
unset($proxy);

$proxy = $factory->createProxy(Destructable::class, $init, ['skipDestructor' => true]);
echo 'DO ';
$proxy->triggerInit = true;
unset($proxy);

$proxy = $factory->createProxy(Destructable::class, $init);
echo "\nDO ";
unset($proxy);
?>
--EXPECT--
NO __destruct
DO init__destruct
DO __destruct
