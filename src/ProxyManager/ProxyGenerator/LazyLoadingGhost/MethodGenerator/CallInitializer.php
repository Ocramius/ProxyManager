<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use LogicException;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\IdentifierSuffixer;
use ProxyManager\ProxyGenerator\Util\Properties;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

use function array_map;
use function assert;
use function get_class;
use function implode;
use function sprintf;
use function str_replace;
use function var_export;

/**
 * Implementation for {@see \ProxyManager\Proxy\LazyLoadingInterface::isProxyInitialized}
 * for lazy loading value holder objects
 */
class CallInitializer extends MethodGenerator
{
    public function __construct(
        PropertyGenerator $initializerProperty,
        PropertyGenerator $initTracker,
        Properties $properties
    ) {
        $docBlock = <<<'DOCBLOCK'
Triggers initialization logic for this ghost object

@param string  $methodName
@param mixed[] $parameters

@return mixed
DOCBLOCK;

        parent::__construct(
            IdentifierSuffixer::getIdentifier('callInitializer'),
            [
                new ParameterGenerator('methodName'),
                new ParameterGenerator('parameters', 'array'),
            ],
            self::FLAG_PRIVATE,
            null,
            $docBlock
        );

        $initializer    = $initializerProperty->getName();
        $initialization = $initTracker->getName();

        $bodyTemplate = <<<'PHP'
if ($this->%s || ! $this->%s) {
    return;
}

$this->%s = true;

%s
%s

$result = $this->%s->__invoke($this, $methodName, $parameters, $this->%s, $properties);
%s$this->%s = false;

return $result;
PHP;

        $referenceableProperties    = $properties->withoutNonReferenceableProperties();
        $nonReferenceableProperties = $properties->onlyNonReferenceableProperties();

        $this->setBody(sprintf(
            $bodyTemplate,
            $initialization,
            $initializer,
            $initialization,
            $this->propertiesInitializationCode($referenceableProperties),
            $this->propertiesReferenceArrayCode($referenceableProperties, $nonReferenceableProperties),
            $initializer,
            $initializer,
            $this->propertiesNonReferenceableCode($nonReferenceableProperties),
            $initialization
        ));
    }

    private function propertiesInitializationCode(Properties $properties): string
    {
        $scopedPropertyGroups = [];
        $nonScopedProperties  = [];

        foreach ($properties->getInstanceProperties() as $property) {
            if ($property->isPrivate() || $property->isReadOnly()) {
                $scopedPropertyGroups[$property->getDeclaringClass()->getName()][$property->getName()] = $property;
            } else {
                $nonScopedProperties[] = $property;
            }
        }

        $assignments = [];

        foreach ($nonScopedProperties as $property) {
            $assignments[] = '$this->'
                . $property->getName()
                . ' = ' . $this->getExportedPropertyDefaultValue($property)
                . ';';
        }

        foreach ($scopedPropertyGroups as $className => $scopedProperties) {
            $cacheKey      = 'cache' . str_replace('\\', '_', $className);
            $assignments[] = 'static $' . $cacheKey . ";\n\n"
                . '$' . $cacheKey . ' ?? $' . $cacheKey . " = \\Closure::bind(static function (\$instance) {\n"
                . $this->getPropertyDefaultsAssignments($scopedProperties) . "\n"
                . '}, null, ' . var_export($className, true) . ");\n\n"
                . '$' . $cacheKey . "(\$this);\n\n";
        }

        return implode("\n", $assignments) . "\n\n";
    }

    /**
     * @param ReflectionProperty[] $properties
     */
    private function getPropertyDefaultsAssignments(array $properties): string
    {
        return implode(
            "\n",
            array_map(
                fn (ReflectionProperty $property): string => '    $instance->' . $property->getName()
                    . ' = ' . $this->getExportedPropertyDefaultValue($property) . ';',
                $properties
            )
        );
    }

