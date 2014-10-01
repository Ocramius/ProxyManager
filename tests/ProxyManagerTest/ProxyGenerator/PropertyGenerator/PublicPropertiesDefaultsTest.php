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

namespace ProxyManagerTest\ProxyGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesDefaults;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesDefaults}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesDefaults
 * @group Coverage
 */
class PublicPropertiesDefaultsTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyClass()
    {
        $publicProperties = new PublicPropertiesDefaults(new ReflectionClass('ProxyManagerTestAsset\\EmptyClass'));

        $this->assertInternalType('array', $publicProperties->getDefaultValue()->getValue());
        $this->assertEmpty($publicProperties->getDefaultValue()->getValue());
        $this->assertTrue($publicProperties->isStatic());
        $this->assertSame(PublicPropertiesDefaults::VISIBILITY_PRIVATE, $publicProperties->getVisibility());
    }

    public function testClassWithPublicProperties()
    {
        $publicProperties = new PublicPropertiesDefaults(
            new ReflectionClass('ProxyManagerTestAsset\\ClassWithPublicProperties')
        );

        $this->assertInternalType('array', $publicProperties->getDefaultValue()->getValue());
        $this->assertCount(10, $publicProperties->getDefaultValue()->getValue());
        $this->assertTrue($publicProperties->isStatic());
        $this->assertSame(PublicPropertiesDefaults::VISIBILITY_PRIVATE, $publicProperties->getVisibility());
    }

    public function testBaseClass()
    {
        $publicProperties = new PublicPropertiesDefaults(
            new ReflectionClass('ProxyManagerTestAsset\\BaseClass')
        );

        $this->assertInternalType('array', $publicProperties->getDefaultValue()->getValue());
        $this->assertSame(
            array('publicProperty' => 'publicPropertyDefault'),
            $publicProperties->getDefaultValue()->getValue()
        );
        $this->assertTrue($publicProperties->isStatic());
        $this->assertSame(PublicPropertiesDefaults::VISIBILITY_PRIVATE, $publicProperties->getVisibility());
    }
}
