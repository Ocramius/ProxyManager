<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MagicMethodGenerator;
use ReflectionClass;

/**
 * Magic `__sleep` for lazy loading ghost objects
 */
class MagicSleep extends MagicMethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        MethodGenerator $callInitializer
    ) {
        parent::__construct($originalClass, '__sleep');

        $this->setBody(
            '$this->' . $initializerProperty->getName() . ' && $this->' . $callInitializer->getName()
            . '(\'__sleep\', []);' . "\n\n"
            . ($originalClass->hasMethod('__sleep') ? 'return parent::__sleep();' : 'return array_keys((array) $this);')
        );
    }
}
