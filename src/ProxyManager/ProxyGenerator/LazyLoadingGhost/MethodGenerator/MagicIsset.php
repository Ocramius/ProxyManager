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
 * Magic `__isset` method for lazy loading ghost objects
 */
class MagicIsset extends MagicMethodGenerator
{
    private string $callParentTemplate = <<<'PHP'
%s

if (isset(self::$%s[$name])) {
    return isset($this->$name);
}

if (isset(self::$%s[$name])) {
    // check protected property access via compatible class
    $callers      = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller       = isset($callers[1]) ? $callers[1] : [];
    $object       = isset($caller['object']) ? $caller['object'] : '';
    $expectedType = self::$%s[$name];

    if ($object instanceof $expectedType) {
        return isset($this->$name);
    }

    $class = isset($caller['class']) ? $caller['class'] : '';

    if ($class === $expectedType || is_subclass_of($class, $expectedType)) {
        return isset($this->$name);
    }
} else {
    // check private property access via same class
    $callers = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller  = isset($callers[1]) ? $callers[1] : [];
    $class   = isset($caller['class']) ? $caller['class'] : '';

    static $accessorCache = [];

    if (isset(self::$%s[$name][$class])) {
        $cacheKey = $class . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(static function ($instance) use ($name) {
                return isset($instance->$name);
            }, null, $class);

        return $accessor($this);
    }

    if ('ReflectionProperty' === $class) {
        $tmpClass = key(self::$%s[$name]);
        $cacheKey = $tmpClass . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(static function ($instance) use ($name) {
                return isset($instance->$name);
            }, null, $tmpClass);

        return $accessor($this);
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
        parent::__construct($originalClass, '__isset', [new ParameterGenerator('name')]);

        $override = $originalClass->hasMethod('__isset');

        $parentAccess = 'return parent::__isset($name);';

        if (! $override) {
            $parentAccess = PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_ISSET,
                'name'
            );
        }

        $this->setBody(sprintf(
            $this->callParentTemplate,
            '$this->' . $initializerProperty->getName() . ' && $this->' . $callInitializer->getName()
            . '(\'__isset\', array(\'name\' => $name));',
            $publicProperties->getName(),
            $protectedProperties->getName(),
            $protectedProperties->getName(),
            $privateProperties->getName(),
            $privateProperties->getName(),
            $parentAccess
        ));
    }
}
