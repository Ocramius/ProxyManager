<?php

declare(strict_types=1);

use ProxyManager\Configuration;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;

require_once __DIR__ . '/../../vendor/autoload.php';

$configuration = new Configuration();

$configuration->setGeneratorStrategy(new EvaluatingGeneratorStrategy());
