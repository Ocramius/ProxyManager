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

namespace ProxyManager\Inflector;

use ProxyManager\Inflector\Util\ParameterEncoder;

/**
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassNameInflector implements ClassNameInflectorInterface
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
     * @var \ProxyManager\Inflector\Util\ParameterEncoder
     */
    private $parameterEncoder;

    /**
     * @param string $proxyNamespace
     */
    public function __construct($proxyNamespace)
    {
        $this->proxyNamespace    = (string) $proxyNamespace;
        $this->proxyMarker       = '\\' . static::PROXY_MARKER . '\\';
        $this->proxyMarkerLength = strlen($this->proxyMarker);
        $this->parameterEncoder  = new ParameterEncoder();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserClassName($className)
    {
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
    public function getProxyClassName($className, array $options = array())
    {
        return $this->proxyNamespace
            . $this->proxyMarker
            . $this->getUserClassName($className)
            . '\\' . $this->parameterEncoder->encodeParameters($options);
    }

    /**
     * {@inheritDoc}
     */
    public function isProxyClassName($className)
    {
        return false !== strrpos($className, $this->proxyMarker);
    }
}
