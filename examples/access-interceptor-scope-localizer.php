<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Configuration;
use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;

class Foo
{
    public $counter = 0;

    public function doFoo()
    {
        $this->counter += 1;

        return $this;
    }
}

$config = new Configuration();
$factory = new AccessInterceptorScopeLocalizerFactory($config);

$proxy = $factory->createProxy(
    new Foo(),
    array('doFoo' => function ($proxy) { echo "pre-foo #{$proxy->counter}!\n"; }),
    array('doFoo' => function ($proxy) { echo "post-foo #{$proxy->counter}!\n"; })
);

$proxy->doFoo()->doFoo()->doFoo()->doFoo();
