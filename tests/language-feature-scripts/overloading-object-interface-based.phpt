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

interface FooInterface
{
    public function bar();
    
    public function baz($string);
}

class Foo implements FooInterface
{
    public function bar()
    {
        return 'default';
    }
    
    public function baz($string)
    {
        return 'baz' . $string;
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

$proxy->overload('baz', function() { return 'baz default'; });
$proxy->overload('baz', function($string, $otherString) { return $string . $otherString; });

var_dump($proxy->baz('!'));
var_dump($proxy->baz('bazzz', '!'));
var_dump($proxy->baz());

?>
--EXPECT--
string(7) "default"
string(4) "test"
string(10) "baz class!"
string(4) "baz!"
string(6) "bazzz!"
string(11) "baz default"