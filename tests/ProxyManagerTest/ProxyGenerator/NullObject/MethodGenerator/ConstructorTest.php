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

namespace ProxyManagerTest\ProxyGenerator\NullObject\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\NullObject\MethodGenerator\Constructor;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\NullObject\MethodGenerator\Constructor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class ConstructorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\NullObject\MethodGenerator\Constructor::__construct
     */
    public function testBodyStructure()
    {
        $reflection  = new ReflectionClass('ProxyManagerTestAsset\\ClassWithMixedProperties');
        $constructor = new Constructor($reflection);

        $this->assertSame('__construct', $constructor->getName());
        $this->assertCount(0, $constructor->getParameters());
        $this->assertSame(
            "\$this->publicProperty0 = null;\n\$this->publicProperty1 = null;\n\$this->publicProperty2 = null;",
            $constructor->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\NullObject\MethodGenerator\Constructor::__construct
     */
    public function testBodyStructureWithoutPublicProperties()
    {
        $reflection  = new ReflectionClass('ProxyManagerTestAsset\\ClassWithPrivateProperties');
        $constructor = new Constructor($reflection);

        $this->assertSame('__construct', $constructor->getName());
        $this->assertCount(0, $constructor->getParameters());
        $body = $constructor->getBody();
        $this->assertTrue(empty($body));
    }
}
