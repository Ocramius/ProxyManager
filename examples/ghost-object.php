<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\GhostObjectInterface;

class Foo
{
    private string $foo = '';

    public function __construct()
    {
        // this will be completely skipped
        sleep(5);
    }

    public function setFoo($foo) : void
    {
        $this->foo = (string) $foo;
    }

    public function getFoo() : string
    {
        return $this->foo;
    }
}

$startTime = microtime(true);
$factory   = new LazyLoadingGhostFactory();

for ($i = 0; $i < 1000; $i += 1) {
    $proxy = $factory->createProxy(
        Foo::class,
        function (GhostObjectInterface $proxy, string $method, array $parameters, & $initializer, array $properties) {
            $initializer = null;

            $properties["\0Foo\0foo"] = 'Hello World!';

            return true;
        }
    );
}

var_dump('time after 1000 instantiations: ' . (microtime(true) - $startTime));

echo $proxy->getFoo() . "\n";

var_dump('time after single call to doFoo: ' . (microtime(true) - $startTime));
