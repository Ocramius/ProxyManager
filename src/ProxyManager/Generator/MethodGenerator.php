<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use Zend\Code\Generator\MethodGenerator as ZendMethodGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Method generator that fixes minor quirks in ZF2's method generator
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * Note: following overrides are just fixing some "lies" in the documented types in zend-code
 *
 * @method \Zend\Code\Generator\DocBlockGenerator|null getDocBlock()
 * @method string|null getBody()
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

        return $method;
    }
}
