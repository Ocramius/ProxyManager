<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\NullObject\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Method decorator for null objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class NullObjectMethodInterceptor extends MethodGenerator
{
    /**
     * @param \Zend\Code\Reflection\MethodReflection $originalMethod
     *
     * @return self|static
     */
    public static function generateMethod(MethodReflection $originalMethod) : self
    {
        /* @var $method self */
        $method = static::fromReflection($originalMethod);

        if ('void' === (string) $originalMethod->getReturnType()) {
            $method->setBody('');

            return $method;
        }

        if ($originalMethod->returnsReference()) {
            $reference = UniqueIdentifierGenerator::getIdentifier('ref');

            $method->setBody("\$$reference = null;\nreturn \$$reference;");

            return $method;
        }

        $method->setBody('');

        return $method;
    }
}
