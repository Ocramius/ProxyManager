<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\RemoteObject\PropertyGenerator;

use ProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty
 * @group Coverage
 */
class AdapterPropertyTest extends AbstractUniquePropertyNameTest
{
    /**
     * {@inheritDoc}
     */
    protected function createProperty() : PropertyGenerator
    {
        return new AdapterProperty();
    }
}
