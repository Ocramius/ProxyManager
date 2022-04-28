<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\ProxiedMethodReturnExpression;
use ProxyManager\Generator\ValueGenerator;
use ReflectionClass;

use function sprintf;
use function strtr;
use function var_export;

/**
 * Method decorator for remote objects
 */
class RemoteObjectMethod extends MethodGenerator
{
    private const TEMPLATE
        = <<<'PHP'
$args = \func_get_args();

switch (\func_num_args()) {#DEFAULT_VALUES#
}

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

        $defaultValues = self::getDefaultValuesForMethod($originalMethod);

        $method->setBody(
            strtr(
                self::TEMPLATE,
                [
                    '#PROXIED_RETURN#' => $proxiedReturn,
                    '#DEFAULT_VALUES#' => $defaultValues,
                ]
            )
        );

        return $method;
    }

    private static function getDefaultValuesForMethod(MethodReflection $originalMethod): string
    {
        $defaultValues = '';
        foreach ($originalMethod->getParameters() as $i => $parameter) {
            if ($parameter->isOptional() && $parameter->isDefaultValueAvailable()) {
                $default        = new ValueGenerator($parameter->getDefaultValue(), $parameter);
                $defaultValues .= sprintf("\n    case %d: \$args[] = %s;", $i, $default->generate());
                continue;
            }

            if ($parameter->isVariadic()) {
                continue;
            }

            $defaultValues .= sprintf("\n    case %d: \$args[] = null;", $i);
        }

        return $defaultValues;
    }
}
