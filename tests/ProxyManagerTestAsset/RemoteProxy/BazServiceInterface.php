<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset\RemoteProxy;

/**
 * Simple interface for a remote API
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
interface BazServiceInterface
{
    /**
     * @param string $param
     *
     * @return string
     */
    public function baz($param);
}
