<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator;

use ProxyManager\Generator\Util\IdentifierSuffixer;
use ReflectionClass;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Property that contains the wrapped value of a lazy loading proxy
 */
class ValueHolderProperty extends PropertyGenerator
{
    /**
     * Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __construct(ReflectionClass $type)
    {
        parent::__construct(IdentifierSuffixer::getIdentifier('valueHolder'));

        $docBlock = new DocBlockGenerator();

        $docBlock->setWordWrap(false);
        $docBlock->setLongDescription('@var \\' . $type->getName() . '|null wrapped object, if the proxy is initialized');
        $this->setDocBlock($docBlock);
        $this->setVisibility(self::VISIBILITY_PRIVATE);
    }
}
