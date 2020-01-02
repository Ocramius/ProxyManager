<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\Util\IdentifierSuffixer;

/**
 * Property that contains the initializer for a lazy object
 */
class InitializerProperty extends PropertyGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct(IdentifierSuffixer::getIdentifier('initializer'));

        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocBlock('@var \\Closure|null initializer responsible for generating the wrapped object');
    }
}
