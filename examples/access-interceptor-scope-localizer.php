<?php
/**
 * This example demonstrates how an access interceptor scope localizer
 * (which is a specific type of smart reference) is safe to use to
 * proxy fluent interfaces.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;

class FluentCounter
{
    public $counter = 0;

    public function fluentMethod() : self
    {
        $this->counter += 1;

        return $this;
    }
}

$factory = new AccessInterceptorScopeLocalizerFactory();
$foo     = new FluentCounter();

/** @var FluentCounter $proxy */
$proxy = $factory->createProxy(
    $foo,
    [
        'fluentMethod' => function ($proxy) : void {
            echo "pre-fluentMethod #{$proxy->counter}!\n";
        },
    ],
    [
        'fluentMethod' => function ($proxy) : void {
            echo "post-fluentMethod #{$proxy->counter}!\n";
        },
    ]
);

$proxy->fluentMethod()->fluentMethod()->fluentMethod()->fluentMethod();

echo 'The proxy counter is now at ' . $proxy->counter . "\n";
echo 'The real instance counter is now at ' . $foo->counter . "\n";
