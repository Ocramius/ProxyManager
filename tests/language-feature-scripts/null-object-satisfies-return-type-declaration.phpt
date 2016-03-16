--TEST--
Verifies that generated null object satisfies return type declarations
--FILE--
<?php

declare (strict_types = 1);

require_once __DIR__ . '/init.php';

class Kitchen
{
    public function foo()
    {
        return 'bar';
    }
}

$factory = new \ProxyManager\Factory\NullObjectFactory($configuration);

$proxy = $factory->createProxy(Kitchen::class);

var_dump($proxy instanceof Kitchen);
?>
--EXPECT--
bool(true)
