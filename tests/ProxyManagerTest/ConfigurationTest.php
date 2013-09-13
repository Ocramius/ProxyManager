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

namespace ProxyManagerTest;

use PHPUnit_Framework_TestCase;
use ProxyManager\Configuration;

/**
 * Tests for {@see \ProxyManager\Configuration}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ProxyManager\Configuration
     */
    protected $configuration;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->configuration = new Configuration();
    }

    /**
     * @covers \ProxyManager\Configuration::getProxiesNamespace
     * @covers \ProxyManager\Configuration::setProxiesNamespace
     */
    public function testGetSetProxiesNamespace()
    {
        $this->assertSame(
            'ProxyManagerGeneratedProxy',
            $this->configuration->getProxiesNamespace(),
            'Default setting check for BC'
        );

        $this->configuration->setProxiesNamespace('foo');
        $this->assertSame('foo', $this->configuration->getProxiesNamespace());
    }

    /**
     * @covers \ProxyManager\Configuration::getClassNameInflector
     * @covers \ProxyManager\Configuration::setClassNameInflector
     */
    public function testSetGetClassNameInflector()
    {
        $this->assertInstanceOf(
            'ProxyManager\\Inflector\\ClassNameInflectorInterface',
            $this->configuration->getClassNameInflector()
        );

        $inflector = $this->getMock('ProxyManager\\Inflector\\ClassNameInflectorInterface');

        $this->configuration->setClassNameInflector($inflector);
        $this->assertSame($inflector, $this->configuration->getClassNameInflector());
    }

    /**
     * @covers \ProxyManager\Configuration::getGeneratorStrategy
     * @covers \ProxyManager\Configuration::setGeneratorStrategy
     */
    public function testSetGetGeneratorStrategy()
    {

        $this->assertInstanceOf(
            'ProxyManager\\GeneratorStrategy\\GeneratorStrategyInterface',
            $this->configuration->getGeneratorStrategy()
        );

        $strategy = $this->getMock('ProxyManager\\GeneratorStrategy\\GeneratorStrategyInterface');

        $this->configuration->setGeneratorStrategy($strategy);
        $this->assertSame($strategy, $this->configuration->getGeneratorStrategy());
    }

    /**
     * @covers \ProxyManager\Configuration::getProxiesTargetDir
     * @covers \ProxyManager\Configuration::setProxiesTargetDir
     */
    public function testSetGetProxiesTargetDir()
    {
        $this->assertTrue(is_dir($this->configuration->getProxiesTargetDir()));

        $this->configuration->setProxiesTargetDir(__DIR__);
        $this->assertSame(__DIR__, $this->configuration->getProxiesTargetDir());
    }

    /**
     * @covers \ProxyManager\Configuration::getProxyAutoloader
     * @covers \ProxyManager\Configuration::setProxyAutoloader
     */
    public function testSetGetProxyAutoloader()
    {
        $this->assertInstanceOf(
            'ProxyManager\\Autoloader\\AutoloaderInterface',
            $this->configuration->getProxyAutoloader()
        );

        $autoloader = $this->getMock('ProxyManager\\Autoloader\\AutoloaderInterface');

        $this->configuration->setProxyAutoloader($autoloader);
        $this->assertSame($autoloader, $this->configuration->getProxyAutoloader());
    }
}
