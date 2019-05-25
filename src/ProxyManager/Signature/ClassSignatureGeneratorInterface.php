<?php

declare(strict_types=1);

namespace ProxyManager\Signature;

use Zend\Code\Generator\ClassGenerator;

/**
 * Applies a signature to a given class generator
 */
interface ClassSignatureGeneratorInterface
{
    /**
     * Applies a signature to a given class generator
     *
     * @param array<string, mixed> $parameters
     */
    public function addSignature(ClassGenerator $classGenerator, array $parameters) : ClassGenerator;
}
