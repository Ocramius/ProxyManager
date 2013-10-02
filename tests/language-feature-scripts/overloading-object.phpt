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
$proxy->overload('bar', function($string) { return $string; });
$proxy->overload('bar', function(Baz $b, $string) { return $b . $string; });

var_dump($proxy->bar());
var_dump($proxy->bar('test'));
var_dump($proxy->bar(new Baz(), '!'));
?>
--EXPECT--
string(7) "default"
string(4) "test"
string(10) "baz class!"
