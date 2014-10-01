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

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class SetProxyInitializerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer::__construct
     */
    public function testBodyStructure()
    {
        $initializer = $this->getMock('Zend\\Code\\Generator\\PropertyGenerator');

        $initializer->expects($this->any())->method('getName')->will($this->returnValue('foo'));

        $setter     = new SetProxyInitializer($initializer);
        $parameters = $setter->getParameters();

        $this->assertSame('setProxyInitializer', $setter->getName());
        $this->assertCount(1, $parameters);

        /* @var $initializer \ProxyManager\Generator\ParameterGenerator */
        $initializer = array_shift($parameters);

        $this->assertInstanceOf('ProxyManager\\Generator\\ParameterGenerator', $initializer);
        $this->assertSame('initializer', $initializer->getName());
        $this->assertSame('$this->foo = $initializer;', $setter->getBody());
    }
}
