<?php
/**
 * This example demonstrates how an access interceptor scope localizer
 * (which is a specific type of smart reference) is safe to use to
 * proxy fluent interfaces.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;

class FluentCounter
{
    public $counter = 0;

    /** @return FluentCounter */
    public function fluentMethod()
    {
        $this->counter += 1;

        return $this;
    }
}

$factory = new AccessInterceptorScopeLocalizerFactory();
$foo = new FluentCounter();

/* @var $proxy FluentCounter */
$proxy = $factory->createProxy(
    $foo,
    array('fluentMethod' => function ($proxy) { echo "pre-fluentMethod #{$proxy->counter}!\n"; }),
    array('fluentMethod' => function ($proxy) { echo "post-fluentMethod #{$proxy->counter}!\n"; })
);

$proxy->fluentMethod()->fluentMethod()->fluentMethod()->fluentMethod();

echo 'The proxy counter is now at ' . $proxy->counter . "\n";
echo 'The real instance counter is now at ' . $foo->counter . "\n";
