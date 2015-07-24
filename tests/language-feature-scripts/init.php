<?php

use ProxyManager\Configuration;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;

require_once __DIR__ . '/../../vendor/autoload.php';

$configuration = new Configuration();

$locator = new \ProxyManager\FileLocator\FileLocator(__DIR__ . '/../../tmp');

$configuration->setProxyAutoloader(
    new \ProxyManager\Autoloader\Autoloader($locator, $configuration->getClassNameInflector())
);
$configuration->setGeneratorStrategy(
    new \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy(
        new \ProxyManager\FileLocator\FileLocator(__DIR__ . '/../../tmp')
    )
);
