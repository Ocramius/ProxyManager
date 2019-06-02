<?php

declare(strict_types=1);

namespace ProxyManager\Inflector\Util;

use function base64_encode;
use function serialize;

/**
 * Encodes parameters into a class-name safe string
 */
class ParameterEncoder
{
    /**
     * Converts the given parameters into a set of characters that are safe to
     * use in a class name
     *
     * @param mixed[] $parameters
     */
    public function encodeParameters(array $parameters) : string
    {
        return base64_encode(serialize($parameters));
    }
}
