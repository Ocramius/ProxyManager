<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\NullObject\MethodGenerator;

use Laminas\Code\Reflection\MethodReflection;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\IdentifierSuffixer;
use ReflectionNamedType;

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
        $method             = static::fromReflectionWithoutBodyAndDocBlock($originalMethod);
        $originalReturnType = $originalMethod->getReturnType();

        if ($originalReturnType instanceof ReflectionNamedType && $originalReturnType->getName() === 'never') {
            $method->setBody('throw new \Exception();');
        } elseif ($originalMethod->returnsReference()) {
            $reference = IdentifierSuffixer::getIdentifier('ref');

            $method->setBody("\$reference = null;\nreturn \$" . $reference . ';');
        }

        return $method;
    }
}
