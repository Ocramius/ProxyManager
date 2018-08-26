<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use Zend\Code\Generator\MethodGenerator as ZendMethodGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Method generator that fixes minor quirks in ZF2's method generator
 *
 * @method \Zend\Code\Generator\DocBlockGenerator|null getDocBlock()
 * @method string|null getSourceContent()
 */
class MethodGenerator extends ZendMethodGenerator
{
    /**
     * {@inheritDoc}
     */
    public static function fromReflectionWithoutBodyAndDocBlock(MethodReflection $reflectionMethod) : self
    {
        /** @var self $method */
        $method = parent::copyMethodSignature($reflectionMethod);

        $method->setInterface(false);
        $method->setBody('');

        return $method;
    }
}
