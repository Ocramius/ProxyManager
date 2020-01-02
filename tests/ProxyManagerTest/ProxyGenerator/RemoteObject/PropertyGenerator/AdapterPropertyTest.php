<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\RemoteObject\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty}
 *
 * @covers \ProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty
 * @group Coverage
 */
final class AdapterPropertyTest extends AbstractUniquePropertyNameTest
{
    /**
     * {@inheritDoc}
     */
    protected function createProperty() : PropertyGenerator
    {
        return new AdapterProperty();
    }
}
