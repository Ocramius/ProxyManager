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
$proxy = $factory->createProxy($foo, array(
    array('bar' => function($string) { return $string; }),
    array('bar' => function(\stdClass $std) { return $std->string; }),
));
    
$proxy = $factory->createProxy($foo);

// be careful, methods adding in live is slower
$factory->createProxyMethods($proxy, array(
    array('bar' => function($string, $otherString) { return $string . $otherString; }),
));

echo "#1: " . $proxy->bar('foo') . "\n"; // 'foo'
echo "#2: " . $proxy->bar('foo', 'bar') . "\n"; // 'foobar'

echo "Proxy documentation :\n\n" . $factory->createProxyDocumentation($proxy);
