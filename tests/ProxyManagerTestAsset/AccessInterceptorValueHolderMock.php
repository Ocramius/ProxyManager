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

namespace ProxyManagerTestAsset;

use ProxyManager\Proxy\AccessInterceptorInterface;
use ProxyManager\Proxy\ValueHolderInterface;

/**
 * Base test class to catch instantiations of access interceptor value holders
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class AccessInterceptorValueHolderMock implements ValueHolderInterface, AccessInterceptorInterface
{
    /**
     * @var object
     */
    public $instance;

    /**
     * @var callable[]
     */
    public $prefixInterceptors;

    /**
     * @var callable[]
     */
    public $suffixInterceptors;

    /**
     * @param object     $instance
     * @param callable[] $prefixInterceptors
     * @param callable[] $suffixInterceptors
     *
     * @return self
     */
    public static function staticProxyConstructor($instance, $prefixInterceptors, $suffixInterceptors) : self
    {
        $selfInstance = new static(); // note: static because on-the-fly generated classes in tests extend this one.

        $selfInstance->instance           = $instance;
        $selfInstance->prefixInterceptors = $prefixInterceptors;
        $selfInstance->suffixInterceptors = $suffixInterceptors;

        return $selfInstance;
    }

    /**
     * {@inheritDoc}
     */
    public function setMethodPrefixInterceptor(string $methodName, \Closure $prefixInterceptor = null)
    {
        // no-op (on purpose)
    }

    /**
     * {@inheritDoc}
     */
    public function setMethodSuffixInterceptor(string $methodName, \Closure $suffixInterceptor = null)
    {
        // no-op (on purpose)
    }

    /**
     * {@inheritDoc}
     */
    public function getWrappedValueHolderValue()
    {
        return $this->instance;
    }
}
