--TEST--
Verifies that generated access interceptors doesn't throw PHP Warning on Serialized class protected property direct unset
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen implements \Serializable
{
    protected $sweets = 'candy';

    function serialize(): ?string
    {
        return $this->sweets;
    }

    function unserialize(string $serialized): void
    {
        $this->sweets = $serialized;
    }
}

$factory = new \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

unset($proxy->sweets);
?>
--EXPECTF--
Deprecated: Kitchen implements the Serializable interface, which is deprecated. Implement __serialize() and __unserialize() instead (or in addition, if support for old PHP versions is necessary) in Standard input code on line 5

Deprecated: ProxyManagerGeneratedProxy\__PM__\Kitchen\Generated%s implements the Serializable interface, which is deprecated. Implement __serialize() and __unserialize() instead (or in addition, if support for old PHP versions is necessary) in %s

%SFatal error:%sCannot %s property%sin %a
