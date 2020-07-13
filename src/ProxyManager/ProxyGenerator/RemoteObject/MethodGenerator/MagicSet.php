<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MagicMethodGenerator;
use ReflectionClass;

use function var_export;

/**
 * Magic `__set` for remote objects
 */
class MagicSet extends MagicMethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(ReflectionClass $originalClass, PropertyGenerator $adapterProperty)
    {
        parent::__construct(
            $originalClass,
            '__set',
            [new ParameterGenerator('name'), new ParameterGenerator('value')]
        );

        $this->setDocBlock('@param string \$name\n@param mixed \$value');
        $this->setBody(
            '$return = $this->' . $adapterProperty->getName() . '->call(' . var_export($originalClass->getName(), true)
            . ', \'__set\', array($name, $value));' . "\n\n"
            . 'return $return;'
        );
    }
}
