<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\ProxiedMethodReturnExpression;
use ReflectionClass;

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

    /** @return static */
    public static function generateMethod(
        MethodReflection $originalMethod,
        PropertyGenerator $adapterProperty,
        ReflectionClass $originalClass
    ): self {
        $method        = static::fromReflectionWithoutBodyAndDocBlock($originalMethod);
        $proxiedReturn = '$return = $this->' . $adapterProperty->getName()
            . '->call(' . var_export($originalClass->getName(), true)
            . ', ' . var_export($originalMethod->getName(), true) . ', $args);' . "\n\n"
            . ProxiedMethodReturnExpression::generate('$return', $originalMethod);

        $defaultValues          = self::getDefaultValuesForMethod($originalMethod);
        $declaredParameterCount = count($originalMethod->getParameters());

        $method->setBody(
            strtr(
                self::TEMPLATE,
                [
                    '#PROXIED_RETURN#' => $proxiedReturn,
                    '#DEFAULT_VALUES#' => var_export($defaultValues, true),
                    '#PARAMETER_COUNT#' => var_export($declaredParameterCount, true),
                ]
            )
        );

        return $method;
    }

    /** @psalm-return list<int|float|bool|array|string|null> */
    private static function getDefaultValuesForMethod(MethodReflection $originalMethod): array
    {
        $defaultValues = [];
        foreach ($originalMethod->getParameters() as $parameter) {
            if ($parameter->isOptional() && $parameter->isDefaultValueAvailable()) {
                /** @psalm-var int|float|bool|array|string|null */
                $defaultValues[] = $parameter->getDefaultValue();
                continue;
            }

            if ($parameter->isVariadic()) {
                continue;
            }

            $defaultValues[] = null;
        }

        return $defaultValues;
    }
}
