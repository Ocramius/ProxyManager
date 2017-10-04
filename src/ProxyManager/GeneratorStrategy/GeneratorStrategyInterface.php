<?php

declare(strict_types=1);

namespace ProxyManager\GeneratorStrategy;

use Zend\Code\Generator\ClassGenerator;

/**
 * Generator strategy interface - defines basic behavior of class generators
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
interface GeneratorStrategyInterface
{
    /**
     * Generate the provided class
     *
     * @param ClassGenerator $classGenerator
     *
     * @return string the class body
     */
    public function generate(ClassGenerator $classGenerator) : string;
}
