<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MethodGenerator;

/**
 * Implementation for {@see \ProxyManager\Proxy\ValueHolderInterface::getWrappedValueHolderValue}
 * for lazy loading value holder objects
 */
class GetWrappedValueHolderValue extends MethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $valueHolderProperty)
    {
        parent::__construct('getWrappedValueHolderValue');
        $this->setBody('return $this->' . $valueHolderProperty->getName() . ';');
        $this->setReturnType('?object');
    }
}
