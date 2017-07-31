<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Base interface used to verify that the proxy generators can actually work with interfaces
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
interface BaseInterface
{
    /**
     * @return string
     */
    public function publicMethod();
}
