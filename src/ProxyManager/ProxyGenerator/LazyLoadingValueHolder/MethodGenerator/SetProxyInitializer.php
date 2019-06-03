<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Closure;
use ProxyManager\Generator\MethodGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Implementation for {@see \ProxyManager\Proxy\LazyLoadingInterface::setProxyInitializer}
 * for lazy loading value holder objects
 */
class SetProxyInitializer extends MethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $initializerProperty)
    {
        parent::__construct('setProxyInitializer');

        $initializerParameter = new ParameterGenerator('initializer');

        $initializerParameter->setType(Closure::class);
        $initializerParameter->setDefaultValue(null);
        $this->setParameter($initializerParameter);
        $this->setBody('$this->' . $initializerProperty->getName() . ' = $initializer;');
        $this->setReturnType('void');
    }
}
