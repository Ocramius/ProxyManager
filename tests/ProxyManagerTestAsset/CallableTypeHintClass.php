<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Class with a callable type hint in a method - used to test callable type hint
 * generation
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class CallableTypeHintClass
{
    /**
     * @param callable $parameter
     *
     * @return callable
     */
    public function callableTypeHintMethod(callable $parameter)
    {
        return $parameter;
    }
}
