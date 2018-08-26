<?php

declare(strict_types=1);

namespace ProxyManager\GeneratorStrategy;

use ProxyManager\Exception\FileNotWritableException;
use ProxyManager\FileLocator\FileLocatorInterface;
use Zend\Code\Generator\ClassGenerator;
use function assert;
use function chmod;
use function file_put_contents;
use function is_string;
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
 *
 */
class FileWriterGeneratorStrategy implements GeneratorStrategyInterface
{
    /** @var FileLocatorInterface */
    protected $fileLocator;

    /** @var callable */
    private $emptyErrorHandler;

    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator       = $fileLocator;
        $this->emptyErrorHandler = function () : void {
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
        $className     = trim($classGenerator->getNamespaceName(), '\\')
            . '\\' . trim($classGenerator->getName(), '\\');
        $generatedCode = $classGenerator->generate();
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
     *
     * @throws FileNotWritableException
     */
    private function writeFile(string $source, string $location) : void
    {
        $tmpFileName = tempnam($location, 'temporaryProxyManagerFile');

        assert(is_string($tmpFileName));

        file_put_contents($tmpFileName, $source);
        chmod($tmpFileName, 0666 & ~umask());

        if (! rename($tmpFileName, $location)) {
            unlink($tmpFileName);

            throw FileNotWritableException::fromInvalidMoveOperation($tmpFileName, $location);
        }
    }
}
