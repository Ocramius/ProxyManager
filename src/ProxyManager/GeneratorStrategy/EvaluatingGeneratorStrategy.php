<?php

declare(strict_types=1);

namespace ProxyManager\GeneratorStrategy;

use Zend\Code\Generator\ClassGenerator;
use function assert;
use function file_put_contents;
use function ini_get;
use function is_string;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * Generator strategy that produces the code and evaluates it at runtime
 */
class EvaluatingGeneratorStrategy implements GeneratorStrategyInterface
{
    /** @var bool flag indicating whether {@see eval} can be used */
    private bool $canEval = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        // @codeCoverageIgnoreStart
        $this->canEval = ! ini_get('suhosin.executor.disable_eval');
        // @codeCoverageIgnoreEnd
    }

    /**
     * Evaluates the generated code before returning it
     *
     * {@inheritDoc}
     */
    public function generate(ClassGenerator $classGenerator) : string
    {
        /** @var string $code */
        $code = $classGenerator->generate();

        // @codeCoverageIgnoreStart
        if (! $this->canEval) {
            $fileName = tempnam(sys_get_temp_dir(), 'EvaluatingGeneratorStrategy.php.tmp.');

            assert(is_string($fileName));

            file_put_contents($fileName, "<?php\n" . $code);
            /* @noinspection PhpIncludeInspection */
            require $fileName;
            unlink($fileName);

            return $code;
        }
        // @codeCoverageIgnoreEnd

        eval($code);

        return $code;
    }
}
