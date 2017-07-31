<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Class with a private property whose collides with the parent class' property naming
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithCollidingPrivateInheritedProperties extends ClassWithPrivateProperties
{
    /**
     * @var string
     */
    private $property0 = 'childClassProperty0';
}
