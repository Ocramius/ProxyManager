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

namespace ProxyManagerTest\Generator;

use PHPUnit_Framework_TestCase;
use ProxyManager\Generator\ClassGenerator;

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
    public function testExtendedClassesAreFQCNs()
    {
        $desiredFqcn     = '\\stdClass';
        $classNameInputs = array('stdClass', '\\stdClass\\');

        foreach ($classNameInputs as $className) {
            $classGenerator = new ClassGenerator();
            $classGenerator->setExtendedClass($className);

            $this->assertEquals($desiredFqcn, $classGenerator->getExtendedClass());
        }
    }

    /**
     * @covers \ProxyManager\Generator\ClassGenerator::setImplementedInterfaces
     */
    public function testImplementedInterfacesAreFQCNs()
    {
        $desiredFqcns        = array('\\Countable');
        $interfaceNameInputs = array(array('Countable'), array('\\Countable\\'));

        foreach ($interfaceNameInputs as $interfaceNames) {
            $classGenerator = new ClassGenerator();
            $classGenerator->setImplementedInterfaces($interfaceNames);

            $this->assertEquals($desiredFqcns, $classGenerator->getImplementedInterfaces());
        }
    }
}
