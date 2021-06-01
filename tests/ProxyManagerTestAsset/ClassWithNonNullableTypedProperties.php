<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

class ClassWithNonNullableTypedProperties
{
    private string $privateProperty;
    protected string $protectedProperty;
    public string $publicProperty;

    public function __construct(string $privateProperty, string $protectedProperty, string $publicProperty)
    {
        $this->privateProperty   = $privateProperty;
        $this->protectedProperty = $protectedProperty;
        $this->publicProperty    = $publicProperty;
    }

    public function getPrivateProperty(): string
    {
        return $this->privateProperty;
    }

    public function getProtectedProperty(): string
    {
        return $this->protectedProperty;
    }
}
