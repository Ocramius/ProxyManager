<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\PropertyGenerator;

use ProxyManager\Generator\Util\IdentifierSuffixer;
use ProxyManager\ProxyGenerator\Util\Properties;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Map of public properties that exist in the class being proxied
 */
class PublicPropertiesMap extends PropertyGenerator
{
    /** @var array<string, bool> */
    private array $publicProperties = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(Properties $properties)
    {
        parent::__construct(IdentifierSuffixer::getIdentifier('publicProperties'));

        foreach ($properties->getPublicProperties() as $publicProperty) {
            $this->publicProperties[$publicProperty->getName()] = true;
        }

        $this->setDefaultValue($this->publicProperties);
        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setStatic(true);
        $this->setDocBlock('@var bool[] map of public properties of the parent class');
    }

    public function isEmpty() : bool
    {
        return ! $this->publicProperties;
    }
}
