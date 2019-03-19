<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * The `staticProxyConstructor` implementation for lazy loading proxies
 */
class StaticProxyConstructor extends MethodGenerator
{
    /**
     * Static constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $initializerProperty, Properties $properties)
    {
        parent::__construct('staticProxyConstructor', [], self::FLAG_PUBLIC | self::FLAG_STATIC);

        $this->setParameter(new ParameterGenerator('initializer'));

        $this->setDocBlock("Constructor for lazy initialization\n\n@param \\Closure|null \$initializer");
        $this->setBody(
            'static $reflection;' . "\n\n"
            . '$reflection = $reflection ?? new \ReflectionClass(__CLASS__);' . "\n"
            . '$instance   = $reflection->newInstanceWithoutConstructor();' . "\n\n"
            . UnsetPropertiesGenerator::generateSnippet($properties, 'instance')
            . '$instance->' . $initializerProperty->getName() . ' = $initializer;' . "\n\n"
            . 'return $instance;'
        );
    }
}
