<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\Util\IdentifierSuffixer;

/**
 * Property that contains the interceptor for operations to be executed before method execution
 */
class MethodPrefixInterceptors extends PropertyGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct(IdentifierSuffixer::getIdentifier('methodPrefixInterceptors'));

        $this->setDefaultValue([]);
        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocBlock('@var \\Closure[] map of interceptors to be called per-method before execution');
    }
}
