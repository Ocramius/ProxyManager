<?php

declare(strict_types=1);

namespace ProxyManager\Signature;

use Zend\Code\Generator\ClassGenerator;

/**
 * Applies a signature to a given class generator
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
interface ClassSignatureGeneratorInterface
{
    /**
     * Applies a signature to a given class generator
     *
     * @param ClassGenerator $classGenerator
     * @param array          $parameters
     *
     * @return ClassGenerator
     */
    public function addSignature(ClassGenerator $classGenerator, array $parameters) : ClassGenerator;
}
