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

$factory = new OverloadingFactory();

// create proxy with some additional methods
$proxy = $factory->createProxy(new Foo(), array(
    'bar' => array(
        function($string) { return $string; },
        function(\stdClass $std) { return $std->string; }
    ),
));

// be careful, methods adding in live is slower
$factory->createProxyMethods($proxy, array(
    'bar' => function($string, $otherString) { return $string . $otherString; },
));

echo "#1: " . $proxy->bar('foo') . "\n"; // 'foo'
echo "#2: " . $proxy->bar('foo', 'bar') . "\n"; // 'foobar'

echo "Proxy documentation :\n\n" . $factory->createProxyDocumentation($proxy);
