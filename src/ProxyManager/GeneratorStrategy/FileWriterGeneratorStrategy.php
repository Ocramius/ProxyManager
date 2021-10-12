<?php

declare(strict_types=1);

namespace ProxyManager\GeneratorStrategy;

use Closure;
use Laminas\Code\Generator\ClassGenerator;
use ProxyManager\Exception\FileNotWritableException;
use ProxyManager\FileLocator\FileLocatorInterface;
use Webimpress\SafeWriter\Exception\ExceptionInterface as FileWriterException;
use Webimpress\SafeWriter\FileWriter;

use function restore_error_handler;
use function set_error_handler;

/**
 * Generator strategy that writes the generated classes to disk while generating them
 *
 * {@inheritDoc}
 */
class FileWriterGeneratorStrategy implements GeneratorStrategyInterface
{
    private Closure $emptyErrorHandler;

    public function __construct(protected FileLocatorInterface $fileLocator)
    {
        $this->emptyErrorHandler = static function (): void {
        };
    }

    /**
     * Write generated code to disk and return the class code
     *
     * {@inheritDoc}
     *
     * @throws FileNotWritableException
     */
    public function generate(ClassGenerator $classGenerator): string
    {
        $generatedCode = $classGenerator->generate();
        $className     = (string) $classGenerator->getNamespaceName() . '\\' . $classGenerator->getName();
        $fileName      = $this->fileLocator->getProxyFileName($className);

        set_error_handler($this->emptyErrorHandler);

        try {
            FileWriter::writeFile($fileName, "<?php\n\n" . $generatedCode);

            return $generatedCode;
        } catch (FileWriterException $e) {
            throw FileNotWritableException::fromPrevious($e);
        } finally {
            restore_error_handler();
        }
    }
}
