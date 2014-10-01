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
use ProxyManager\Signature\ClassSignatureGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\Signature\ClassSignatureGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Signature\ClassSignatureGenerator
 * @group Coverage
 */
class ClassSignatureGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ProxyManager\Signature\SignatureGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $signatureGenerator;

    /**
     * @var ClassSignatureGenerator
     */
    private $classSignatureGenerator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->signatureGenerator      = $this->getMock('ProxyManager\\Signature\\SignatureGeneratorInterface');
        $this->classSignatureGenerator = new ClassSignatureGenerator($this->signatureGenerator);
    }

    public function testAddSignature()
    {
        /* @var $classGenerator \PHPUnit_Framework_MockObject_MockObject|\Zend\Code\Generator\ClassGenerator */
        $classGenerator = $this->getMock('Zend\\Code\\Generator\\ClassGenerator');

        $classGenerator
            ->expects($this->once())
            ->method('addPropertyFromGenerator')
            ->with($this->callback(function (PropertyGenerator $property) {
                return $property->getName() === 'signaturePropertyName'
                    && $property->isStatic()
                    && $property->getVisibility() === 'private'
                    && $property->getDefaultValue()->getValue() === 'valid-signature';
            }));

        $this
            ->signatureGenerator
            ->expects($this->any())
            ->method('generateSignature')
            ->with(array('foo' => 'bar'))
            ->will($this->returnValue('valid-signature'));

        $this
            ->signatureGenerator
            ->expects($this->any())
            ->method('generateSignatureKey')
            ->with(array('foo' => 'bar'))
            ->will($this->returnValue('PropertyName'));


        $this->classSignatureGenerator->addSignature($classGenerator, array('foo' => 'bar'));
    }
}
