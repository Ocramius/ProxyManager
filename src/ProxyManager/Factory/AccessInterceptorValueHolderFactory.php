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

namespace ProxyManager\Factory;

use ProxyManager\Proxy\AccessInterceptorValueHolderInterface;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

/**
 * Factory responsible of producing proxy objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class AccessInterceptorValueHolderFactory extends AbstractBaseFactory
{
    /**
     * @var \ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator|null
     */
    private $generator;

    /**
     * @param object     $instance           the object to be wrapped within the value holder
     * @param \Closure[] $prefixInterceptors an array (indexed by method name) of interceptor closures to be called
     *                                       before method logic is executed
     * @param \Closure[] $suffixInterceptors an array (indexed by method name) of interceptor closures to be called
     *                                       after method logic is executed
     *
     * @return AccessInterceptorValueHolderInterface
     */
    public function createProxy(
        $instance,
        array $prefixInterceptors = [],
        array $suffixInterceptors = []
    ) : AccessInterceptorValueHolderInterface {
        $proxyClassName = $this->generateProxy(get_class($instance));

        return $proxyClassName::staticProxyConstructor($instance, $prefixInterceptors, $suffixInterceptors);
    }

    /**
     * {@inheritDoc}
     */
    protected function getGenerator() : ProxyGeneratorInterface
    {
        return $this->generator ?: $this->generator = new AccessInterceptorValueHolderGenerator();
    }
}
