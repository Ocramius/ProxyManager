<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator;

use ProxyManager\Generator\Util\IdentifierSuffixer;
use ProxyManager\ProxyGenerator\Util\Properties;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Property that contains the initializer for a lazy object
 */
class PrivatePropertiesMap extends PropertyGenerator
{
    public const KEY_DEFAULT_VALUE = 'defaultValue';

    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Properties $properties)
    {
        parent::__construct(
            IdentifierSuffixer::getIdentifier('privateProperties')
        );

        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setStatic(true);
        $this->setDocBlock(
            '@var array[][] visibility and default value of defined properties, indexed by property name and class name'
        );
        $this->setDefaultValue($this->getMap($properties));
    }

    /**
     * @return array<string, array<class-string, bool>>
     */
    private function getMap(Properties $properties) : array
    {
        $map = [];

        foreach ($properties->getPrivateProperties() as $property) {
            $map[$property->getName()][$property->getDeclaringClass()->getName()] = true;
        }

        return $map;
    }
}
