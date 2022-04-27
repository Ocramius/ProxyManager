<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use Laminas\Code\Generator\Exception\RuntimeException;
use Laminas\Code\Generator\ValueGenerator as LaminasValueGenerator;
use ReflectionParameter;

use function explode;
use function implode;
use function preg_replace;
use function preg_split;
use function rtrim;
use function substr;
use function var_export;

use const PREG_SPLIT_DELIM_CAPTURE;

/**
 * @internal do not use this in your code: it is only here for internal use
 */
class ValueGenerator extends LaminasValueGenerator
{
    private ?ReflectionParameter $reflection;

    public function __construct(mixed $value, ?ReflectionParameter $reflection = null)
    {
        if ($value instanceof LaminasValueGenerator) {
            $this->value         = $value->value;
            $this->type          = $value->type;
            $this->arrayDepth    = $value->arrayDepth;
            $this->outputMode    = $value->outputMode;
            $this->allowedTypes  = $value->allowedTypes;
            $this->constants     = $value->constants;
            $this->isSourceDirty = $value->isSourceDirty;
            $this->indentation   = $value->indentation;
            $this->sourceContent = $value->sourceContent;
        } else {
            parent::__construct($value, parent::TYPE_AUTO, parent::OUTPUT_SINGLE_LINE);
        }

        $this->reflection = $reflection;
    }

    public function generate(): string
    {
        try {
            return parent::generate();
        } catch (RuntimeException $e) {
            if ($this->reflection) {
                $value = rtrim(substr(explode('$' . $this->reflection->getName() . ' = ', (string) $this->reflection, 2)[1], 0, -2));
            } else {
                $value = var_export($this->value, true);
            }

            return self::fixExport($value);
        }
    }

    private static function fixExport(string $value): string
    {
        $parts = preg_split('{(\'(?:[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\')}', $value, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $i => &$part) {
            if ($part === '' || $i % 2 !== 0) {
                continue;
            }

            $part = preg_replace('/(?(DEFINE)(?<V>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+))(?<!\\\\)(?&V)(?:\\\\(?&V))*+::/', '\\\\$0', $part);
        }

        return implode('', $parts);
    }
}
