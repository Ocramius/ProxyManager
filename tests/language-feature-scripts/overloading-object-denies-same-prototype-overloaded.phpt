--TEST--
Verifies that generated remote object can call public method
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Baz
{
    public function __toString() {
        return 'baz class';
    }
}

class Foo
{
    public function bar()
    {
        return 'default';
    }
}

$foo = new Foo();

$factory = new \ProxyManager\Factory\OverloadingFactory();
$proxy = $factory->createProxy($foo);
$factory->createProxyMethods($proxy, array(
    array('bar' => 
        $c = function($string) { return $string; }
    ),
    array('bar' => 
        $c = function($otherString) { return $otherString; }
    ),
));

?>
--EXPECTF--
Fatal error: Uncaught exception %sAn other method (bar) with the same prototype already exists%s