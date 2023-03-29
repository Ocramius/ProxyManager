<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\NullObject\MethodGenerator;

use Laminas\Code\Reflection\MethodReflection;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\IdentifierSuffixer;
use ReflectionNamedType;

use function in_array;

/**
 * Method decorator for null objects
 */
class NullObjectMethodInterceptor extends MethodGenerator
{
    /**
     * @return static
     */
    public static function generateMethod(MethodReflection $originalMethod): self
    {
        $method = static::fromReflectionWithoutBodyAndDocBlock($originalMethod);

        $returnType = $originalMethod->getReturnType();
        $nullCast   = $returnType instanceof ReflectionNamedType && ! $returnType->allowsNull() && in_array($returnType->getName(), ['array', 'float', 'int', 'string'], true)  ? '(' . $returnType->getName() . ') ' : '';

        if ($originalMethod->returnsReference() || $nullCast !== '') {
            $reference = IdentifierSuffixer::getIdentifier('ref');

            $method->setBody('$' . $reference . ' = ' . $nullCast . "null;\nreturn \$" . $reference . ';');
        }

        return $method;
    }
}
