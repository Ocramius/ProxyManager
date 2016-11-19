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

namespace ProxyManagerTest\ProxyGenerator\Util;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\Util\GetMethodIfExists;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\GetMethodIfExists}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Util\GetMethodIfExists
 * @group Coverage
 */
class GetMethodIfExistsTest extends PHPUnit_Framework_TestCase
{
    public function testGetExistingMethod() : void
    {
        $method = GetMethodIfExists::get(new \ReflectionClass(self::class), 'testGetExistingMethod');

        self::assertInstanceOf(\ReflectionMethod::class, $method);
        self::assertSame('testGetExistingMethod', $method->getName());
        self::assertSame(self::class, $method->getDeclaringClass()->getName());
    }

    public function testGetNonExistingMethod() : void
    {
        self::assertNull(GetMethodIfExists::get(new \ReflectionClass(self::class), uniqid('nonExisting', true)));
    }
}
