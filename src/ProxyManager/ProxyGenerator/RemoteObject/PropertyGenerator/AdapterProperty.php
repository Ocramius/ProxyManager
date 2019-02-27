<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator;

use ProxyManager\Factory\RemoteObject\AdapterInterface;
use ProxyManager\Generator\Util\IdentifierSuffixer;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Property that contains the remote object adapter
 */
class AdapterProperty extends PropertyGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct(IdentifierSuffixer::getIdentifier('adapter'));

        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocBlock('@var \\' . AdapterInterface::class . ' Remote web service adapter');
    }
}
