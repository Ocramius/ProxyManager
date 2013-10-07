<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Factory\LazyLoadingGhostFactory;

class Foo
{
    private $foo;

    public function __construct()
    {
        sleep(5);
    }

    public function setFoo($foo)
    {
        $this->foo = (string) $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}

$startTime = microtime(true);
$factory   = new LazyLoadingGhostFactory();

for ($i = 0; $i < 1000; $i += 1) {
    $proxy = $factory->createProxy(
        'Foo',
        function ($proxy, $method, $parameters, & $initializer) {
            $initializer   = null;
            $proxy->setFoo('Hello World!');

            return true;
        }
    );
}

var_dump('time after 1000 instantiations: ' . (microtime(true) - $startTime));

echo $proxy->getFoo() . "\n";

var_dump('time after single call to doFoo: ' . (microtime(true) - $startTime));
