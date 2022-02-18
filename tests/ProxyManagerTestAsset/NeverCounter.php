<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

use RuntimeException;

class NeverCounter
{
    /**
     * @var int
     */
    public $counter = 0;

    public function increment(int $amount) : never
    {
        $this->counter += $amount;
        throw new RuntimeException();
    }
}
