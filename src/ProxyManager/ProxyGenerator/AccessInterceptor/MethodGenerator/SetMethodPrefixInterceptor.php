<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator;

use Closure;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MethodGenerator;

/**
 * Implementation for {@see \ProxyManager\Proxy\AccessInterceptorInterface::setMethodPrefixInterceptor}
 * for access interceptor objects
 */
class SetMethodPrefixInterceptor extends MethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $prefixInterceptor)
    {
        parent::__construct('setMethodPrefixInterceptor');

        $interceptor = new ParameterGenerator('prefixInterceptor');

        $interceptor->setType(Closure::class);
        $interceptor->setDefaultValue(null);
        $this->setParameter(new ParameterGenerator('methodName', 'string'));
        $this->setParameter($interceptor);
        $this->setReturnType('void');
        $this->setBody('$this->' . $prefixInterceptor->getName() . '[$methodName] = $prefixInterceptor;');
    }
}
