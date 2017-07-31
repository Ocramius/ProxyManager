<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Class with a `self` type hint in a method - used to test overriding method with the `self` type hint in generators
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithSelfHint
{
    public function selfHintMethod(self $parameter)
    {
        return $parameter;
    }
}
