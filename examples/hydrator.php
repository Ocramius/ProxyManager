<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Configuration;
use ProxyManager\Factory\HydratorFactory;

class Foo
{
    private $foo   = 1;
    protected $bar = 2;
    public $baz    = 3;
}

$config    = new Configuration();
$factory   = new HydratorFactory($config);
$hydrator  = $factory->createProxy('Foo');
$foo       = new Foo();

var_dump('Extracted data:', $hydrator->extract($foo)); // array('foo' => 1, 'bar' => 2, 'baz' => 3);

$hydrator->hydrate(
    array(
         'foo' => 4,
         'bar' => 5,
         'baz' => 6
    ),
    $foo
);

var_dump('Object hydrated with new data:', $foo); // the object with the new properties set
