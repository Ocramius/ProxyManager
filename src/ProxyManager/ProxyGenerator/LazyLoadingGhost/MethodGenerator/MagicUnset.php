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

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use ProxyManager\Generator\MagicMethodGenerator;
use ProxyManager\Generator\Util\ProxiedMethodReturnExpression;
use Zend\Code\Generator\ParameterGenerator;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Magic `__unset` method for lazy loading ghost objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MagicUnset extends MagicMethodGenerator
{
    /**
     * @var string
     */
    private $callParentTemplate = <<<'PHP'
%init

if (isset(self::$%publicProperties[$name])) {
    unset($this->$name);

    %voidReturn1
}

if (isset(self::$%protectedProperties1[$name])) {
    // check protected property access via compatible class
    $callers      = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller       = isset($callers[1]) ? $callers[1] : [];
    $object       = isset($caller['object']) ? $caller['object'] : '';
    $expectedType = self::$%protectedProperties2[$name];

    if ($object instanceof $expectedType) {
        unset($this->$name);

        %voidReturn2
    }

    $class = isset($caller['class']) ? $caller['class'] : '';

    if ($class === $expectedType || is_subclass_of($class, $expectedType) || $class === 'ReflectionProperty') {
        unset($this->$name);

        %voidReturn3
    }
} elseif (isset(self::$%privateProperties1[$name])) {
    // check private property access via same class
    $callers = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller  = isset($callers[1]) ? $callers[1] : [];
    $class   = isset($caller['class']) ? $caller['class'] : '';

    static $accessorCache = [];

    if (isset(self::$%privateProperties2[$name][$class])) {
        $cacheKey = $class . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(function ($instance) use ($name) {
                unset($instance->$name);
            }, null, $class);

        %accessorReturn1
    }

    if ('ReflectionProperty' === $class) {
        $tmpClass = key(self::$%privateProperties3[$name]);
        $cacheKey = $tmpClass . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(function ($instance) use ($name) {
                unset($instance->$name);
            }, null, $tmpClass);

        %accessorReturn2
    }
}

%parentAccess
PHP;

    /**
     * @param ReflectionClass        $originalClass
     * @param PropertyGenerator      $initializerProperty
     * @param MethodGenerator        $callInitializer
     * @param PublicPropertiesMap    $publicProperties
     * @param ProtectedPropertiesMap $protectedProperties
     * @param PrivatePropertiesMap   $privateProperties
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        MethodGenerator $callInitializer,
        PublicPropertiesMap $publicProperties,
        ProtectedPropertiesMap $protectedProperties,
        PrivatePropertiesMap $privateProperties
    ) {
        parent::__construct($originalClass, '__unset', [new ParameterGenerator('name')]);

        $existingMethod = $originalClass->hasMethod('__unset') ? $originalClass->getMethod('__unset') : null;

        $this->setDocBlock(($existingMethod ? "{@inheritDoc}\n" : '') . '@param string $name');

        $parentAccess = $existingMethod
            ? ProxiedMethodReturnExpression::generate('parent::__unset($name)', $existingMethod)
            : PublicScopeSimulator::getPublicAccessSimulationCode(
                $existingMethod,
                PublicScopeSimulator::OPERATION_UNSET,
                'name'
            );

        $symbols = [
            '%init'                 => '$this->' . $initializerProperty->getName()
                . ' && $this->' . $callInitializer->getName()
                . '(\'__unset\', array(\'name\' => $name));',
            '%publicProperties'     => $publicProperties->getName(),
            '%protectedProperties1' => $protectedProperties->getName(),
            '%protectedProperties2' => $protectedProperties->getName(),
            '%privateProperties1'   => $privateProperties->getName(),
            '%privateProperties2'   => $privateProperties->getName(),
            '%privateProperties3'   => $privateProperties->getName(),
            '%parentAccess'         => $parentAccess,
            '%voidReturn1'          => ProxiedMethodReturnExpression::generate('true', $existingMethod),
            '%voidReturn2'          => ProxiedMethodReturnExpression::generate('true', $existingMethod),
            '%voidReturn3'          => ProxiedMethodReturnExpression::generate('true', $existingMethod),
            '%accessorReturn1'      => ProxiedMethodReturnExpression::generate('$accessor($this)', $existingMethod),
            '%accessorReturn2'      => ProxiedMethodReturnExpression::generate('$accessor($this)', $existingMethod),
        ];

        $this->setBody(str_replace(
            array_keys($symbols),
            array_values($symbols),
            $this->callParentTemplate
        ));
    }
}
