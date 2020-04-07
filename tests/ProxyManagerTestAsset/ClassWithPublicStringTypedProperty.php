<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Base test class to verify interactions with public typed properties
 * that do not have default values.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithPublicStringTypedProperty
{
    public string $typedProperty;
}
