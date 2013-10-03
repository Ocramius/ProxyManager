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

try {
    $proxy->overload('bar', function($string) { return $string; });
    $proxy->overload('bar', function($otherString) { return $otherString; });
} catch(\Exception $e) {
    var_dump($e->getMessage());
}

?>
--EXPECT--
string(60) "An other method (bar) with the same prototype already exists"