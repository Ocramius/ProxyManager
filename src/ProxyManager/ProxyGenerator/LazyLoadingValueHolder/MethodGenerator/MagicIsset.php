<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MagicMethodGenerator;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;

/**
 * Magic `__isset` method for lazy loading value holder objects
 */
class MagicIsset extends MagicMethodGenerator
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
        parent::__construct($originalClass, '__isset', [new ParameterGenerator('name')]);

        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();
        $callParent  = '';

        if (! $publicProperties->isEmpty()) {
            $callParent = 'if (isset(self::$' . $publicProperties->getName() . "[\$name])) {\n"
                . '    return isset($this->' . $valueHolder . '->$name);'
                . "\n}\n\n";
        }

        $callParent .= PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_ISSET,
            'name',
            null,
            $valueHolderProperty,
            null,
            $originalClass
        );

        $this->setBody(
            '$this->' . $initializer . ' && $this->' . $initializer
            . '->__invoke($this->' . $valueHolder . ', $this, \'__isset\', array(\'name\' => $name), $this->'
            . $initializer . ');' . "\n\n" . $callParent
        );
    }
}
