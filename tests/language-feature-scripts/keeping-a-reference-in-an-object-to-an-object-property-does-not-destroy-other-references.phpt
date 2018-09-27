--TEST--
Keeping a reference to an object property in another object should not override existing references
?>
--FILE--
<?php

class Container
{
    /** @var \stdClass|null */
    public $referencedProperty;

    public function __clone()
    {
        $this->referencedProperty = clone $this->referencedProperty;
    }
}

$references = new stdClass();

$container = new Container();

$loadFieldByReference = function (& $referencedProperty) use (& $references) {
    $referencedProperty    = new \stdClass();
    $references->reference = &$referencedProperty;
};

$loadFieldByReference($container->referencedProperty);

$container->referencedProperty->publicProperty = 123;
$clone                                         = clone $container;
$clone->referencedProperty->publicProperty     = 234;

echo $container->referencedProperty->publicProperty, "\n";
echo $clone->referencedProperty->publicProperty, "\n";
echo $container->referencedProperty === $clone->referencedProperty ? "same\n" : "not same\n";

?>
--EXPECT--
234
234
same
