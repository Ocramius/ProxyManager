<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\ParameterGenerator;
use ProxyManager\Exception\UnsupportedProxiedClassException;
use ProxyManager\Generator\MethodGenerator;
use ReflectionClass;

use function strtr;

class BindProxyProperty extends MethodGenerator
{
    public function __construct(ReflectionClass $originalClass)
    {
        parent::__construct(
            'bindProxyProperty',
            [
                new ParameterGenerator('localizedObject', $originalClass->getName()),
                new ParameterGenerator('class', ReflectionClass::class),
                new ParameterGenerator('propertyName', 'string'),
            ],
            self::FLAG_PRIVATE
        );

        $bodyTemplate = <<<'CODE'
        $originalClass = $class;
        while (! $class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        if (!$property->isInitialized($localizedObject)) {
            throw new \{ EXCEPTION_CLASS }(
                sprintf(
                    'Cannot create reference for property $%s of class %s: property must be initialized',
                    $property->getName(),
                    $originalClass->getName()
                )
            );
        }
        if (!$property->isPrivate()) {
            $this->{$propertyName} = & $localizedObject->{$propertyName};
            return;
        }
        
        \Closure::bind(
            function () use ($localizedObject, $propertyName) {
                $this->{$propertyName} = & $localizedObject->{$propertyName};
            },
            $this,
            $property->getDeclaringClass()->getName()
        )->__invoke();
        CODE;

        $this->setBody(
            strtr(
                $bodyTemplate,
                ['{ EXCEPTION_CLASS }' => UnsupportedProxiedClassException::class]
            )
        );
    }
}
