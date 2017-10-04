<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Test asset class with method with default values
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithMethodWithDefaultParameters
{
    /**
     * @param array $parameter
     *
     * @return string
     */
    public function publicMethodWithDefaults(array $parameter = ['foo'])
    {
        return 'defaultValue';
    }
}
