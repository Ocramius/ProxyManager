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

namespace ProxyManagerTest\Exception;

use PHPUnit_Framework_TestCase;
use ProxyManager\Exception\UnsupportedProxiedClassException;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ReflectionProperty;

/**
 * Tests for {@see \ProxyManager\Exception\UnsupportedProxiedClassException}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Exception\UnsupportedProxiedClassException
 * @group Coverage
 */
class UnsupportedProxiedClassExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\Exception\UnsupportedProxiedClassException::unsupportedLocalizedReflectionProperty
     */
    public function testUnsupportedLocalizedReflectionProperty()
    {
        self::assertSame(
            'Provided reflection property "property0" of class "' . ClassWithPrivateProperties::class
            . '" is private and cannot be localized in PHP 5.3',
            UnsupportedProxiedClassException::unsupportedLocalizedReflectionProperty(
                new ReflectionProperty(ClassWithPrivateProperties::class, 'property0')
            )->getMessage()
        );
    }
}
