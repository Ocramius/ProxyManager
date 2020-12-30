<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use Laminas\Code\Generator\ClassGenerator as LaminasClassGenerator;

use function array_map;
use function trim;

/**
 * Class generator that ensures that interfaces/classes that are implemented/extended are FQCNs
 */
class ClassGenerator extends LaminasClassGenerator
{
    /**
     * {@inheritDoc}
     */
    public function setExtendedClass($extendedClass): LaminasClassGenerator
    {
        if ($extendedClass) {
            $extendedClass = '\\' . trim($extendedClass, '\\');
        }

        return parent::setExtendedClass($extendedClass);
    }

    /**
     * {@inheritDoc}
     *
     * @param array<int, string> $interfaces
     *
     * @psalm-suppress MoreSpecificImplementedParamType parent interface does not specify type of array values
     */
    public function setImplementedInterfaces(array $interfaces): LaminasClassGenerator
    {
        return parent::setImplementedInterfaces(array_map(
            static function (string $interface): string {
                return '\\' . trim($interface, '\\');
            },
            $interfaces
        ));
    }
}
