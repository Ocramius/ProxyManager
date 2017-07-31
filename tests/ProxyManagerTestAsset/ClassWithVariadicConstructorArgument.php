<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Test asset class with a constructor with variadic arguments
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithVariadicConstructorArgument
{
    /**
     * @var mixed
     */
    private $foo;

    /**
     * @var array
     */
    private $bar;

    /**
     * ClassWithVariadicConstructorArguments constructor.
     *
     * @param mixed $foo
     * @param array ...$bar
     */
    public function __construct($foo, ... $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
