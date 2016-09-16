<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace ProxyManagerTest\Generator;

use Countable;
use PHPUnit_Framework_TestCase;
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
class ClassGeneratorTest extends PHPUnit_Framework_TestCase
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
