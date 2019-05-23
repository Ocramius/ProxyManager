<?php

declare(strict_types=1);

namespace ProxyManager\Inflector;

use function is_int;
use ProxyManager\Inflector\Util\ParameterHasher;
use function ltrim;
use function strlen;
use function strrpos;
use function substr;

/**
 * {@inheritDoc}
 */
final class ClassNameInflector implements ClassNameInflectorInterface
{
    protected string $proxyNamespace;
    /** @var int @TODO annotation still needed for phpstan to understand this */
    private int $proxyMarkerLength;
    private string $proxyMarker;
    private ParameterHasher $parameterHasher;

    public function __construct(string $proxyNamespace)
    {
        $this->proxyNamespace    = $proxyNamespace;
        $this->proxyMarker       = '\\' . self::PROXY_MARKER . '\\';
        $this->proxyMarkerLength = strlen($this->proxyMarker);
        $this->parameterHasher   = new ParameterHasher();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserClassName(string $className) : string
    {
        $className = ltrim($className, '\\');
        $position  = strrpos($className, $this->proxyMarker);

        if (! is_int($position)) {
            return $className;
        }

        return substr(
            $className,
            $this->proxyMarkerLength + $position,
            ((int) strrpos($className, '\\')) - ($position + $this->proxyMarkerLength)
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
        return strrpos($className, $this->proxyMarker) !== false;
    }
}
