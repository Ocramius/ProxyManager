<?php

declare(strict_types=1);

namespace ProxyManager\Inflector\Util;

use function md5;
use function serialize;

/**
 * Converts given parameters into a likely unique hash
 */
class ParameterHasher
{
    /**
     * Converts the given parameters into a likely-unique hash
     *
     * @param mixed[] $parameters
     */
    public function hashParameters(array $parameters) : string
    {
        return md5(serialize($parameters));
    }
}
