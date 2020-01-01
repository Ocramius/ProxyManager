<?php

declare(strict_types=1);

namespace ProxyManager\GeneratorStrategy;

use Laminas\Code\Generator\ClassGenerator;

/**
 * Generator strategy interface - defines basic behavior of class generators
 */
interface GeneratorStrategyInterface
{
    /**
     * Generate the provided class
     */
    public function generate(ClassGenerator $classGenerator) : string;
}
