<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use InvalidArgumentException;
use ProxyManager\Generator\MagicMethodGenerator;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;
use function sprintf;

/**
 * Magic `__set` for lazy loading ghost objects
 */
class MagicSet extends MagicMethodGenerator
{
    private const TEMPLATE = <<<'PHP'
$this->%initializerProperty% && $this->%callInitializer%('__set', ['name' => $name, 'value' => $value]);

if (isset(self::$%publicProperties%[$name])) {
    %returnNonVoidExpression%($this->$name = $value);
    %returnVoidExpression%
}

if (isset(self::$%protectedProperties%[$name])) {
    // check protected property access via compatible class
    $callers      = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller       = isset($callers[1]) ? $callers[1] : [];
    $object       = isset($caller['object']) ? $caller['object'] : '';
    $expectedType = self::$%protectedProperties%[$name];

    if ($object instanceof $expectedType) {
        %returnNonVoidExpression%($this->$name = $value);
        %returnVoidExpression%
    }

    $class = isset($caller['class']) ? $caller['class'] : '';

    if ($class === $expectedType || is_subclass_of($class, $expectedType) || $class === 'ReflectionProperty') {
        %returnNonVoidExpression%($this->$name = $value);
        %returnVoidExpression%
    }
} elseif (isset(self::$%privateProperties%[$name])) {
    // check private property access via same class
    $callers = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller  = isset($callers[1]) ? $callers[1] : [];
    $class   = isset($caller['class']) ? $caller['class'] : '';

    static $accessorCache = [];

    if (isset(self::$%privateProperties%[$name][$class])) {
        $cacheKey = $class . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(function ($instance, $value) use ($name) {
                return ($instance->$name = $value);
            }, null, $class);

        %returnNonVoidExpression%$accessor($this, $value);
        %returnVoidExpression%
    }

    if ('ReflectionProperty' === $class) {
        $tmpClass = key(self::$%privateProperties%[$name]);
        $cacheKey = $tmpClass . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(function ($instance, $value) use ($name) {
                return ($instance->$name = $value);
            }, null, $tmpClass);

        %returnNonVoidExpression%$accessor($this, $value);
        %returnVoidExpression%
    }
}

%parentAccess%
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

        $returnType   = $this->getReturnType();
        $isVoid       = $returnType && 'void' === strtolower($returnType->generate());
        $parentAccess = ($isVoid ? '' : 'return ') . 'parent::__set($name, $value);';

        if (! $originalClass->hasMethod('__set')) {
            $parentAccess = PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_SET,
                'name',
                'value'
            );
        }

        $replacements = [
            '%initializerProperty%'     => $initializerProperty->getName(),
            '%callInitializer%'         => $callInitializer->getName(),
            '%publicProperties%'        => $publicProperties->getName(),
            '%protectedProperties%'     => $protectedProperties->getName(),
            '%privateProperties%'       => $privateProperties->getName(),
            '%parentAccess%'            => $parentAccess,
            '%returnVoidExpression%'    => $isVoid ? 'return;' : '',
            '%returnNonVoidExpression%' => $isVoid ? '' : 'return ',
        ];

        $this->setBody(str_replace(
            array_keys($replacements),
            $replacements,
            self::TEMPLATE
        ));
    }
}