    private function propertiesReferenceArrayCode(Properties $properties, Properties $nonReferenceableProperties): string
    {
        $assignments                          = [];
        $nonReferenceablePropertiesDefinition = '';

        foreach ($properties->getAccessibleProperties() as $propertyInternalName => $property) {
            $assignments[] = '    '
                . var_export($propertyInternalName, true) . ' => & $this->' . $property->getName()
                . ',';
        }

        foreach ($nonReferenceableProperties->getInstanceProperties() as $propertyInternalName => $property) {
            $propertyAlias = $property->getName() . ($property->isPrivate() ? '_on_' . str_replace('\\', '_', $property->getDeclaringClass()->getName()) : '');
            $propertyType  = $property->getType();
            assert($propertyType !== null);

            $nonReferenceablePropertiesDefinition .= sprintf("    public %s $%s;\n", self::getReferenceableType($propertyType), $propertyAlias);

            $assignments[] = sprintf('    %s => & $nonReferenceableProperties->%s,', var_export($propertyInternalName, true), $propertyAlias);
        }

        $code  = $nonReferenceableProperties->empty() ? '' : sprintf("\$nonReferenceableProperties = new class() {\n%s};\n", $nonReferenceablePropertiesDefinition);
        $code .= "\$properties = [\n" . implode("\n", $assignments) . "\n];\n\n";

        // must use assignments, as direct reference during array definition causes a fatal error (not sure why)
        foreach ($properties->getGroupedPrivateProperties() as $className => $classPrivateProperties) {
            $cacheKey = 'cacheFetch' . str_replace('\\', '_', $className);

            $code .= 'static $' . $cacheKey . ";\n\n"
                . '$' . $cacheKey . ' ?? $' . $cacheKey
                . " = \\Closure::bind(function (\$instance, array & \$properties) {\n"
                . $this->generatePrivatePropertiesAssignmentsCode($classPrivateProperties)
                . '}, null, ' . var_export($className, true) . ");\n\n"
                . '$' . $cacheKey . '($this, $properties);';
        }

        return $code;
    }

    /**
     * @param array<string, ReflectionProperty> $properties indexed by internal name
     */
    private function generatePrivatePropertiesAssignmentsCode(array $properties): string
    {
        $code = '';

        foreach ($properties as $property) {
            $key   = "\0" . $property->getDeclaringClass()->getName() . "\0" . $property->getName();
            $code .= '    $properties[' . var_export($key, true) . '] = '
                . '& $instance->' . $property->getName() . ";\n";
        }

        return $code;
    }

    private function getExportedPropertyDefaultValue(ReflectionProperty $property): string
    {
        $name     = $property->getName();
        $defaults = $property->getDeclaringClass()->getDefaultProperties();

        return var_export($defaults[$name] ?? null, true);
    }

    private function propertiesNonReferenceableCode(Properties $properties): string
    {
        if ($properties->empty()) {
            return '';
        }

        $code                 = [];
        $scopedPropertyGroups = [];

        foreach ($properties->getInstanceProperties() as $propertyInternalName => $property) {
            if (! $property->isPrivate() && ! $property->isReadOnly()) {
                $propertyAlias = $property->getName() . ($property->isPrivate() ? '_on_' . str_replace('\\', '_', $property->getDeclaringClass()->getName()) : '');
                $code[]        = sprintf('isset($nonReferenceableProperties->%s) && $this->%s = $nonReferenceableProperties->%1$s;', $propertyAlias, $property->getName());
            } else {
                $scopedPropertyGroups[$property->getDeclaringClass()->getName()][$propertyInternalName] = $property;
            }
        }

        foreach ($scopedPropertyGroups as $className => $scopedProperties) {
            $cacheKey = 'cacheAssign' . str_replace('\\', '_', $className);

            $code[] = 'static $' . $cacheKey . ";\n";
            $code[] = '$' . $cacheKey . ' ?? $' . $cacheKey . ' = \Closure::bind(function ($instance, $nonReferenceableProperties) {';

            foreach ($scopedProperties as $property) {
                $propertyAlias = $property->getName() . ($property->isPrivate() ? '_on_' . str_replace('\\', '_', $property->getDeclaringClass()->getName()) : '');
                $code[]        = sprintf('    isset($nonReferenceableProperties->%s) && $this->%s = $nonReferenceableProperties->%1$s;', $propertyAlias, $property->getName());
            }

            $code[] = '}, null, ' . var_export($className, true) . ");\n";
            $code[] = '$' . $cacheKey . '($this, $nonReferenceableProperties);';
        }

        return implode("\n", $code) . "\n";
    }

    private static function getReferenceableType(ReflectionType $type): string
    {
        if ($type instanceof ReflectionNamedType) {
            return '?' . ($type->isBuiltin() ? '' : '\\') . $type->getName();
        }

        if ($type instanceof ReflectionIntersectionType) {
            return self::getReferenceableType($type->getTypes()[0]);
        }

        if (! $type instanceof ReflectionUnionType) {
            throw new LogicException('Unexpected ' . get_class($type));
        }

        $union = 'null';

        foreach ($type->getTypes() as $subType) {
            $union .= '|' . ($subType->isBuiltin() ? '' : '\\') . $subType->getName();
        }

        return $union;
    }
}
