--TEST--
Verifies that lazy loading value holder factory can generate proxy for PHP core classes.
?>
--FILE--
<?php
error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__ . '/init.php';

class PharMock extends Phar
{
    public function __construct()
    {
    }

    #[\ReturnTypeWillChange]
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
    ->compress(123);

?>
--EXPECT--
123
