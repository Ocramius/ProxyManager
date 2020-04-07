<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\IdentifierSuffixer;
use ProxyManager\ProxyGenerator\Util\Properties;
use ReflectionProperty;
use function array_map;
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
$this->%s = false;

return $result;
PHP;

        $referenceableProperties = $properties->withoutNonReferenceableProperties();

        $this->setBody(sprintf(
            $bodyTemplate,
            $initialization,
            $initializer,
            $initialization,
            $this->propertiesInitializationCode($referenceableProperties),
            $this->propertiesReferenceArrayCode($referenceableProperties),
            $initializer,
            $initializer,
            $initialization
        ));
    }

    private function propertiesInitializationCode(Properties $properties) : string
    {
        $assignments = [];

        foreach ($properties->getAccessibleProperties() as $property) {
            $assignments[] = '$this->'
                . $property->getName()
                . ' = ' . $this->getExportedPropertyDefaultValue($property)
                . ';';
        }

        foreach ($properties->getGroupedPrivateProperties() as $className => $privateProperties) {
            $cacheKey      = 'cache' . str_replace('\\', '_', $className);
            $assignments[] = 'static $' . $cacheKey . ";\n\n"
                . '$' . $cacheKey . ' ?: $' . $cacheKey . " = \\Closure::bind(static function (\$instance) {\n"
                . $this->getPropertyDefaultsAssignments($privateProperties) . "\n"
                . '}, null, ' . var_export($className, true) . ");\n\n"
                . '$' . $cacheKey . "(\$this);\n\n";
        }

        return implode("\n", $assignments) . "\n\n";
    }

    /**
     * @param ReflectionProperty[] $properties
     */
    private function getPropertyDefaultsAssignments(array $properties) : string
    {
        return implode(
            "\n",
            array_map(
                function (ReflectionProperty $property) : string {
                    return '    $instance->' . $property->getName()
                        . ' = ' . $this->getExportedPropertyDefaultValue($property) . ';';
                },
                $properties
            )
        );
    }

    private function propertiesReferenceArrayCode(Properties $properties) : string
    {
        $assignments = [];

        foreach ($properties->getAccessibleProperties() as $propertyInternalName => $property) {
            $assignments[] = '    '
                . var_export($propertyInternalName, true) . ' => & $this->' . $property->getName()
                . ',';
        }

        $code = "\$properties = [\n" . implode("\n", $assignments) . "\n];\n\n";

        // must use assignments, as direct reference during array definition causes a fatal error (not sure why)
        foreach ($properties->getGroupedPrivateProperties() as $className => $classPrivateProperties) {
            $cacheKey = 'cacheFetch' . str_replace('\\', '_', $className);

            $code .= 'static $' . $cacheKey . ";\n\n"
                . '$' . $cacheKey . ' ?: $' . $cacheKey
                . " = \\Closure::bind(function (\$instance, array & \$properties) {\n"
                . $this->generatePrivatePropertiesAssignmentsCode($classPrivateProperties)
                . '}, $this, ' . var_export($className, true) . ");\n\n"
                . '$' . $cacheKey . '($this, $properties);';
        }

        return $code;
    }

    /**
     * @param array<string, ReflectionProperty> $properties indexed by internal name
     */
    private function generatePrivatePropertiesAssignmentsCode(array $properties) : string
    {
        $code = '';

        foreach ($properties as $property) {
            $key   = "\0" . $property->getDeclaringClass()->getName() . "\0" . $property->getName();
            $code .= '    $properties[' . var_export($key, true) . '] = '
                . '& $instance->' . $property->getName() . ";\n";
        }

        return $code;
    }

    private function getExportedPropertyDefaultValue(ReflectionProperty $property) : string
    {
        $name     = $property->getName();
        $defaults = $property->getDeclaringClass()->getDefaultProperties();

        return var_export($defaults[$name] ?? null, true);
    }
}
