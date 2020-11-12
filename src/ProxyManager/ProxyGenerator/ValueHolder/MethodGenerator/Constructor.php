<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\MethodReflection;
use Laminas\Code\Reflection\ParameterReflection;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator;
use ReflectionClass;
use ReflectionMethod;

use function array_filter;
use function array_map;
use function assert;
use function implode;
use function reset;
use function var_export;

/**
 * The `__construct` implementation for lazy loading proxies
 */
class Constructor extends MethodGenerator
{
    /**
     * @throws InvalidArgumentException
     */
    public static function generateMethod(ReflectionClass $originalClass, PropertyGenerator $valueHolder): self
    {
        $originalConstructor = self::getConstructor($originalClass);

        $constructor = $originalConstructor
            ? self::fromReflectionWithoutBodyAndDocBlock($originalConstructor)
            : new self('__construct');

        $constructor->setBody(
            'static $reflection;' . "\n\n"
            . 'if (! $this->' . $valueHolder->getName() . ') {' . "\n"
            . '    $reflection = $reflection ?? new \ReflectionClass('
            . var_export($originalClass->getName(), true)
            . ");\n"
            . '    $this->' . $valueHolder->getName() . ' = $reflection->newInstanceWithoutConstructor();' . "\n"
            . UnsetPropertiesGenerator::generateSnippet(Properties::fromReflectionClass($originalClass), 'this')
            . '}'
            . ($originalConstructor ? self::generateOriginalConstructorCall($originalConstructor, $valueHolder) : '')
        );

        return $constructor;
    }

    private static function generateOriginalConstructorCall(
        MethodReflection $originalConstructor,
        PropertyGenerator $valueHolder
    ): string {
        return "\n\n"
            . '$this->' . $valueHolder->getName() . '->' . $originalConstructor->getName() . '('
            . implode(
                ', ',
                array_map(
                    static function (ParameterReflection $parameter): string {
                        return ($parameter->isVariadic() ? '...' : '') . '$' . $parameter->getName();
                    },
                    $originalConstructor->getParameters()
                )
            )
            . ');';
    }

    private static function getConstructor(ReflectionClass $class): ?MethodReflection
    {
        $constructors = array_map(
            static function (ReflectionMethod $method): MethodReflection {
                return new MethodReflection(
                    $method->getDeclaringClass()->getName(),
                    $method->getName()
                );
            },
            array_filter(
                $class->getMethods(),
                static function (ReflectionMethod $method): bool {
                    return $method->isConstructor();
                }
            )
        );

        return reset($constructors) ?: null;
    }
}
