<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use ReflectionClass;

/**
 * Base interface for proxy generators - describes how a proxy generator should use
 * reflection classes to modify given class generators
 */
interface ProxyGeneratorInterface
{
    /**
     * Apply modifications to the provided $classGenerator to proxy logic from $originalClass
     *
     * Due to BC compliance, we cannot add a native `: void` return type declaration here
     *
     * phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @return void
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator);
}
