<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Exception\UnsupportedProxiedClassException;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ReflectionClass;
use ReflectionProperty;

use function array_key_exists;
use function implode;
use function sprintf;
use function strtr;
use function var_export;

use const PHP_EOL;

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

        $localizedProperties = [];
        $properties          = Properties::fromReflectionClass($originalClass)->withoutNonReferenceableProperties();
        foreach ($properties->getAccessibleProperties() as $property) {
            $propertyName = $property->getName();

            $localizedProperties[] = '$this->' . $propertyName . ' = & $localizedObject->' . $propertyName . ';';
        }

        foreach ($properties->getPrivateProperties() as $property) {
            $propertyName = $property->getName();

            $localizedProperties[] = "\\Closure::bind(function () use (\$localizedObject) {\n    "
                . '$this->' . $propertyName . ' = & $localizedObject->' . $propertyName . ";\n"
                . '}, $this, ' . var_export($property->getDeclaringClass()->getName(), true)
                . ')->__invoke();';
        }

        $bodyLines = [
            implode("\n\n", $localizedProperties),
        ];

        $nonReferenceableProperties = Properties::fromReflectionClass($originalClass)
            ->onlyNonReferenceableProperties()
            ->onlyInstanceProperties();

        if (! $nonReferenceableProperties->empty()) {
            $bodyLines[] = '$class = new \ReflectionObject($localizedObject);';
            $bodyLines[] = $this->generateClassMapInitializationCode($nonReferenceableProperties);

            foreach ($nonReferenceableProperties->getInstanceProperties() as $property) {
                $bodyLines[] = $this->generateCodeForPropertyBinding($property);
            }
        }

        $bodyLines[] = sprintf('$this->%s = $prefixInterceptors;', $prefixInterceptors->getName());
        $bodyLines[] = sprintf('$this->%s = $suffixInterceptors;', $suffixInterceptors->getName());
        $this->setBody(implode(PHP_EOL, $bodyLines));
    }

    private function generateClassMapInitializationCode(Properties $properties): string
    {
        $classMapInitializationCode = '$classesMap = []; // Class name to ReflectionClass map' . PHP_EOL;

        $mappedClasses = [];
        foreach ($properties->getPrivateProperties() as $property) {
            $declaringClassName = $property->getDeclaringClass()->getName();
            if (array_key_exists($declaringClassName, $mappedClasses)) {
                continue;
            }

            $classMapInitializationCode .= sprintf(
                '$classesMap[\'%s\'] = new \ReflectionClass(\'%s\');',
                $declaringClassName,
                '\\' . $declaringClassName
            );
            $classMapInitializationCode .= PHP_EOL;

            $mappedClasses[$declaringClassName] = null;
        }

        return $classMapInitializationCode;
    }

    private function generateCodeForPropertyBinding(ReflectionProperty $property): string
    {
        $declaringClassName     = $property->getDeclaringClass()->getName();
        $localizedPropertyCode  = $property->isPrivate()
            ? sprintf(
                '$property = $classesMap[\'%s\']->getProperty(\'%s\');',
                $declaringClassName,
                $property->getName()
            )
            : sprintf('$property = $class->getProperty(\'%s\');', $property->getName());
        $localizedPropertyCode .= PHP_EOL;

        if (! $property->isPublic()) {
            $localizedPropertyCode .= '$property->setAccessible(true);' . PHP_EOL;
        }

        $localizedPropertyCode .= <<<'CODE'
        if (!$property->isInitialized($localizedObject)) {
            throw new \{ EXCEPTION_CLASS }(
                sprintf(
                    'Cannot create reference for property $%s of class %s: property must be initialized',
                    $property->getName(),
                    $class->getName()
                )
            );
        }
        CODE;

        $codeToCreateReference = '$this->{ PROPERTY_NAME } = & $localizedObject->{ PROPERTY_NAME };';
        if ($property->isPrivate()) {
            $codeToCreateReference = <<<'CODE'
            \Closure::bind(
                function () use ($localizedObject) {
                    $this->{ PROPERTY_NAME } = & $localizedObject->{ PROPERTY_NAME };
                },
                $this,
                $property->getDeclaringClass()->getName()
            )->__invoke();
            CODE;
        }

        $localizedPropertyCode .= PHP_EOL . $codeToCreateReference . PHP_EOL;

        return strtr(
            $localizedPropertyCode,
            [
                '{ PROPERTY_NAME }' => $property->getName(),
                '{ EXCEPTION_CLASS }' => UnsupportedProxiedClassException::class,
            ]
        );
    }
}
