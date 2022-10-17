<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MagicMethodGenerator;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;

use function implode;
use function sprintf;

/**
 * Magic `__get` for lazy loading ghost objects
 */
class MagicGet extends MagicMethodGenerator
{
    private string $callParentTemplate =  <<<'PHP'
$this->%s && ! $this->%s && $this->%s('__get', array('name' => $name));

if (isset(self::$%s[$name])) {
    return $this->$name;
}

if (isset(self::$%s[$name])) {
    if ($this->%s) {
        return $this->$name;
    }

    // check protected property access via compatible class
    $callers      = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller       = isset($callers[1]) ? $callers[1] : [];
    $object       = isset($caller['object']) ? $caller['object'] : '';
    $expectedType = self::$%s[$name];

    if ($object instanceof $expectedType) {
        return $this->$name;
    }

    $class = isset($caller['class']) ? $caller['class'] : '';

    if ($class === $expectedType || is_subclass_of($class, $expectedType) || $class === 'ReflectionProperty') {
        return $this->$name;
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
            : $accessorCache[$cacheKey] = \Closure::bind(static function & ($instance) use ($name) {
                %s
            }, null, $class);

        return $accessor($this);
    }

    if ($this->%s || 'ReflectionProperty' === $class) {
        $tmpClass = key(self::$%s[$name]);
        $cacheKey = $tmpClass . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(static function & ($instance) use ($name) {
                %s
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
        PrivatePropertiesMap $privateProperties,
        InitializationTracker $initializationTracker
    ) {
        parent::__construct($originalClass, '__get', [new ParameterGenerator('name')]);

        $override = $originalClass->hasMethod('__get');

        $parentAccess = 'return parent::__get($name);';

        if (! $override) {
            $parentAccess = PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_GET,
                'name'
            );
        }

        $readOnlyPropertyNames = $privateProperties->getReadOnlyPropertyNames();

        if ($readOnlyPropertyNames) {
            $privateReturnCode  = sprintf('\in_array($name, [\'%s\'], true) ? $value = $instance->$name : $value = & $instance->$name;', implode("', '", $readOnlyPropertyNames));
            $privateReturnCode .= "\n\n                return \$value;";
        } else {
            $privateReturnCode = 'return $instance->$name;';
        }

        $this->setBody(sprintf(
            $this->callParentTemplate,
            $initializerProperty->getName(),
            $initializationTracker->getName(),
            $callInitializer->getName(),
            $publicProperties->getName(),
            $protectedProperties->getName(),
            $initializationTracker->getName(),
            $protectedProperties->getName(),
            $privateProperties->getName(),
            $privateProperties->getName(),
            $privateReturnCode,
            $initializationTracker->getName(),
            $privateProperties->getName(),
            $privateReturnCode,
            $parentAccess
        ));
    }
}
