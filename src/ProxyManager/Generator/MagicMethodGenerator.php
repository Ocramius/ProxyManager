<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use Laminas\Code\Generator\MethodGenerator as LaminasMethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use ReflectionClass;

use function strtolower;

/**
 * Method generator for magic methods
 */
class MagicMethodGenerator extends MethodGenerator
{
    /**
     * @param ParameterGenerator[]|array[]|string[] $parameters
     */
    public function __construct(ReflectionClass $originalClass, string $name, array $parameters = [])
    {
        parent::__construct(
            $name,
            $parameters,
            self::FLAG_PUBLIC
        );

        $this->setReturnsReference(strtolower($name) === '__get');

        if (! $originalClass->hasMethod($name)) {
            return;
        }

        $this->setReturnsReference($originalClass->getMethod($name)->returnsReference());
    }

    public function setBody($body): LaminasMethodGenerator
    {
        if ((string) $this->getReturnType() === 'void') {
            $body = preg_replace('/return ([^;]++;)/', '\1 return;', $body);
        }

        return parent::setBody($body);
    }
}
