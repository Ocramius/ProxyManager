<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MethodGenerator;
use ReflectionClass;

/**
 * Destructor that skips the original destructor when the proxy is not initialized.
 */
class SkipDestructor extends MethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(
        PropertyGenerator $initializerProperty,
        PropertyGenerator $valueHolderProperty
    ) {
        parent::__construct('__destruct');

        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();

        $this->setBody(
            '$this->' . $initializer . ' || $this->' . $valueHolder . '->__destruct();'
        );
    }
}
