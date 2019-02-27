<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use InvalidArgumentException;
use ProxyManager\Generator\MagicMethodGenerator;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Magic `__get` for lazy loading value holder objects
 */
class MagicGet extends MagicMethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        PropertyGenerator $valueHolderProperty,
        PublicPropertiesMap $publicProperties
    ) {
        parent::__construct($originalClass, '__get', [new ParameterGenerator('name')]);

        $hasParent = $originalClass->hasMethod('__get');

        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();
        $callParent  = 'if (isset(self::$' . $publicProperties->getName() . "[\$name])) {\n"
            . '    return $this->' . $valueHolder . '->$name;'
            . "\n}\n\n";

        if ($hasParent) {
            $this->setInitializerBody(
                $initializer,
                $valueHolder,
                $callParent . 'return $this->' . $valueHolder . '->__get($name);'
            );

            return;
        }

        $this->setInitializerBody(
            $initializer,
            $valueHolder,
            $callParent . PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_GET,
                'name',
                null,
                $valueHolderProperty
            )
        );
    }

    private function setInitializerBody(string $initializer, string $valueHolder, string $callParent) : void
    {
        $this->setBody(
            '$this->' . $initializer . ' && $this->' . $initializer
            . '->__invoke($this->' . $valueHolder . ', $this, \'__get\', [\'name\' => $name], $this->'
            . $initializer . ');'
            . "\n\n" . $callParent
        );
    }
}
