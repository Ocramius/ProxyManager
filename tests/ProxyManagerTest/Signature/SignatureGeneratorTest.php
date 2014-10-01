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
use ProxyManager\Signature\SignatureGenerator;

/**
 * Tests for {@see \ProxyManager\Signature\SignatureGenerator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\Signature\SignatureGenerator
 * @group Coverage
 */
class SignatureGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SignatureGenerator
     */
    private $signatureGenerator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->signatureGenerator = new SignatureGenerator;
    }

    /**
     * @param array  $parameters
     * @param string $expected
     *
     * @dataProvider signatures
     */
    public function testGenerateSignature(array $parameters, $expected)
    {
        $this->assertSame($expected, $this->signatureGenerator->generateSignature($parameters));
    }

    /**
     * @param array  $parameters
     * @param string $expected
     *
     * @dataProvider signatureKeys
     */
    public function testGenerateSignatureKey(array $parameters, $expected)
    {
        $this->assertSame($expected, $this->signatureGenerator->generateSignatureKey($parameters));
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function signatures()
    {
        return array(
            array(
                array(),
                'YTowOnt9'
            ),
            array(
                array('foo' => 'bar'),
                'YToxOntzOjM6ImZvbyI7czozOiJiYXIiO30='
            ),
            array(
                array('foo' => 'bar', 'baz' => 'tab'),
                'YToyOntzOjM6ImZvbyI7czozOiJiYXIiO3M6MzoiYmF6IjtzOjM6InRhYiI7fQ=='
            ),
            array(
                array('bar'),
                'YToxOntpOjA7czozOiJiYXIiO30='
            ),
            array(
                array('bar', 'baz'),
                'YToyOntpOjA7czozOiJiYXIiO2k6MTtzOjM6ImJheiI7fQ=='
            ),
        );
    }

    /**
     * Data provider.
     *
     * @return array[]
     */
    public function signatureKeys()
    {
        return array(
            array(array(), '40cd750bba9870f18aada2478b24840a'),
            array(array('foo' => 'bar'), '49a3696adf0fbfacc12383a2d7400d51'),
            array(array('foo' => 'bar', 'baz' => 'tab'), '3f3cabbf33bae82b0711205c913a8fa0'),
            array(array('bar'), '6fc5f617053f53f56b4734453ec86daa'),
            array(array('bar', 'baz'), 'b9f31192ffbb4aa958cd1c5f88540c1e'),
        );
    }
}
