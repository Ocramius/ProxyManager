<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use ProxyManager\Exception\UnsupportedProxiedClassException;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ReflectionClass;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;
use function implode;
use function var_export;

/**
 * The `bindProxyProperties` method implementation for access interceptor scope localizers
 */
class BindProxyProperties extends MethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) {
        parent::__construct(
            'bindProxyProperties',
            [
                new ParameterGenerator('localizedObject', $originalClass->getName()),
                new ParameterGenerator('prefixInterceptors', 'array', []),
                new ParameterGenerator('suffixInterceptors', 'array', []),
            ],
            self::FLAG_PRIVATE,
            null,
            "@override constructor to setup interceptors\n\n"
            . '@param \\' . $originalClass->getName() . " \$localizedObject\n"
            . "@param \\Closure[] \$prefixInterceptors method interceptors to be used before method logic\n"
            . '@param \\Closure[] $suffixInterceptors method interceptors to be used before method logic'
        );

        $localizedProperties        = [];
        $properties                 = Properties::fromReflectionClass($originalClass);
        $nonReferenceableProperties = $properties
            ->onlyNonReferenceableProperties()
            ->onlyInstanceProperties();

        if (! $nonReferenceableProperties->empty()) {
            throw UnsupportedProxiedClassException::nonReferenceableLocalizedReflectionProperties(
                $originalClass,
                $nonReferenceableProperties
            );
        }

        $propertiesThatCanBeReferenced = $properties->onlyPropertiesThatCanBeUnset();

        foreach ($propertiesThatCanBeReferenced->getAccessibleProperties() as $property) {
            $propertyName = $property->getName();

            $localizedProperties[] = '$this->' . $propertyName . ' = & $localizedObject->' . $propertyName . ';';
        }

        foreach ($propertiesThatCanBeReferenced->getPrivateProperties() as $property) {
            $propertyName = $property->getName();

            $localizedProperties[] = "\\Closure::bind(function () use (\$localizedObject) {\n    "
                . '$this->' . $propertyName . ' = & $localizedObject->' . $propertyName . ";\n"
                . '}, $this, ' . var_export($property->getDeclaringClass()->getName(), true)
                . ')->__invoke();';
        }

        $this->setBody(
            ($localizedProperties ? implode("\n\n", $localizedProperties) . "\n\n" : '')
            . '$this->' . $prefixInterceptors->getName() . " = \$prefixInterceptors;\n"
            . '$this->' . $suffixInterceptors->getName() . ' = $suffixInterceptors;'
        );
    }
}
