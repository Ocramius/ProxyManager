<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\ProxiedMethodReturnExpression;
use ReflectionClass;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\MethodReflection;
use function array_map;
use function array_values;
use function implode;
use function var_export;

/**
 * Method decorator for remote objects
 *
 */
class RemoteObjectMethod extends MethodGenerator
{
    /**
     *
     * @return self|static
     */
    public static function generateMethod(
        MethodReflection $originalMethod,
        PropertyGenerator $adapterProperty,
        ReflectionClass $originalClass
    ) : self {
        /** @var self $method */
        $method = static::fromReflectionWithoutBodyAndDocBlock($originalMethod);
        $list   = array_values(array_map(
            function (ParameterGenerator $parameter) : string {
                return '$' . $parameter->getName();
            },
            $method->getParameters()
        ));

        $method->setBody(
            '$return = $this->' . $adapterProperty->getName()
            . '->call(' . var_export($originalClass->getName(), true)
            . ', ' . var_export($originalMethod->getName(), true) . ', array(' . implode(', ', $list) . '));' . "\n\n"
            . ProxiedMethodReturnExpression::generate('$return', $originalMethod)
        );

        return $method;
    }
}
