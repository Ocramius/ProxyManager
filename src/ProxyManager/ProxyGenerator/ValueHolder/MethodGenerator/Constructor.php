<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator;
use ReflectionClass;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * The `__construct` implementation for lazy loading proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class Constructor extends MethodGenerator
{
    /**
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     */
    public static function generateMethod(ReflectionClass $originalClass, PropertyGenerator $valueHolder) : self
    {
        $originalConstructor = self::getConstructor($originalClass);

        /* @var $constructor self */
        $constructor = $originalConstructor
            ? self::fromReflection($originalConstructor)
            : new self('__construct');

        $constructor->setBody(
            'static $reflection;' . "\n\n"
            . 'if (! $this->' . $valueHolder->getName() . ') {' . "\n"
            . '    $reflection = $reflection ?: new \ReflectionClass('
            . var_export($originalClass->getName(), true)
            . ");\n"
            . '    $this->' . $valueHolder->getName() . ' = $reflection->newInstanceWithoutConstructor();' . "\n"
            . UnsetPropertiesGenerator::generateSnippet(Properties::fromReflectionClass($originalClass), 'this')
            . '}'
            . self::generateOriginalConstructorCall($originalClass, $valueHolder)
        );

        return $constructor;
    }

    private static function generateOriginalConstructorCall(
        ReflectionClass $class,
        PropertyGenerator $valueHolder
    ) : string {
        $originalConstructor = self::getConstructor($class);

        if (! $originalConstructor) {
            return '';
        }

        $constructor = self::fromReflection($originalConstructor);

        return "\n\n"
            . '$this->' . $valueHolder->getName() . '->' . $constructor->getName() . '('
            . implode(
                ', ',
                array_map(
                    function (ParameterGenerator $parameter) : string {
                        return ($parameter->getVariadic() ? '...' : '') . '$' . $parameter->getName();
                    },
                    $constructor->getParameters()
                )
            )
            . ');';
    }

    /**
     * @param ReflectionClass $class
     *
     * @return MethodReflection|null
     */
    private static function getConstructor(ReflectionClass $class)
    {
        $constructors = array_map(
            function (\ReflectionMethod $method) : MethodReflection {
                return new MethodReflection(
                    $method->getDeclaringClass()->getName(),
                    $method->getName()
                );
            },
            array_filter(
                $class->getMethods(),
                function (\ReflectionMethod $method) : bool {
                    return $method->isConstructor();
                }
            )
        );

        return reset($constructors) ?: null;
    }
}
