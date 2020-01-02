<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MagicMethodGenerator;
use ReflectionClass;

/**
 * Magic `__clone` for lazy loading value holder objects
 */
class MagicClone extends MagicMethodGenerator
{
    /**
     * Constructor
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
            '$this->' . $initializer . ' && $this->' . $initializer
            . '->__invoke($this->' . $valueHolder
            . ', $this, \'__clone\', array(), $this->' . $initializer . ');' . "\n\n"
            . '$this->' . $valueHolder . ' = clone $this->' . $valueHolder . ';'
        );
    }
}
