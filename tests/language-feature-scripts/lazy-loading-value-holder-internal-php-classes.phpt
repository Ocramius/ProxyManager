--TEST--
Verifies that lazy loading value holder factory can generate proxy for PHP core classes.
--SKIPIF--
<?php
if (PHP_VERSION_ID < 50600) {
    echo 'skip PHP 5.6+ is needed to instantiated internal php classes via reflection';
}
?>
--FILE--
<?php

require_once __DIR__ . '/init.php';

class PharMock extends Phar
{
    public function __construct()
    {
    }

    public function compress($compression_type, $file_ext = null)
    {
        echo $compression_type;
    }
}

$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$factory
    ->createProxy(Phar::class, function (& $wrapped, $proxy, $method, array $parameters, & $initializer) {
        $initializer = null;
        $wrapped     = new PharMock();
    })
    ->compress('Lazy Loaded!');

?>
--EXPECT--
Lazy Loaded!