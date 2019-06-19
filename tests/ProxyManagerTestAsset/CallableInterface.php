<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

interface CallableInterface
{
    /**
     * @param mixed $params
     *
     * @return mixed
     */
    public function __invoke(...$params);
}
