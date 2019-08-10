<?php

declare(strict_types=1);

namespace ProxyManager\GeneratorStrategy;

use Closure;
use ProxyManager\Exception\FileNotWritableException;
use ProxyManager\FileLocator\FileLocatorInterface;
use Zend\Code\Generator\ClassGenerator;
use function chmod;
use function dirname;
use function file_put_contents;
use function rename;
use function restore_error_handler;
use function set_error_handler;
use function tempnam;
use function trim;
use function umask;
use function unlink;

/**
 * Generator strategy that writes the generated classes to disk while generating them
 *
 * {@inheritDoc}
 */
class FileWriterGeneratorStrategy implements GeneratorStrategyInterface
{
    protected FileLocatorInterface $fileLocator;
    private Closure $emptyErrorHandler;

    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator       = $fileLocator;
        $this->emptyErrorHandler = static function () : void {
        };
    }

    /**
     * Write generated code to disk and return the class code
     *
     * {@inheritDoc}
     *
     * @throws FileNotWritableException
     */
    public function generate(ClassGenerator $classGenerator) : string
    {
        /** @var string $generatedCode */
        $generatedCode = $classGenerator->generate();
        $className     = $classGenerator->getNamespaceName() . '\\' . $classGenerator->getName();
        $fileName      = $this->fileLocator->getProxyFileName($className);

        set_error_handler($this->emptyErrorHandler);

        try {
            $this->writeFile("<?php\n\n" . $generatedCode, $fileName);

            return $generatedCode;
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Writes the source file in such a way that race conditions are avoided when the same file is written
     * multiple times in a short time period
     *
     * @throws FileNotWritableException
     */
    private function writeFile(string $source, string $location) : void
    {
        $directory   = dirname($location);
        $tmpFileName = tempnam($directory, 'temporaryProxyManagerFile');

        if ($tmpFileName === false) {
            throw FileNotWritableException::fromNotWritableDirectory($directory);
        }

        file_put_contents($tmpFileName, $source);
        chmod($tmpFileName, 0666 & ~umask());

        if (! rename($tmpFileName, $location)) {
            unlink($tmpFileName);

            throw FileNotWritableException::fromInvalidMoveOperation($tmpFileName, $location);
        }
    }
}
