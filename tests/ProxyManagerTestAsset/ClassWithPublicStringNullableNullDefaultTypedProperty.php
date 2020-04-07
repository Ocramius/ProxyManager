<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Base test class to verify interactions with public typed properties
 * that are nullable and have a default value.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithPublicStringNullableNullDefaultTypedProperty
{
    public ?string $typedNullableNullDefaultProperty = null;
}
