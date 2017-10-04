<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Base test class with various intercepted properties
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class BaseClass implements BaseInterface
{
    /**
     * @var string
     */
    public $publicProperty = 'publicPropertyDefault';

    /**
     * @var string
     */
    protected $protectedProperty = 'protectedPropertyDefault';

    /**
     * @var string
     */
    private $privateProperty = 'privatePropertyDefault';

    /**
     * @return string
     */
    public function publicMethod()
    {
        return 'publicMethodDefault';
    }

    /**
     * @return string
     */
    public function publicPropertyGetter()
    {
        return $this->publicProperty;
    }

    /**
     * @return string
     */
    public function protectedPropertyGetter()
    {
        return $this->protectedProperty;
    }

    /**
     * @return string
     */
    public function privatePropertyGetter()
    {
        return $this->privateProperty;
    }

    /**
     * @return string
     */
    protected function protectedMethod()
    {
        return 'protectedMethodDefault';
    }

    /**
     * @return string
     */
    private function privateMethod()
    {
        return 'privateMethodDefault';
    }

    /**
     * @param \stdClass $param
     *
     * @return string
     */
    public function publicTypeHintedMethod(\stdClass $param)
    {
        return 'publicTypeHintedMethodDefault';
    }

    /**
     * @param array $param
     *
     * @return string
     */
    public function publicArrayHintedMethod(array $param)
    {
        return 'publicArrayHintedMethodDefault';
    }

    /**
     * @return string
     */
    public function & publicByReferenceMethod()
    {
        $returnValue = 'publicByReferenceMethodDefault';

        return $returnValue;
    }

    /**
     * @param mixed $param
     * @param mixed $byRefParam
     *
     * @return string
     */
    public function publicByReferenceParameterMethod($param, & $byRefParam)
    {
        return 'publicByReferenceParameterMethodDefault';
    }
}
