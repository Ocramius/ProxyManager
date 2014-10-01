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

namespace ProxyManagerTest\Signature;

use PHPUnit_Framework_TestCase;
use ProxyManager\Signature\SignatureChecker;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\Signature\SignatureChecker}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Signature\SignatureChecker
 * @group Coverage
 */
class SignatureCheckerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $signatureExample = 'valid-signature';

    /**
     * @var SignatureChecker
     */
    private $signatureChecker;

    /**
     * @var \ProxyManager\Signature\SignatureGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $signatureGenerator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->signatureGenerator = $this->getMock('ProxyManager\Signature\SignatureGeneratorInterface');
        $this->signatureChecker   = new SignatureChecker($this->signatureGenerator);
    }

    public function testCheckSignatureWithValidKey()
    {
        $this
            ->signatureGenerator
            ->expects($this->atLeastOnce())
            ->method('generateSignatureKey')
            ->with(array('foo' => 'bar'))
            ->will($this->returnValue('Example'));
        $this
            ->signatureGenerator
            ->expects($this->atLeastOnce())
            ->method('generateSignature')
            ->with(array('foo' => 'bar'))
            ->will($this->returnValue('valid-signature'));

        $this->signatureChecker->checkSignature(new ReflectionClass($this), array('foo' => 'bar'));
    }

    public function testCheckSignatureWithInvalidKey()
    {
        $this
            ->signatureGenerator
            ->expects($this->any())
            ->method('generateSignatureKey')
            ->with(array('foo' => 'bar'))
            ->will($this->returnValue('InvalidKey'));
        $this
            ->signatureGenerator
            ->expects($this->any())
            ->method('generateSignature')
            ->with(array('foo' => 'bar'))
            ->will($this->returnValue('valid-signature'));

        $this->setExpectedException('ProxyManager\Signature\Exception\MissingSignatureException');

        $this->signatureChecker->checkSignature(new ReflectionClass($this), array('foo' => 'bar'));
    }

    public function testCheckSignatureWithInvalidValue()
    {
        $this
            ->signatureGenerator
            ->expects($this->any())
            ->method('generateSignatureKey')
            ->with(array('foo' => 'bar'))
            ->will($this->returnValue('Example'));
        $this
            ->signatureGenerator
            ->expects($this->any())
            ->method('generateSignature')
            ->with(array('foo' => 'bar'))
            ->will($this->returnValue('invalid-signature'));

        $this->setExpectedException('ProxyManager\Signature\Exception\InvalidSignatureException');

        $this->signatureChecker->checkSignature(new ReflectionClass($this), array('foo' => 'bar'));
    }
}
