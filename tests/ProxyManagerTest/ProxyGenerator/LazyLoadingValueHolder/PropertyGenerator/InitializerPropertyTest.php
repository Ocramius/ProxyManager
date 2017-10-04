<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator;

use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty
 * @group Coverage
 */
class InitializerPropertyTest extends AbstractUniquePropertyNameTest
{
    /**
     * {@inheritDoc}
     */
    protected function createProperty() : PropertyGenerator
    {
        return new InitializerProperty();
    }
}
