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

use PHPUnit_Framework_TestCase;
use ProxyManager\Generator\MagicMethodGenerator;
use ProxyManagerTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\Generator\MagicMethodGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class MagicMethodGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\Generator\MagicMethodGenerator::__construct
     */
    public function testGeneratesCorrectByRefReturnValue() : void
    {
        $reflection  = new ReflectionClass(ClassWithByRefMagicMethods::class);
        $magicMethod = new MagicMethodGenerator($reflection, '__get', ['name']);

        self::assertStringMatchesFormat('%Apublic function & __get(%A', $magicMethod->generate());
    }

    /**
     * @covers \ProxyManager\Generator\MagicMethodGenerator::__construct
     */
    public function testGeneratesCorrectByValReturnValue() : void
    {
        $reflection  = new ReflectionClass(ClassWithMagicMethods::class);
        $magicMethod = new MagicMethodGenerator($reflection, '__get', ['name']);

        self::assertStringMatchesFormat('%Apublic function __get(%A', $magicMethod->generate());
    }
}
