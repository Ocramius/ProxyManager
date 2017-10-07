<?php

declare(strict_types=1);

namespace ProxyManagerTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\UnsupportedProxiedClassException;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ReflectionProperty;

/**
 * Tests for {@see \ProxyManager\Exception\UnsupportedProxiedClassException}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Exception\UnsupportedProxiedClassException
 * @group Coverage
 */
class UnsupportedProxiedClassExceptionTest extends TestCase
{
    /**
     * @covers \ProxyManager\Exception\UnsupportedProxiedClassException::unsupportedLocalizedReflectionProperty
     */
    public function testUnsupportedLocalizedReflectionProperty() : void
    {
        self::assertSame(
            'Provided reflection property "property0" of class "' . ClassWithPrivateProperties::class
            . '" is private and cannot be localized in PHP 5.3',
            UnsupportedProxiedClassException::unsupportedLocalizedReflectionProperty(
                new ReflectionProperty(ClassWithPrivateProperties::class, 'property0')
            )->getMessage()
        );
    }
}
