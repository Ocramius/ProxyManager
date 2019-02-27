<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator;

use Closure;
use ProxyManager\Generator\MethodGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Implementation for {@see \ProxyManager\Proxy\AccessInterceptorInterface::setMethodSuffixInterceptor}
 * for access interceptor objects
 */
class SetMethodSuffixInterceptor extends MethodGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PropertyGenerator $suffixInterceptor)
    {
        parent::__construct('setMethodSuffixInterceptor');

        $interceptor = new ParameterGenerator('suffixInterceptor');

        $interceptor->setType(Closure::class);
        $interceptor->setDefaultValue(null);
        $this->setParameter(new ParameterGenerator('methodName', 'string'));
        $this->setParameter($interceptor);
        $this->setReturnType('void');
        $this->setBody('$this->' . $suffixInterceptor->getName() . '[$methodName] = $suffixInterceptor;');
    }
}
