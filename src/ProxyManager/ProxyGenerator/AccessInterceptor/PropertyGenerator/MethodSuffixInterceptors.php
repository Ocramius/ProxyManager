<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator;

use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Property that contains the interceptor for operations to be executed after method execution
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MethodSuffixInterceptors extends PropertyGenerator
{
    /**
     * Constructor
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct(UniqueIdentifierGenerator::getIdentifier('methodSuffixInterceptors'));

        $this->setDefaultValue([]);
        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocBlock('@var \\Closure[] map of interceptors to be called per-method after execution');
    }
}
