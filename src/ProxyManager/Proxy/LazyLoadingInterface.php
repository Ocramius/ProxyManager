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

namespace ProxyManager\Proxy;

/**
 * Lazy loading object identifier
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
interface LazyLoadingInterface extends ProxyInterface
{
    /**
     * Set or unset the initializer for the proxy instance
     *
     * @link https://github.com/Ocramius/ProxyManager/blob/master/docs/lazy-loading-value-holder.md#lazy-initialization
     *
     * An initializer should have a signature like following:
     *
     * <code>
     * $initializer = function (& $wrappedObject, $proxy, string $method, array $parameters, & $initializer) {};
     * </code>
     *
     * @param \Closure|null $initializer
     *
     * @return mixed
     */
    public function setProxyInitializer(\Closure $initializer = null);

    /**
     * @return \Closure|null
     */
    public function getProxyInitializer();

    /**
     * Force initialization of the proxy
     *
     * @return bool true if the proxy could be initialized
     */
    public function initializeProxy() : bool;

    /**
     * Retrieves current initialization status of the proxy
     *
     * @return bool
     */
    public function isProxyInitialized() : bool;
}
