<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Class with a iterable type hint in a method - used to test iterable type hint generation
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class IterableTypeHintClass
{
    /**
     * @param iterable $parameter
     *
     * @return iterable
     */
    public function iterableTypeHintMethod(iterable $parameter)
    {
        return $parameter;
    }
}
