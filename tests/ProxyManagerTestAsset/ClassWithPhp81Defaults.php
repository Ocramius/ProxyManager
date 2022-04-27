<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

use stdClass;

enum FooEnum
{
    case bar;
}

/**
 * Base test class to play around with new default values that came with PHP 8.1.0
 */
class ClassWithPhp81Defaults
{
    public FooEnum $enum = FooEnum::bar;

    public function __construct($enum = FooEnum::bar, $object = new stdClass())
    {
    }
}
