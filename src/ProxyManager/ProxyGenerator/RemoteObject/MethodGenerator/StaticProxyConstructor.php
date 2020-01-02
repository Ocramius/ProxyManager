<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator;

use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Factory\RemoteObject\AdapterInterface;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator;
use ReflectionClass;

/**
 * The `staticProxyConstructor` implementation for remote object proxies
 */
class StaticProxyConstructor extends MethodGenerator
{
    /**
     * Constructor
     *
     * @param ReflectionClass   $originalClass Reflection of the class to proxy
     * @param PropertyGenerator $adapter       Adapter property
     */
    public function __construct(ReflectionClass $originalClass, PropertyGenerator $adapter)
    {
        $adapterName = $adapter->getName();

        parent::__construct(
            'staticProxyConstructor',
            [new ParameterGenerator($adapterName, AdapterInterface::class)],
            MethodGenerator::FLAG_PUBLIC | MethodGenerator::FLAG_STATIC,
            null,
            'Constructor for remote object control\n\n'
            . '@param \\ProxyManager\\Factory\\RemoteObject\\AdapterInterface \$adapter'
        );

        $body = 'static $reflection;' . "\n\n"
            . '$reflection = $reflection ?? new \ReflectionClass(__CLASS__);' . "\n"
            . '$instance   = $reflection->newInstanceWithoutConstructor();' . "\n\n"
            . '$instance->' . $adapterName . ' = $' . $adapterName . ";\n\n"
            . UnsetPropertiesGenerator::generateSnippet(Properties::fromReflectionClass($originalClass), 'instance');

        $this->setBody($body . "\n\nreturn \$instance;");
    }
}
