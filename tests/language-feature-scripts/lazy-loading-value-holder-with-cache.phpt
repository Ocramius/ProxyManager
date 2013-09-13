--TEST--
Verifies that lazy loading value holder proxy file is generated
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweets = 'candy';
}

$configuration->setProxiesTargetDir(__DIR__ . '/cache');
$fileLocator = new \ProxyManager\FileLocator\FileLocator($configuration->getProxiesTargetDir());
$configuration->setGeneratorStrategy(
    new \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy($fileLocator)
);

$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);

$proxy = $factory->createProxy('Kitchen', function (& $wrapped, $proxy, $method, array $parameters, & $initializer) {
    $initializer = null;
    $wrapped     = new Kitchen();
});

$filename = $fileLocator->getProxyFileName(get_class($proxy));
var_dump(file_exists($filename));

$proxy = $factory->createProxy('Kitchen', function (& $wrapped, $proxy, $method, array $parameters, & $initializer) {
    $initializer = null;
    $wrapped     = new Kitchen();
});

var_dump(file_exists($filename));
@unlink($filename);

?>
--EXPECT--
bool(true)
bool(true)