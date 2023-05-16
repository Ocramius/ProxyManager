<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Class with a promoted constructor properties
 *
 * @license MIT
 */
class ClassWithPromotedProperties
{
    public function __construct(
        protected int $amount,
        protected ?int $nullableAmount
    ) {
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getNullableAmount(): ?int
    {
        return $this->nullableAmount;
    }
}
