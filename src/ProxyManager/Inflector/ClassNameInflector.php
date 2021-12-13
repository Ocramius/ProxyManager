<?php

declare(strict_types=1);

namespace ProxyManager\Inflector;

use ProxyManager\Inflector\Util\ParameterHasher;

use function is_int;
use function ltrim;
use function strlen;
use function strrpos;
use function substr;

final class ClassNameInflector implements ClassNameInflectorInterface
{
    /** @var int @TODO annotation still needed for phpstan to understand this */
    private int $proxyMarkerLength;
    private string $proxyMarker;
    private ParameterHasher $parameterHasher;

    public function __construct(protected string $proxyNamespace)
    {
        $this->proxyMarker       = '\\' . self::PROXY_MARKER . '\\';
        $this->proxyMarkerLength = strlen($this->proxyMarker);
        $this->parameterHasher   = new ParameterHasher();
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificReturnType we ignore these issues because classes may not have been loaded yet
     */
    public function getUserClassName(string $className): string
    {
        $className = ltrim($className, '\\');
        $position  = strrpos($className, $this->proxyMarker);

        if (! is_int($position)) {
            /** @psalm-suppress LessSpecificReturnStatement */
            return $className;
        }

        /** @psalm-suppress LessSpecificReturnStatement */
        return substr(
            $className,
            $this->proxyMarkerLength + $position,
            (int) strrpos($className, '\\') - ($position + $this->proxyMarkerLength)
        );
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificReturnType we ignore these issues because classes may not have been loaded yet
     */
    public function getProxyClassName(string $className, array $options = []): string
    {
        /** @psalm-suppress LessSpecificReturnStatement */
        return $this->proxyNamespace
            . $this->proxyMarker
            . $this->getUserClassName($className)
            . '\\Generated' . $this->parameterHasher->hashParameters($options);
    }

    public function isProxyClassName(string $className): bool
    {
        return strrpos($className, $this->proxyMarker) !== false;
    }
}
