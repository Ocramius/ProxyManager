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

use ReflectionClass;
use PHPUnit_Framework_TestCase;
use ProxyManager\Generator\MagicMethodGenerator;

/**
 * Tests for {@see \ProxyManager\Generator\MagicMethodGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MagicMethodGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\Generator\MagicMethodGenerator::__construct
     */
    public function testGeneratesCorrectByRefReturnValue()
    {
        $reflection  = new ReflectionClass('ProxyManagerTestAsset\\ClassWithByRefMagicMethods');
        $magicMethod = new MagicMethodGenerator($reflection, '__get', array('name'));

        $this->assertTrue($magicMethod->returnsReference());
    }

    /**
     * @covers \ProxyManager\Generator\MagicMethodGenerator::__construct
     */
    public function testGeneratesCorrectByValReturnValue()
    {
        $reflection  = new ReflectionClass('ProxyManagerTestAsset\\ClassWithMagicMethods');
        $magicMethod = new MagicMethodGenerator($reflection, '__get', array('name'));

        $this->assertFalse($magicMethod->returnsReference());
    }
}
