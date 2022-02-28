<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

class ReferenceIncrementDecrementClass
{
    public function incrementReference(int & $reference): void
    {
        $reference += 1;
    }
}
