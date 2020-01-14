<?php

declare(strict_types=1);

namespace ProxyManager\Example\GhostObject;

use Closure;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\GhostObjectInterface;

require_once __DIR__ . '/../vendor/autoload.php';

class Foo
{
    private string $foo = '';

    public function __construct()
    {
        // this will be completely skipped
        sleep(5);
    }

    public function setFoo(string $foo) : void
    {
        $this->foo = $foo;
    }

    public function getFoo() : string
    {
        return $this->foo;
    }
}

(static function () : void {
    $startTime = microtime(true);
    $factory   = new LazyLoadingGhostFactory();
    $i         = 0;

    do {
        $proxy = $factory->createProxy(
            Foo::class,
            function (
                GhostObjectInterface $proxy,
                string $method,
                array $parameters,
                ?Closure & $initializer,
                array $properties
            ) : bool {
                $initializer = null;

                $properties["\0ProxyManager\\Example\\GhostObject\\Foo\0foo"] = 'Hello World!';

                return true;
            }
        );

        $i += 1;
    } while ($i < 1000);

    var_dump('time after 1000 instantiations: ' . (microtime(true) - $startTime));

    echo $proxy->getFoo() . "\n";

    var_dump('time after single call to doFoo: ' . (microtime(true) - $startTime));
})();
