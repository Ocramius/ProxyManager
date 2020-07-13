<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Util\InterceptorGenerator;

use function implode;

/**
 * Method with additional pre- and post- interceptor logic in the body
 */
class InterceptedMethod extends MethodGenerator
{
    /**
     * @throws InvalidArgumentException
     */
    public static function generateMethod(
        MethodReflection $originalMethod,
        PropertyGenerator $valueHolderProperty,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ): self {
        $method          = static::fromReflectionWithoutBodyAndDocBlock($originalMethod);
        $forwardedParams = [];

        foreach ($originalMethod->getParameters() as $parameter) {
            $forwardedParams[] = ($parameter->isVariadic() ? '...' : '') . '$' . $parameter->getName();
        }

        $method->setBody(InterceptorGenerator::createInterceptedMethodBody(
            '$returnValue = $this->' . $valueHolderProperty->getName() . '->'
            . $originalMethod->getName() . '(' . implode(', ', $forwardedParams) . ');',
            $method,
            $valueHolderProperty,
            $prefixInterceptors,
            $suffixInterceptors,
            $originalMethod
        ));

        return $method;
    }
}
