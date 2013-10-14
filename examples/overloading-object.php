<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Factory\OverloadingFactory;

class Foo
{
    public $myPublicProperty = 'propertyDefault';
    
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
    'foo' => array(
        function($string) { return 'foo' . $string; },
    ),
    'property' => array(
        function($name) { return $this->$name; }
    ),
));

// be careful, methods adding in live is slower
$factory->createProxyMethods($proxy, array(
    'bar' => function($string, $otherString) { return $string . $otherString; },
));

echo "#1: " . $proxy->bar('foo') . "\n"; // 'foo'
echo "#2: " . $proxy->bar('foo', 'bar') . "\n"; // 'foobar'
echo "#3: " . $proxy->property('myPublicProperty') . "\n"; // 'propertyDefault'

echo "Proxy documentation :\n\n" . $factory->createProxyDocumentation($proxy);
