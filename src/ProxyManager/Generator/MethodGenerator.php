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

namespace ProxyManager\Generator;

use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator as ZendMethodGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Method generator that fixes minor quirks in ZF2's method generator
 *
 * @todo provide links to fixes PRs
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MethodGenerator extends ZendMethodGenerator
{
    /**
     * @var bool
     */
    protected $returnsReference = false;

    /**
     * @param boolean $returnsReference
     */
    public function setReturnsReference($returnsReference)
    {
        $this->returnsReference = (bool) $returnsReference;
    }

    /**
     * @return boolean
     */
    public function returnsReference()
    {
        return $this->returnsReference;
    }

    /**
     * @override enforces generation of \ProxyManager\Generator\MethodGenerator
     *
     * {@inheritDoc}
     */
    public static function fromReflection(MethodReflection $reflectionMethod)
    {
        /* @var $method self */
        $method = new static();

        $method->setSourceContent($reflectionMethod->getContents(false));
        $method->setSourceDirty(false);

        if ($reflectionMethod->getDocComment() != '') {
            $method->setDocBlock(DocBlockGenerator::fromReflection($reflectionMethod->getDocBlock()));
        }

        $method->setFinal($reflectionMethod->isFinal());

        if ($reflectionMethod->isPrivate()) {
            $method->setVisibility(self::VISIBILITY_PRIVATE);
        } elseif ($reflectionMethod->isProtected()) {
            $method->setVisibility(self::VISIBILITY_PROTECTED);
        } else {
            $method->setVisibility(self::VISIBILITY_PUBLIC);
        }

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $method->setParameter(ParameterGenerator::fromReflection($reflectionParameter));
        }

        $method->setStatic($reflectionMethod->isStatic());
        $method->setName($reflectionMethod->getName());
        $method->setBody($reflectionMethod->getBody());
        $method->setReturnsReference($reflectionMethod->returnsReference());

        return $method;
    }

    /**
     * @override fixes by-reference return value in zf2's method generator
     *
     * {@inheritDoc}
     */
    public function generate()
    {
        $output = '';
        $indent = $this->getIndentation();

        if (null !== ($docBlock = $this->getDocBlock())) {
            $docBlock->setIndentation($indent);

            $output .= $docBlock->generate();
        }

        $output .= $indent;

        if ($this->isAbstract()) {
            $output .= 'abstract ';
        } else {
            $output .= (($this->isFinal()) ? 'final ' : '');
        }

        $output .= $this->getVisibility()
            . (($this->isStatic()) ? ' static' : '')
            . ' function '
            . (($this->returnsReference()) ? '& ' : '')
            . $this->getName() . '(';
        $parameters = $this->getParameters();

        if (!empty($parameters)) {
            $parameterOutput = array();

            foreach ($parameters as $parameter) {
                $parameterOutput[] = $parameter->generate();
            }

            $output .= implode(', ', $parameterOutput);
        }

        $output .= ')' . self::LINE_FEED . $indent . '{' . self::LINE_FEED;

        if ($this->body) {
            $output .= preg_replace('#^(.+?)$#m', $indent . $indent . '$1', trim($this->body))
                . self::LINE_FEED;
        }

        $output .= $indent . '}' . self::LINE_FEED;

        return $output;
    }
}
