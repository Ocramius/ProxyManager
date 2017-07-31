<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Class used to verify that accessing protected scope of other objects still triggers lazy loading/interception
 */
class OtherObjectAccessClass
{
    private $privateProperty = 'privateProperty';

    protected $protectedProperty = 'protectedProperty';

    public $publicProperty = 'publicProperty';

    public function getPrivateProperty(self $other) : string
    {
        return $other->privateProperty;
    }

    public function getProtectedProperty(self $other) : string
    {
        return $other->protectedProperty;
    }

    public function getPublicProperty(self $other) : string
    {
        return $other->publicProperty;
    }
}
