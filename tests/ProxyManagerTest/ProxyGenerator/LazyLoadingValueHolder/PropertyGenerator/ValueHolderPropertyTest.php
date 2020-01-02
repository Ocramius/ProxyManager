<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty}
 *
 * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty
 * @group Coverage
 */
final class ValueHolderPropertyTest extends AbstractUniquePropertyNameTest
{
    /**
     * {@inheritDoc}
     */
    protected function createProperty() : PropertyGenerator
    {
        return new ValueHolderProperty(new ReflectionClass(self::class));
    }

    /** @group #400 */
    public function testWillDocumentPropertyType() : void
    {
        $docBlock = (new ValueHolderProperty(new ReflectionClass(self::class)))->getDocBlock();

        self::assertNotNull($docBlock);
        self::assertEquals(
            <<<'PHPDOC'
/**
 * @var \ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderPropertyTest|null wrapped object, if the proxy is initialized
 */

PHPDOC
            ,
            $docBlock->generate()
        );
    }
}
