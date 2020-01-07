<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\Util;

use ReflectionClass;
use ReflectionProperty;
use function array_map;
use function implode;
use function reset;
use function sprintf;
use function var_export;

/**
 * Generates code necessary to unset all the given properties from a particular given instance string name
 */
final class UnsetPropertiesGenerator
{
    private const CLOSURE_TEMPLATE = <<<'PHP'
\Closure::bind(function (\%s $instance) {
    %s
}, $%s, %s)->__invoke($%s);
PHP;

    public static function generateSnippet(Properties $properties, string $instanceName) : string
    {
        $unsettableProperties = $properties->onlyPropertiesThatCanBeUnset();

        return self::generateUnsetAccessiblePropertiesCode($unsettableProperties, $instanceName)
            . self::generateUnsetPrivatePropertiesCode($unsettableProperties, $instanceName);
    }

    private static function generateUnsetAccessiblePropertiesCode(Properties $properties, string $instanceName) : string
    {
        $accessibleProperties = $properties->getAccessibleProperties();

        if (! $accessibleProperties) {
            return '';
        }

        return self::generateUnsetStatement($accessibleProperties, $instanceName) . "\n\n";
    }

    private static function generateUnsetPrivatePropertiesCode(Properties $properties, string $instanceName) : string
    {
        $groups = $properties->getGroupedPrivateProperties();

        if (! $groups) {
            return '';
        }

        $unsetClosureCalls = [];

        foreach ($groups as $privateProperties) {
            /** @var ReflectionProperty $firstProperty */
            $firstProperty = reset($privateProperties);

            $unsetClosureCalls[] = self::generateUnsetClassPrivatePropertiesBlock(
                $firstProperty->getDeclaringClass(),
                $privateProperties,
                $instanceName
            );
        }

        return implode("\n\n", $unsetClosureCalls) . "\n\n";
    }

    /** @param array<string, ReflectionProperty> $properties */
    private static function generateUnsetClassPrivatePropertiesBlock(
        ReflectionClass $declaringClass,
        array $properties,
        string $instanceName
    ) : string {
        $declaringClassName = $declaringClass->getName();

        return sprintf(
            self::CLOSURE_TEMPLATE,
            $declaringClassName,
            self::generateUnsetStatement($properties, 'instance'),
            $instanceName,
            var_export($declaringClassName, true),
            $instanceName
        );
    }

    /** @param array<string, ReflectionProperty> $properties */
    private static function generateUnsetStatement(array $properties, string $instanceName) : string
    {
        return 'unset('
            . implode(
                ', ',
                array_map(
                    static function (ReflectionProperty $property) use ($instanceName) : string {
                        return '$' . $instanceName . '->' . $property->getName();
                    },
                    $properties
                )
            )
            . ');';
    }
}
