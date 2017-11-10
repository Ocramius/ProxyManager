<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use ProxyManager\Generator\MagicMethodGenerator;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Magic `__clone` for lazy loading value holder objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MagicClone extends MagicMethodGenerator
{
    /**
     * Constructor
     *
     * @param ReflectionClass   $originalClass
     * @param PropertyGenerator $initializerProperty
     * @param PropertyGenerator $valueHolderProperty
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        PropertyGenerator $valueHolderProperty
    ) {
        parent::__construct($originalClass, '__clone');

        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();

        $this->setBody(
            '$this->' . $initializer . ' && ($this->' . $initializer
            . '->__invoke($' . $valueHolder
            . ', $this, \'__clone\', array(), $this->' . $initializer . ') || 1)'
            . ' && $this->' . $valueHolder . ' = $' . $valueHolder . ';' . "\n\n"
            . '$this->' . $valueHolder . ' = clone $this->' . $valueHolder . ';'
        );
    }
}
