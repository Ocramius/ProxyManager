<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Factory\OverloadingFactory;

class Foo
{
    public function bar()
    {
        return 'default';
    }
}

$foo = new Foo();

$factory = new OverloadingFactory();
$proxy = $factory->createProxy($foo);
$proxy->overload('bar', function($string) { return $string; });

echo $proxy->bar('foo'); // 'foo'

echo $factory->createProxyDocumentation($proxy); // get the overloaded class documentation
