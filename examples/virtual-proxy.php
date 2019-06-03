<?php

declare(strict_types=1);

namespace ProxyManager\Example\VirtualProxy;

use Closure;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

require_once __DIR__ . '/../vendor/autoload.php';

class Foo
{
    public function __construct()
    {
        sleep(1);
    }

    public function doFoo() : void
    {
        echo 'Foo!';
    }
}

(static function () : void {
    $startTime = microtime(true);
    $factory   = new LazyLoadingValueHolderFactory();
    $i         = 0;

    do {
        $proxy = $factory->createProxy(
            Foo::class,
            static function (
                ?object & $wrappedObject, ?object $proxy, string $method, array $parameters, ?Closure & $initializer
            ) {
                $initializer = null;
                $wrappedObject = new Foo();

                return true;
            }
        );

        $i += 1;
    } while ($i < 1000);

    var_dump('time after 1000 instantiations: ' . (microtime(true) - $startTime));

    $proxy->doFoo();

    var_dump('time after single call to doFoo: ' . (microtime(true) - $startTime));
})();
