<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Zend\Code\Generator\ParameterGenerator;
use function strtolower;

/**
 * Method generator for magic methods
 */
class MagicMethodGenerator extends MethodGenerator
{
    /**
     * @param mixed[] $parameters
     */
    public function __construct(ReflectionClass $originalClass, string $name, array $parameters = [])
    {
        parent::__construct(
            $name,
            $parameters,
            static::FLAG_PUBLIC
        );

        $this->setReturnsReference(strtolower($name) === '__get');

        if (! $originalClass->hasMethod($name)) {
            return;
        }

        $this->mirrorParentMethodSignature($originalClass->getMethod($name));
    }

    private function mirrorParentMethodSignature(ReflectionMethod $parentMethod) : void
    {
        $returnType = $parentMethod->getReturnType();

        if ($returnType !== null) {
            $this->setReturnType(($returnType->allowsNull() ? '?' : '') . $returnType->getName());
        }

        $this->setReturnsReference($parentMethod->returnsReference());

        foreach (array_values($parentMethod->getParameters()) as $index => $parentParameter) {
            $this->mirrorParentMethodParameterType($parentParameter, array_values($this->parameters)[$index]);
        }
    }

    private function mirrorParentMethodParameterType(
        ReflectionParameter $parentParameter,
        ParameterGenerator $parameter
    ) : void {
        $parameter->setVariadic($parentParameter->isVariadic());

        $type = $parentParameter->getType();

        if ($type !== null) {
            $parameter->setType(($type->allowsNull() ? '?' : '') . $type->getName());
        }

        if ($parentParameter->isDefaultValueAvailable()) {
            $parameter->setDefaultValue($parentParameter->getDefaultValue());
        }
    }
}
