<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;
use ReflectionClass;
use ReflectionProperty;
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
     * Constructor
     *
     * @param ReflectionClass   $originalClass
     * @param PropertyGenerator $valueHolder
     *
     * @return MethodGenerator
     */
    public static function generateMethod(ReflectionClass $originalClass, PropertyGenerator $valueHolder)
    {
        $originalConstructor = self::getConstructor($originalClass);

        $constructor = $originalConstructor
            ? self::fromReflection($originalConstructor)
            : new self('__construct');

        $constructor->setDocblock('{@inheritDoc}');
        $constructor->setBody(
            'static $reflection;' . "\n\n"
            . 'if (! $this->' . $valueHolder->getName() . ') {' . "\n"
            . '    $reflection = $reflection ?: new \ReflectionClass('
            . var_export($originalClass->getName(), true)
            . ");\n"
            . '    $this->' . $valueHolder->getName() . ' = $reflection->newInstanceWithoutConstructor();' . "\n"
            . self::getUnsetPropertiesString($originalClass)
            . "}\n\n"
            . '$this->' . $valueHolder->getName() . '->' . $constructor->getName() . '('
            . implode(
                ', ',
                array_map(
                    function (ParameterGenerator $parameter) {
                        return '$' . $parameter->getName();
                    },
                    $constructor->getParameters()
                )
            )
            . ');'
        );

        return $constructor;
    }

    /**
     * @param ReflectionClass $class
     *
     * @return string
     */
    private static function getUnsetPropertiesString(ReflectionClass $class)
    {
        $unsetProperties = implode(
            "\n    ",
            array_map(
                function (ReflectionProperty $unsetProperty) {
                    return 'unset($this->' . $unsetProperty->getName() . ');';
                },
                $class->getProperties(ReflectionProperty::IS_PUBLIC)
            )
        );

        return $unsetProperties ? "\n    " . $unsetProperties . "\n" : '';
    }

    /**
     * @param ReflectionClass $class
     *
     * @return MethodReflection|null
     */
    private static function getConstructor(ReflectionClass $class)
    {
        $constructors = array_map(
            function (\ReflectionMethod $method) {
                return new MethodReflection(
                    $method->getDeclaringClass()->getName(),
                    $method->getName()
                );
            },
            array_filter(
                $class->getMethods(),
                function (\ReflectionMethod $method) {
                    return $method->isConstructor();
                }
            )
        );

        return reset($constructors) ?: null;
    }
}
