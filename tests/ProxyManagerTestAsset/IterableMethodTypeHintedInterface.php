<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

interface IterableMethodTypeHintedInterface
{
    public function returnIterable() : iterable;
}
