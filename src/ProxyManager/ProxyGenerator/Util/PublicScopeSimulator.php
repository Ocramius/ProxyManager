<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\Util;

use InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use ReflectionClass;

use function array_filter;
use function array_map;
use function implode;
use function sprintf;
use function var_export;

/**
 * Generates code necessary to simulate a fatal error in case of unauthorized
 * access to class members in magic methods even when in child classes and dealing
 * with protected members.
 */
class PublicScopeSimulator
{
    public const OPERATION_SET   = 'set';
    public const OPERATION_GET   = 'get';
    public const OPERATION_ISSET = 'isset';
    public const OPERATION_UNSET = 'unset';

    /**
     * Generates code for simulating access to a property from the scope that is accessing a proxy.
     * This is done by introspecting `debug_backtrace()` and then binding a closure to the scope
     * of the parent caller.
     *
     * @param string            $operationType      operation to execute: one of 'get', 'set', 'isset' or 'unset'
     * @param string            $nameParameter      name of the `name` parameter of the magic method
     * @param string|null       $valueParameter     name of the `value` parameter of the magic method, only to be
     *                                              used with $operationType 'set'
     * @param PropertyGenerator $valueHolder        name of the property containing the target object from which
     *                                              to read the property. `$this` if none provided
     * @param string|null       $returnPropertyName name of the property to which we want to assign the result of
     *                                              the operation. Return directly if none provided
     * @param string|null       $interfaceName      name of the proxified interface if any
     * @psalm-param $operationType self::OPERATION_*
     *
     * @throws InvalidArgumentException
     */
    public static function getPublicAccessSimulationCode(
        string $operationType,
        string $nameParameter,
        ?string $valueParameter = null,
        ?PropertyGenerator $valueHolder = null,
        ?string $returnPropertyName = null,
        ?ReflectionClass $originalClass = null
    ): string {
        $byRef  = self::getByRefReturnValue($operationType);
        $target = '$this';

        if ($valueHolder) {
            $target = '$this->' . $valueHolder->getName();
        }

        $originalClassReflection = $originalClass === null
            ? 'new \\ReflectionClass(get_parent_class($this))'
            : 'new \\ReflectionClass(' . var_export($originalClass->getName(), true) . ')';

        $accessorEvaluation = $returnPropertyName
            ? '$' . $returnPropertyName . ' = ' . $byRef . '$accessor();'
            : '$returnValue = ' . $byRef . '$accessor();' . "\n\n" . 'return $returnValue;';

        if ($operationType === self::OPERATION_UNSET) {
            $accessorEvaluation = '$accessor();';
        }

        return '$realInstanceReflection = ' . $originalClassReflection . ';' . "\n\n"
            . 'if (! $realInstanceReflection->hasProperty($' . $nameParameter . ')) {' . "\n"
            . '    $targetObject = ' . $target . ';' . "\n\n"
            . self::getUndefinedPropertyNotice($operationType, $nameParameter)
            . '    ' . self::getOperation($operationType, $nameParameter, $valueParameter) . "\n"
            . '}' . "\n\n"
            . '$targetObject = ' . self::getTargetObject($valueHolder) . ";\n"
            . '$accessor = function ' . $byRef . '() use ('
            . implode(', ', array_map(
                static fn (string $parameterName): string => '$' . $parameterName,
                array_filter(['targetObject', $nameParameter, $valueParameter])
            ))
            . ') {' . "\n"
            . '    ' . self::getOperation($operationType, $nameParameter, $valueParameter) . "\n"
            . "};\n"
            . self::generateScopeReBind()
            . $accessorEvaluation;
    }

    /**
     * This will generate code that triggers a notice if access is attempted on a non-existing property
     *
     * @psalm-param $operationType self::OPERATION_*
     */
    private static function getUndefinedPropertyNotice(string $operationType, string $nameParameter): string
    {
        if ($operationType !== self::OPERATION_GET) {
            return '';
        }

        return '    $backtrace = debug_backtrace(false, 1);' . "\n"
            . '    trigger_error(' . "\n"
            . '        sprintf(' . "\n"
            . '            \'Undefined property: %s::$%s in %s on line %s\',' . "\n"
            . '            $realInstanceReflection->getName(),' . "\n"
            . '            $' . $nameParameter . ',' . "\n"
            . '            $backtrace[0][\'file\'],' . "\n"
            . '            $backtrace[0][\'line\']' . "\n"
            . '        ),' . "\n"
            . '        \E_USER_NOTICE' . "\n"
            . '    );' . "\n";
    }

    /**
     * Defines whether the given operation produces a reference.
     *
     * Note: if the object is a wrapper, the wrapped instance is accessed directly. If the object
     * is a ghost or the proxy has no wrapper, then an instance of the parent class is created via
     * on-the-fly unserialization
     *
     * @psalm-param $operationType self::OPERATION_*
     */
    private static function getByRefReturnValue(string $operationType): string
    {
        return $operationType === self::OPERATION_GET || $operationType === self::OPERATION_SET ? '& ' : '';
    }

    /**
     * Retrieves the logic to fetch the object on which access should be attempted
     */
    private static function getTargetObject(?PropertyGenerator $valueHolder = null): string
    {
        if ($valueHolder) {
            return '$this->' . $valueHolder->getName();
        }

        return '$realInstanceReflection->newInstanceWithoutConstructor()';
    }

    /**
     * @psalm-param $operationType self::OPERATION_*
     *
     * @throws InvalidArgumentException
     */
    private static function getOperation(string $operationType, string $nameParameter, ?string $valueParameter): string
    {
        if ($valueParameter !== null && $operationType !== self::OPERATION_SET) {
            throw new InvalidArgumentException(
                'Parameter $valueParameter should be provided (only) when $operationType === "' . self::OPERATION_SET . '"'
                . self::class
                . '::OPERATION_SET'
            );
        }

        switch ($operationType) {
            case self::OPERATION_GET:
                return 'return $targetObject->$' . $nameParameter . ';';

            case self::OPERATION_SET:
                if ($valueParameter === null) {
                    throw new InvalidArgumentException(
                        'Parameter $valueParameter should be provided (only) when $operationType === "' . self::OPERATION_SET . '"'
                        . self::class
                        . '::OPERATION_SET'
                    );
                }

                return '$targetObject->$' . $nameParameter . ' = $' . $valueParameter . ';'
                    . "\n\n"
                    . '    return $targetObject->$' . $nameParameter . ';';

            case self::OPERATION_ISSET:
                return 'return isset($targetObject->$' . $nameParameter . ');';

            case self::OPERATION_UNSET:
                return 'unset($targetObject->$' . $nameParameter . ');'
                    . "\n\n"
                    . '    return;';
        }

        throw new InvalidArgumentException(sprintf('Invalid operation "%s" provided', $operationType));
    }

    /**
     * Generates code to bind operations to the parent scope
     */
    private static function generateScopeReBind(): string
    {
        return <<<'PHP'
$backtrace = debug_backtrace(true, 2);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));

PHP;
    }
}
