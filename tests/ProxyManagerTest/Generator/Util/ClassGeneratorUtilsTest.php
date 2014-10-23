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

namespace ProxyManagerTest\Generator\Util;

use ReflectionClass;
use PHPUnit_Framework_TestCase;
use ProxyManager\Generator\Util\ClassGeneratorUtils;

/**
 * Test to {@see ProxyManager\Generator\Util\ClassGeneratorUtils}
 *
 * @author Jefersson Nathan <malukenho@phpse.net>
 * @license MIT
 *
 * @covers ProxyManager\Generator\Util\ClassGeneratorUtils
 */
class ClassGeneratorUtilsTest extends PHPUnit_Framework_TestCase
{
    public function testCantAddAFinalMethod()
    {
        $classGenerator  = $this->getMock('Zend\\Code\\Generator\\ClassGenerator');
        $methodGenerator = $this->getMock('Zend\\Code\\Generator\\MethodGenerator');

        $methodGenerator
            ->expects($this->once())
            ->method('getName')
            ->willReturn('foo');

        $classGenerator
            ->expects($this->never())
            ->method('addMethodFromGenerator');

        $reflection = new ReflectionClass('ProxyManagerTestAsset\\ClassWithFinalMethods');

        ClassGeneratorUtils::addMethodIfNotFinal($reflection, $classGenerator, $methodGenerator);
    }

    public function testCanAddANotFinalMethod()
    {
        $classGenerator  = $this->getMock('Zend\\Code\\Generator\\ClassGenerator');
        $methodGenerator = $this->getMock('Zend\\Code\\Generator\\MethodGenerator');

        $methodGenerator
            ->expects($this->once())
            ->method('getName')
            ->willReturn('publicMethod');

        $classGenerator
            ->expects($this->once())
            ->method('addMethodFromGenerator');

        $reflection = new ReflectionClass('ProxyManagerTestAsset\\BaseClass');

        ClassGeneratorUtils::addMethodIfNotFinal($reflection, $classGenerator, $methodGenerator);
    }
}
