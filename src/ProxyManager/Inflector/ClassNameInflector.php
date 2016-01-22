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

namespace ProxyManager\Inflector;

use ProxyManager\Inflector\Util\ParameterHasher;

/**
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
final class ClassNameInflector implements ClassNameInflectorInterface
{
    /**
     * @var string
     */
    protected $proxyNamespace;

    /**
     * @var int
     */
    private $proxyMarkerLength;

    /**
     * @var string
     */
    private $proxyMarker;

    /**
     * @var \ProxyManager\Inflector\Util\ParameterHasher
     */
    private $parameterHasher;

    /**
     * @param string $proxyNamespace
     */
    public function __construct(string $proxyNamespace)
    {
        $this->proxyNamespace    = $proxyNamespace;
        $this->proxyMarker       = '\\' . static::PROXY_MARKER . '\\';
        $this->proxyMarkerLength = strlen($this->proxyMarker);
        $this->parameterHasher   = new ParameterHasher();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserClassName(string $className) : string
    {
        $className = ltrim($className, '\\');

        if (false === $position = strrpos($className, $this->proxyMarker)) {
            return $className;
        }

        return substr(
            $className,
            $this->proxyMarkerLength + $position,
            strrpos($className, '\\') - ($position + $this->proxyMarkerLength)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getProxyClassName(string $className, array $options = []) : string
    {
        return $this->proxyNamespace
            . $this->proxyMarker
            . $this->getUserClassName($className)
            . '\\Generated' . $this->parameterHasher->hashParameters($options);
    }

    /**
     * {@inheritDoc}
     */
    public function isProxyClassName(string $className) : bool
    {
        return false !== strrpos($className, $this->proxyMarker);
    }
}
