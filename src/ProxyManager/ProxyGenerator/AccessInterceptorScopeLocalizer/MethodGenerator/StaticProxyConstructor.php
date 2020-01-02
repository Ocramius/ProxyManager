<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use ProxyManager\Generator\MethodGenerator;
use ReflectionClass;

/**
 * The `staticProxyConstructor` implementation for an access interceptor scope localizer proxy
 */
class StaticProxyConstructor extends MethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(ReflectionClass $originalClass)
    {
        parent::__construct('staticProxyConstructor', [], self::FLAG_PUBLIC | self::FLAG_STATIC);

        $localizedObject = new ParameterGenerator('localizedObject');
        $prefix          = new ParameterGenerator('prefixInterceptors');
        $suffix          = new ParameterGenerator('suffixInterceptors');

        $localizedObject->setType($originalClass->getName());
        $prefix->setDefaultValue([]);
        $suffix->setDefaultValue([]);
        $prefix->setType('array');
        $suffix->setType('array');

        $this->setParameter($localizedObject);
        $this->setParameter($prefix);
        $this->setParameter($suffix);
        $this->setReturnType($originalClass->getName());

        $this->setDocBlock(
            "Constructor to setup interceptors\n\n"
            . '@param \\' . $originalClass->getName() . " \$localizedObject\n"
            . "@param \\Closure[] \$prefixInterceptors method interceptors to be used before method logic\n"
            . "@param \\Closure[] \$suffixInterceptors method interceptors to be used before method logic\n\n"
            . '@return self'
        );
        $this->setBody(
            'static $reflection;' . "\n\n"
            . '$reflection = $reflection ?? new \ReflectionClass(__CLASS__);' . "\n"
            . '$instance   = $reflection->newInstanceWithoutConstructor();' . "\n\n"
            . '$instance->bindProxyProperties($localizedObject, $prefixInterceptors, $suffixInterceptors);' . "\n\n"
            . 'return $instance;'
        );
    }
}
