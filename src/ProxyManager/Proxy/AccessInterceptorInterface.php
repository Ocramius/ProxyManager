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

namespace ProxyManager\Proxy;

/**
 * Access interceptor object marker
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
interface AccessInterceptorInterface extends ProxyInterface
{
    /**
     * Set or remove the prefix interceptor for a method
     *
     * @link https://github.com/Ocramius/ProxyManager/blob/master/docs/access-interceptor-value-holder.md
     *
     * A prefix interceptor should have a signature like following:
     *
     * <code>
     * $prefixInterceptor = function ($proxy, $instance, $method, $params, & $returnEarly) {};
     * </code>
     *
     * @param string        $methodName        name of the intercepted method
     * @param \Closure|null $prefixInterceptor interceptor closure or null to unset the currently active interceptor
     *
     * @return void
     */
    public function setMethodPrefixInterceptor($methodName, \Closure $prefixInterceptor = null);

    /**
     * Set or remove the suffix interceptor for a method
     *
     * @link https://github.com/Ocramius/ProxyManager/blob/master/docs/access-interceptor-value-holder.md
     *
     * A prefix interceptor should have a signature like following:
     *
     * <code>
     * $suffixInterceptor = function ($proxy, $instance, $method, $params, $returnValue, & $returnEarly) {};
     * </code>
     *
     * @param string        $methodName        name of the intercepted method
     * @param \Closure|null $suffixInterceptor interceptor closure or null to unset the currently active interceptor
     *
     * @return void
     */
    public function setMethodSuffixInterceptor($methodName, \Closure $suffixInterceptor = null);
}
