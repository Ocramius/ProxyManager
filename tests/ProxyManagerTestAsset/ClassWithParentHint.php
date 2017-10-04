<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Class with a `parent` type hint in a method - used to test overriding method with the `parent` type in generators
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithParentHint extends EmptyClass
{
    public function parentHintMethod(parent $parameter)
    {
        return $parameter;
    }
}
