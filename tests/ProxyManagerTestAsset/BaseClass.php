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

namespace ProxyManagerTestAsset;

/**
 * Base test class with various intercepted properties
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class BaseClass implements BaseInterface
{
    /**
     * @var string
     */
    public $publicProperty = 'publicPropertyDefault';

    /**
     * @var string
     */
    protected $protectedProperty = 'protectedPropertyDefault';

    /**
     * @var string
     */
    private $privateProperty = 'privatePropertyDefault';

    /**
     * @return string
     */
    public function publicMethod()
    {
        return 'publicMethodDefault';
    }

    /**
     * @return string
     */
    protected function protectedMethod()
    {
        return 'protectedMethodDefault';
    }

    /**
     * @return string
     */
    private function privateMethod()
    {
        return 'privateMethodDefault';
    }

    /**
     * @param \stdClass $param
     *
     * @return string
     */
    public function publicTypeHintedMethod(\stdClass $param)
    {
        return 'publicTypeHintedMethodDefault';
    }

    /**
     * @param array $param
     *
     * @return string
     */
    public function publicArrayHintedMethod(array $param)
    {
        return 'publicArrayHintedMethodDefault';
    }

    /**
     * @return string
     */
    public function & publicByReferenceMethod()
    {
        $returnValue = 'publicByReferenceMethodDefault';

        return $returnValue;
    }

    /**
     * @param mixed $param
     * @param mixed $byRefParam
     *
     * @return string
     */
    public function publicByReferenceParameterMethod($param, & $byRefParam)
    {
        return 'publicByReferenceParameterMethodDefault';
    }
}
