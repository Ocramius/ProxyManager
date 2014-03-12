--TEST--
Verifies that lazy loading value holder factory can generate proxy for PHP core classes.
--FILE--
<?php

require_once __DIR__ . '/init.php';

$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$factory
    ->createProxy('PDO', function () {
        die('Lazy Loaded!');
    })
    ->quote();

?>
--EXPECT--
Lazy Loaded!