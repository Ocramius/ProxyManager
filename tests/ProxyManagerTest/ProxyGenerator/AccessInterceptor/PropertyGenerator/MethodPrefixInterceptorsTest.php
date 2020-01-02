<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptor\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors}
 *
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors
 * @group Coverage
 */
final class MethodPrefixInterceptorsTest extends AbstractUniquePropertyNameTest
{
    /**
     * {@inheritDoc}
     */
    protected function createProperty() : PropertyGenerator
    {
        return new MethodPrefixInterceptors();
    }
}
