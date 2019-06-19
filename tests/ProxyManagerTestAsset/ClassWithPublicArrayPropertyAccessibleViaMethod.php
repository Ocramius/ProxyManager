<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Base test class to verify that proxies actually modify the array keys of
 * public properties that keep an array
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithPublicArrayPropertyAccessibleViaMethod
{
    /** @var mixed[] */
    public $arrayProperty = [];

    /** @return mixed[] */
    public function getArrayProperty() : array
    {
        return $this->arrayProperty;
    }
}
