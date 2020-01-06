<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\ProxiedMethodReturnExpression;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\MethodReflection;
use function count;
use function strtr;
use function var_export;

/**
 * Method decorator for remote objects
 */
class RemoteObjectMethod extends MethodGenerator
{
    private const TEMPLATE
        = <<<'PHP'
$defaultValues = #DEFAULT_VALUES#;
$declaredParameterCount = #PARAMETER_COUNT#;

$args = \func_get_args() + $defaultValues;

#PROXIED_RETURN#
PHP;

    /** @return self|static */
    public static function generateMethod(
        MethodReflection $originalMethod,
        PropertyGenerator $adapterProperty,
        ReflectionClass $originalClass
    ) : self {
        $defaultValues = [];
        foreach ($originalMethod->getParameters() as $parameter) {
            if ($parameter->isOptional() && $parameter->isDefaultValueAvailable()) {
                $defaultValues[] = $parameter->getDefaultValue();
                continue;
            }

            if ($parameter->isVariadic()) {
                continue;
            }

            $defaultValues[] = null;
        }
        $declaredParameterCount = count($originalMethod->getParameters());

        /** @var self $method */
        $method        = static::fromReflectionWithoutBodyAndDocBlock($originalMethod);
        $proxiedReturn = '$return = $this->' . $adapterProperty->getName()
            . '->call(' . var_export($originalClass->getName(), true)
            . ', ' . var_export($originalMethod->getName(), true) . ', $args);' . "\n\n"
            . ProxiedMethodReturnExpression::generate('$return', $originalMethod);

        $body = strtr(
            self::TEMPLATE,
            [
                '#PROXIED_RETURN#' => $proxiedReturn,
                '#DEFAULT_VALUES#' => var_export($defaultValues, true),
                '#PARAMETER_COUNT#' => var_export($declaredParameterCount, true),
            ]
        );

        $method->setBody(
            $body
        );

        return $method;
    }
}
