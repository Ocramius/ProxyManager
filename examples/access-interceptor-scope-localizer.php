<?php
/**
 * This example demonstrates how an access interceptor scope localizer
 * (which is a specific type of smart reference) is safe to use to
 * proxy fluent interfaces.
 */

declare(strict_types=1);

namespace ProxyManager\Example\AccessInterceptorScopeLocalizer;

use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;
use ProxyManager\Proxy\AccessInterceptorInterface;

require_once __DIR__ . '/../vendor/autoload.php';

class FluentCounter
{
    public int $counter = 0;

    public function fluentMethod() : self
    {
        $this->counter += 1;

        return $this;
    }
}

(static function () : void {
    $factory = new AccessInterceptorScopeLocalizerFactory();
    $foo     = new FluentCounter();
    $proxy   = $factory->createProxy(
        $foo,
        [
            'fluentMethod' => static function (AccessInterceptorInterface $proxy, FluentCounter $realInstance) : void {
                echo "pre-fluentMethod #{$realInstance->counter}!\n";
            },
        ],
        [
            'fluentMethod' => static function (AccessInterceptorInterface $proxy, FluentCounter $realInstance) : void {
                echo "post-fluentMethod #{$realInstance->counter}!\n";
            },
        ]
    );

    $proxy->fluentMethod()->fluentMethod()->fluentMethod()->fluentMethod();

    echo 'The proxy counter is now at ' . $proxy->counter . "\n";
    echo 'The real instance counter is now at ' . $foo->counter . "\n";
})();
