<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\Util;

use ReflectionClass;
use ReflectionProperty;

use function array_map;
use function assert;
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

    public static function generateSnippet(Properties $properties, string $instanceName): string
    {
        $scopedPropertyGroups = [];
        $nonScopedProperties  = [];

        foreach ($properties->getInstanceProperties() as $propertyInternalName => $property) {
            if ($property->isPrivate() || $property->isReadOnly()) {
                $scopedPropertyGroups[$property->getDeclaringClass()->getName()][$property->getName()] = $property;
            } else {
                $nonScopedProperties[$propertyInternalName] = $property;
            }
        }

        return self::generateUnsetNonScopedPropertiesCode($nonScopedProperties, $instanceName)
            . self::generateUnsetScopedPropertiesCode($scopedPropertyGroups, $instanceName);
    }

    /** @param array<string, ReflectionProperty> $nonScopedProperties */
    private static function generateUnsetNonScopedPropertiesCode(array $nonScopedProperties, string $instanceName): string
    {
        if (! $nonScopedProperties) {
            return '';
        }

        return self::generateUnsetStatement($nonScopedProperties, $instanceName) . "\n\n";
    }

    /** @param array<class-string, array<string, ReflectionProperty>> $scopedPropertyGroups */
    private static function generateUnsetScopedPropertiesCode(array $scopedPropertyGroups, string $instanceName): string
    {
        if (! $scopedPropertyGroups) {
            return '';
        }

        $unsetClosureCalls = [];

        foreach ($scopedPropertyGroups as $scopedProperties) {
            $firstProperty = reset($scopedProperties);
            assert($firstProperty instanceof ReflectionProperty);

            $unsetClosureCalls[] = self::generateUnsetClassScopedPropertiesBlock(
                $firstProperty->getDeclaringClass(),
                $scopedProperties,
                $instanceName
            );
        }

        return implode("\n\n", $unsetClosureCalls) . "\n\n";
    }

    /** @param array<string, ReflectionProperty> $properties */
    private static function generateUnsetClassScopedPropertiesBlock(
        ReflectionClass $declaringClass,
        array $properties,
        string $instanceName
    ): string {
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
    private static function generateUnsetStatement(array $properties, string $instanceName): string
    {
        return 'unset('
            . implode(
                ', ',
                array_map(
                    static fn (ReflectionProperty $property): string => '$' . $instanceName . '->' . $property->getName(),
                    $properties
                )
            )
            . ');';
    }
}
