<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset\RemoteProxy;

use stdClass;

/**
 * Simple interface for a remote API that methods with default parameters that came with PHP 8.1.0
 */
interface RemoteServiceWithPhp81DefaultsInterface
{
    public function php81Defaults($enum = FooEnum::bar, $object = new stdClass());
}
