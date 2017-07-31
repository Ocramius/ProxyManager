<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

class ScalarTypeHintedClass
{
    public function acceptString(string $param)
    {
        return $param;
    }

    public function acceptInteger(int $param)
    {
        return $param;
    }

    public function acceptBoolean(bool $param)
    {
        return $param;
    }

    public function acceptFloat(float $param)
    {
        return $param;
    }
}
