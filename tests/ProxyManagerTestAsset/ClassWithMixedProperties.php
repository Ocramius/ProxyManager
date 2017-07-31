<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Base test class to play around with mixed visibility properties
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithMixedProperties
{
    public static $publicStaticProperty       = 'publicStaticProperty';

    protected static $protectedStaticProperty = 'protectedStaticProperty';

    private static $privateStaticProperty     = 'privateStaticProperty';

    public $publicProperty0       = 'publicProperty0';

    public $publicProperty1       = 'publicProperty1';

    public $publicProperty2       = 'publicProperty2';

    protected $protectedProperty0 = 'protectedProperty0';

    protected $protectedProperty1 = 'protectedProperty1';

    protected $protectedProperty2 = 'protectedProperty2';

    private $privateProperty0     = 'privateProperty0';

    private $privateProperty1     = 'privateProperty1';

    private $privateProperty2     = 'privateProperty2';
}
