<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Test object to be hydrated
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class HydratedObject
{
    /**
     * @var mixed
     */
    public $foo = 1;

    /**
     * @var mixed
     */
    protected $bar = 2;

    /**
     * @var mixed
     */
    private $baz = 3;

    /**
     * Method to be disabled
     */
    public function doFoo()
    {
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }
}
