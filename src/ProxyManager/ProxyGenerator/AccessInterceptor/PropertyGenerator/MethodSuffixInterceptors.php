<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator;

use ProxyManager\Generator\Util\IdentifierSuffixer;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Property that contains the interceptor for operations to be executed after method execution
 */
class MethodSuffixInterceptors extends PropertyGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct(IdentifierSuffixer::getIdentifier('methodSuffixInterceptors'));

        $this->setDefaultValue([]);
        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocBlock('@var \\Closure[] map of interceptors to be called per-method after execution');
    }
}
