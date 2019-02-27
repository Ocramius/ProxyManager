<?php

declare(strict_types=1);

namespace ProxyManagerTest;

use ReflectionObject;

/**
 * @internal
 */
final class Assert
{
    /**
     * @return mixed
     */
    public static function readAttribute(object $object, string $propertyName)
    {
        $reflection = new ReflectionObject($object);
        $property   = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
