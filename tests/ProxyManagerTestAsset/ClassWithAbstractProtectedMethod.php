<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Class with one abstract protected method
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
abstract class ClassWithAbstractProtectedMethod
{
    /**
     * @return void
     */
    abstract protected function protectedAbstractMethod();
}
