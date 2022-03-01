<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use Laminas\Code\Generator\AbstractMemberGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator as LaminasMethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ReflectionException;
use ReflectionMethod;

/**
 * Method generator that fixes minor quirks in ZF2's method generator
 */
class MethodGenerator extends LaminasMethodGenerator
{
    protected $hasTentativeReturnType = false;

    /**
     * @return static
     */
    public static function fromReflectionWithoutBodyAndDocBlock(MethodReflection $reflectionMethod): self
    {
        /** @var static $method */
        $method = parent::copyMethodSignature($reflectionMethod);

        $method->setInterface(false);
        $method->setBody('');

        if (\PHP_VERSION_ID < 80100) {
            return $method;
        }

        $getPrototype = \Closure::fromCallable([new ReflectionMethod(ReflectionMethod::class, 'getPrototype'), 'invoke']);

        while (true) {
            if ($reflectionMethod->hasTentativeReturnType()) {
                $method->hasTentativeReturnType = true;
                break;
            }

            if ($reflectionMethod->isAbstract()) {
                break;
            }

            try {
                $reflectionMethod = $getPrototype($reflectionMethod);
            } catch (ReflectionException $e) {
                break;
            }
        }

        return $method;
    }

    /**
     * {@inheritDoc}
     */
    public function setDocBlock($docBlock): AbstractMemberGenerator
    {
        parent::setDocBlock($docBlock);

        if (! $this->hasTentativeReturnType) {
            return $this;
        }

        $docBlock = parent::getDocBlock();

        return parent::setDocBlock(new class($docBlock) extends DocBlockGenerator {
            public function __construct(DocBlockGenerator $docBlock)
            {
                $this->setShortDescription($docBlock->getShortDescription() ?? '');
                $this->setLongDescription($docBlock->getLongDescription() ?? '');
                $this->setTags($docBlock->getTags());
                $this->setWordWrap($docBlock->getWordWrap());
                $this->setSourceDirty($docBlock->isSourceDirty());
                $this->setIndentation($docBlock->getIndentation());
                $this->setSourceContent($docBlock->getSourceContent());
            }

            public function generate(): string
            {
                return parent::generate() . $this->getIndentation() . '#[\ReturnTypeWillChange]' . self::LINE_FEED;
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getDocBlock(): ?DocBlockGenerator
    {
        $docBlock = parent::getDocBlock();

        if ($docBlock !== null || ! $this->hasTentativeReturnType) {
            return $docBlock;
        }

        return new class($this->getIndentation()) extends DocBlockGenerator {
            public function __construct(string $indentation)
            {
                $this->setIndentation($indentation);
            }

            public function generate(): string
            {
                return $this->getIndentation() . '#[\ReturnTypeWillChange]' . self::LINE_FEED;
            }
        };
    }

    /**
     * {@inheritDoc} override needed to specify type in more detail
     */
    public function getSourceContent(): ?string
    {
        return parent::getSourceContent();
    }
}
