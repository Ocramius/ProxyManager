<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use ProxyManager\Generator\MagicMethodGenerator;
use ReflectionClass;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Magic `__clone` for lazy loading ghost objects
 */
class MagicClone extends MagicMethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        MethodGenerator $callInitializer
    ) {
        parent::__construct($originalClass, '__clone');

        $this->setBody(
            '$this->' . $initializerProperty->getName() . ' && $this->' . $callInitializer->getName()
            . '(\'__clone\', []);'
            . ($originalClass->hasMethod('__clone') ? "\n\nparent::__clone();" : '')
        );
    }
}
