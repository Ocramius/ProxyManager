<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Factory\RemoteObject\AdapterInterface;
use ProxyManager\Generator\Util\IdentifierSuffixer;

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
