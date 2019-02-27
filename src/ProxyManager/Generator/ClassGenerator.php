<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use Zend\Code\Generator\ClassGenerator as ZendClassGenerator;
use function trim;

/**
 * Class generator that ensures that interfaces/classes that are implemented/extended are FQCNs
 */
class ClassGenerator extends ZendClassGenerator
{
    /**
     * {@inheritDoc}
     */
    public function setExtendedClass($extendedClass) : parent
    {
        if ($extendedClass) {
            $extendedClass = '\\' . trim($extendedClass, '\\');
        }

        return parent::setExtendedClass($extendedClass);
    }

    /**
     * {@inheritDoc}
     */
    public function setImplementedInterfaces(array $interfaces) : parent
    {
        foreach ($interfaces as & $interface) {
            $interface = '\\' . trim($interface, '\\');
        }

        return parent::setImplementedInterfaces($interfaces);
    }
}
