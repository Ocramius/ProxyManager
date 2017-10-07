<?php

declare(strict_types=1);

namespace ProxyManagerTest\Generator;

use Countable;
use PHPUnit\Framework\TestCase;
use ProxyManager\Generator\ClassGenerator;
use stdClass;

/**
 * Tests for {@see \ProxyManager\Generator\ClassGenerator}
 *
 * @author Gordon Stratton <gordon.stratton@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class ClassGeneratorTest extends TestCase
{
    /**
     * @covers \ProxyManager\Generator\ClassGenerator::setExtendedClass
     */
    public function testExtendedClassesAreFQCNs() : void
    {
        $desiredFqcn     = '\\stdClass';
        $classNameInputs = [stdClass::class, '\\stdClass\\'];

        foreach ($classNameInputs as $className) {
            $classGenerator = new ClassGenerator();
            $classGenerator->setExtendedClass($className);

            self::assertEquals($desiredFqcn, $classGenerator->getExtendedClass());
        }
    }

    /**
     * @covers \ProxyManager\Generator\ClassGenerator::setImplementedInterfaces
     */
    public function testImplementedInterfacesAreFQCNs() : void
    {
        $desiredFqcns        = ['\\Countable'];
        $interfaceNameInputs = [[Countable::class], ['\\Countable\\']];

        foreach ($interfaceNameInputs as $interfaceNames) {
            $classGenerator = new ClassGenerator();
            $classGenerator->setImplementedInterfaces($interfaceNames);

            self::assertEquals($desiredFqcns, $classGenerator->getImplementedInterfaces());
        }
    }
}
