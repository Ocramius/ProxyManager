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
    /** @var string */
    public static $publicStaticProperty       = 'publicStaticProperty';

    /** @var string */
    protected static $protectedStaticProperty = 'protectedStaticProperty';

    /** @var string */
    private static $privateStaticProperty     = 'privateStaticProperty';

    /** @var string */
    public $publicProperty0       = 'publicProperty0';

    /** @var string */
    public $publicProperty1       = 'publicProperty1';

    /** @var string */
    public $publicProperty2       = 'publicProperty2';

    /** @var string */
    protected $protectedProperty0 = 'protectedProperty0';

    /** @var string */
    protected $protectedProperty1 = 'protectedProperty1';

    /** @var string */
    protected $protectedProperty2 = 'protectedProperty2';

    /** @var string */
    private $privateProperty0     = 'privateProperty0';

    /** @var string */
    private $privateProperty1     = 'privateProperty1';

    /** @var string */
    private $privateProperty2     = 'privateProperty2';
}
