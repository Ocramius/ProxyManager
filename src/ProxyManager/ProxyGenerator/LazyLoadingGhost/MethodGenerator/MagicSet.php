<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MagicMethodGenerator;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;

use function sprintf;

/**
 * Magic `__set` for lazy loading ghost objects
 */
class MagicSet extends MagicMethodGenerator
{
    private string $callParentTemplate = <<<'PHP'
%s

if (isset(self::$%s[$name])) {
    return ($this->$name = $value);
}

if (isset(self::$%s[$name])) {
    // check protected property access via compatible class
    $callers      = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller       = isset($callers[1]) ? $callers[1] : [];
    $object       = isset($caller['object']) ? $caller['object'] : '';
    $expectedType = self::$%s[$name];

    if ($object instanceof $expectedType) {
        return ($this->$name = $value);
    }

    $class = isset($caller['class']) ? $caller['class'] : '';

    if ($class === $expectedType || is_subclass_of($class, $expectedType) || $class === 'ReflectionProperty') {
        return ($this->$name = $value);
    }
} elseif (isset(self::$%s[$name])) {
    // check private property access via same class
    $callers = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller  = isset($callers[1]) ? $callers[1] : [];
    $class   = isset($caller['class']) ? $caller['class'] : '';

    static $accessorCache = [];

    if (isset(self::$%s[$name][$class])) {
        $cacheKey = $class . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(static function ($instance, $value) use ($name) {
                return ($instance->$name = $value);
            }, null, $class);

        return $accessor($this, $value);
    }

    if ('ReflectionProperty' === $class) {
        $tmpClass = key(self::$%s[$name]);
        $cacheKey = $tmpClass . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(static function ($instance, $value) use ($name) {
                return ($instance->$name = $value);
            }, null, $tmpClass);

        return $accessor($this, $value);
    }
}

%s
PHP;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        MethodGenerator $callInitializer,
        PublicPropertiesMap $publicProperties,
        ProtectedPropertiesMap $protectedProperties,
        PrivatePropertiesMap $privateProperties
    ) {
        parent::__construct(
            $originalClass,
            '__set',
            [new ParameterGenerator('name'), new ParameterGenerator('value')]
        );

        $override = $originalClass->hasMethod('__set');

        $parentAccess = 'return parent::__set($name, $value);';

        if (! $override) {
            $parentAccess = PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_SET,
                'name',
                'value'
            );
        }

        $this->setBody(sprintf(
            $this->callParentTemplate,
            '$this->' . $initializerProperty->getName() . ' && $this->' . $callInitializer->getName()
            . '(\'__set\', array(\'name\' => $name, \'value\' => $value));',
            $publicProperties->getName(),
            $protectedProperties->getName(),
            $protectedProperties->getName(),
            $privateProperties->getName(),
            $privateProperties->getName(),
            $privateProperties->getName(),
            $parentAccess
        ));
    }
}
