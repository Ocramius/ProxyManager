--TEST--
Verifies that generated null object disallow public function
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    public function foo()
    {
        return 'bar';
    }
}

$factory = new \ProxyManager\Factory\NullObjectFactory($configuration);

$proxy = $factory->createProxy('Kitchen');

var_dump($proxy->foo());
?>
--EXPECT--
NULL
