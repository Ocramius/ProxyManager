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

namespace ProxyManager\ProxyGenerator\Util;

use ReflectionFunction;
use Zend\Code\Reflection\MethodReflection;

/**
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class ReflectionTools
{
    /**
     * Get arguments line parser
     * 
     * @param mixed $entry
     * @return \ProxyManager\ProxyGenerator\Util\ReflectionTools\ArrayArgumentsParsing|\ProxyManager\ProxyGenerator\Util\ReflectionTools\MethodArgumentsParsing|\ProxyManager\ProxyGenerator\Util\ReflectionTools\FunctionArgumentsParsing
     * @throws \InvalidArgumentException
     */
    public function getArgumentsLine($entry)
    {
        if (is_array($entry)) {
            return new ReflectionTools\ArrayArgumentsParsing($entry);
        }
        if($entry instanceof MethodReflection) {
            return new ReflectionTools\MethodArgumentsParsing($entry);
        }
        if($entry instanceof ReflectionFunction || is_string($entry)) {
            if (is_string($entry)) {
                $entry = new ReflectionFunction($entry);
            }
            return new ReflectionTools\FunctionArgumentsParsing($entry);
        }
        throw new \InvalidArgumentException('Parameter type is not supported');
    }
    
    /**
     * @param string|closure $function
     * @return string
     */
    public static function getFunctionContent($function)
    {
        /** ZF2 PR : https://github.com/zendframework/zf2/pull/5245 */
        $reflectionFunction = new \ReflectionFunction($function);

        $lines = array_slice(
            file($reflectionFunction->getFileName(), FILE_IGNORE_NEW_LINES),
            $reflectionFunction->getStartLine() - 1,
            ($reflectionFunction->getEndLine() - ($reflectionFunction->getStartLine() - 1)),
            true
        );
        
        $functionLine = implode("\n", $lines);
        if ($reflectionFunction->isClosure()) {
            preg_match('#^\s*\$[^\=]+=\s*function\s*\([^\)]*\)\s*\{(.*)\}\s*;?$#s', $functionLine, $matches);
        } else {
            preg_match('#^\s*function\s*[^\(]+\([^\)]*\)\s*\{(.*)\}\s*$#s', $functionLine, $matches);
        }

        return trim($matches[1]);
         /** ZF2 PR end */
    }
}
