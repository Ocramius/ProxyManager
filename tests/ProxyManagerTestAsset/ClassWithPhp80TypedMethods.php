<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

use BadMethodCallException;

/**
 * Base test class to play around with new method type definitions that came with PHP 8.0.0
 */
class ClassWithPhp80TypedMethods
{
    public function mixedType(mixed $parameter): mixed
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    /** Note: the false type cannot be used standalone, and must be part of a union type */
    public function falseType(false|self $parameter): false|self
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    public function unionNullableType(bool|null $parameter): bool|null
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    public function unionReverseNullableType(null|bool $parameter): null|bool
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    public function unionNullableTypeWithDefaultValue(bool|string|null $parameter = null): bool|string|null
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    public function unionType(ClassWithPhp80TypedMethods|\stdClass $parameter): ClassWithPhp80TypedMethods|\stdClass
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    public function staticType(self $parameter): static
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    public function selfAndBoolType(self|bool $parameter): self|bool
    {
        throw new BadMethodCallException('Not supposed to be run');
    }
}
