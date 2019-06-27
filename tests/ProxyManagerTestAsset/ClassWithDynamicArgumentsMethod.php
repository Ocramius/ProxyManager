<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Class with a method using dynamic arguments
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithDynamicArgumentsMethod
{
    /**
     * @param mixed ...$args
     *
     * @return mixed[]
     */
    public function dynamicArgumentsMethod()
    {
        return func_get_args();
    }
}
