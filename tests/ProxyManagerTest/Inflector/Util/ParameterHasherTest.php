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

namespace ProxyManagerTest\Inflector\Util;

use PHPUnit_Framework_TestCase;
use ProxyManager\Inflector\Util\ParameterHasher;

/**
 * Tests for {@see \ProxyManager\Inflector\Util\ParameterHasher}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class ParameterHasherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getParameters
     *
     * @covers \ProxyManager\Inflector\Util\ParameterHasher::hashParameters
     */
    public function testGeneratesValidClassName(array $parameters, $expectedHash)
    {
        $encoder = new ParameterHasher();

        $this->assertSame($expectedHash, $encoder->hashParameters($parameters));
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return array(
            array(array(), '40cd750bba9870f18aada2478b24840a'),
            array(array('foo' => 'bar'), '49a3696adf0fbfacc12383a2d7400d51'),
            array(array('bar' => 'baz'), '6ed41c8a63c1571554ecaeb998198757'),
            array(array(null), '38017a839aaeb8ff1a658fce9af6edd3'),
            array(array(null, null), '12051f9a58288e5328ad748881cc4e00'),
            array(array('bar' => null), '0dbb112e1c4e6e4126232de2daa2d660'),
            array(array('bar' => 12345), 'eb6291ea4973741bf9b6571f49b4ffd2'),
            array(array('foo' => 'bar', 'bar' => 'baz'), '4447ff857f244d24c31bd84d7a855eda'),
        );
    }
}
