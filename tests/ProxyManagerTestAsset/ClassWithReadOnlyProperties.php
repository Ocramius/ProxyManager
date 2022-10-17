<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

use stdClass;

/**
 * Base test class to play around with read-only properties
 */
class ClassWithReadOnlyProperties
{
    public readonly string|stdClass $property0;
    protected readonly string $property1;
    private readonly string $property2;
}
