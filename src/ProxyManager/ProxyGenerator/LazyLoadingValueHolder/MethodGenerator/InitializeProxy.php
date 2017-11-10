<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Implementation for {@see \ProxyManager\Proxy\LazyLoadingInterface::initializeProxy}
 * for lazy loading value holder objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class InitializeProxy extends MethodGenerator
{
    /**
     * Constructor
     *
     * @param PropertyGenerator $initializerProperty
     * @param PropertyGenerator $valueHolderProperty
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     */
    public function __construct(PropertyGenerator $initializerProperty, PropertyGenerator $valueHolderProperty)
    {
        parent::__construct('initializeProxy');
        $this->setReturnType('bool');

        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();

        $this->setBody(
            'return $this->' . $initializer . ' && ($this->' . $initializer
            . '->__invoke($' . $valueHolder
            . ', $this, \'initializeProxy\', array(), $this->' . $initializer . ') || 1)'
            . ' && $this->' . $valueHolder . ' = $' . $valueHolder . ';'
        );
    }
}
