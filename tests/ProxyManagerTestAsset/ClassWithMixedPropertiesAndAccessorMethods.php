<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Class with one abstract public method
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithMixedPropertiesAndAccessorMethods
{
    /**
     * @var mixed
     */
    public $publicProperty = 'publicProperty';

    /**
     * @var mixed
     */
    protected $protectedProperty = 'protectedProperty';

    /**
     * @var mixed
     */
    private $privateProperty = 'privateProperty';

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->$name);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->$name;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function remove($name)
    {
        unset($this->$name);
    }
}
