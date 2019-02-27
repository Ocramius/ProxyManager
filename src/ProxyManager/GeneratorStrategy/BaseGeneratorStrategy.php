<?php

declare(strict_types=1);

namespace ProxyManager\GeneratorStrategy;

use Zend\Code\Generator\ClassGenerator;

/**
 * Generator strategy that generates the class body
 */
class BaseGeneratorStrategy implements GeneratorStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(ClassGenerator $classGenerator) : string
    {
        return $classGenerator->generate();
    }
}
