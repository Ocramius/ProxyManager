<?php

use ProxyManager\Configuration;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;

require_once __DIR__ . '/../Bootstrap.php';

$configuration = new Configuration();

$configuration->setGeneratorStrategy(new EvaluatingGeneratorStrategy());
