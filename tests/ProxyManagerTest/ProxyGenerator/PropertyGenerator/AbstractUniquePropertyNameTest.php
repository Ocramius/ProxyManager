<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\PropertyGenerator;

use PHPUnit\Framework\TestCase;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Base test for unique property names
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
abstract class AbstractUniquePropertyNameTest extends TestCase
{
    /**
     * Verifies that a given property name is the same across two different instantiations of the property
     */
    public function testUniqueProperty() : void
    {
        $property1 = $this->createProperty();
        $property2 = $this->createProperty();

        self::assertSame($property1->getName(), $property1->getName());
        self::assertSame($property1->getName(), $property2->getName());
    }

    abstract protected function createProperty() : PropertyGenerator;
}
