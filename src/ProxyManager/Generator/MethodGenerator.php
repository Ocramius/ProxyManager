<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator as LaminasMethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Reflection\MethodReflection;

/**
 * Method generator that fixes minor quirks in ZF2's method generator
 */
class MethodGenerator extends LaminasMethodGenerator
{
    /**
     * @return static
     */
    public static function fromReflectionWithoutBodyAndDocBlock(MethodReflection $reflectionMethod): self
    {
        /** @var static $method */
        $method = static::copyMethodSignature($reflectionMethod);

        $method->setInterface(false);
        $method->setBody('');

        return $method;
    }

    public static function copyMethodSignature(MethodReflection $reflectionMethod): parent
    {
        $method = parent::copyMethodSignature($reflectionMethod);

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $method->setParameter(
                ParameterGenerator::fromReflection($reflectionParameter)
            );
        }

        return $method;
    }

    /**
     * {@inheritDoc} override needed to specify type in more detail
     */
    public function getDocBlock(): ?DocBlockGenerator
    {
        return parent::getDocBlock();
    }

    /**
     * {@inheritDoc} override needed to specify type in more detail
     */
    public function getSourceContent(): ?string
    {
        return parent::getSourceContent();
    }
}
