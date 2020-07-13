<?php

declare(strict_types=1);

namespace ProxyManager\GeneratorStrategy;

use Laminas\Code\Generator\ClassGenerator;

/**
 * Generator strategy that generates the class body
 */
class BaseGeneratorStrategy implements GeneratorStrategyInterface
{
    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MixedInferredReturnType upstream has no declared type
     */
    public function generate(ClassGenerator $classGenerator): string
    {
        /** @psalm-suppress MixedReturnStatement upstream has no declared type */
        return $classGenerator->generate();
    }
}
