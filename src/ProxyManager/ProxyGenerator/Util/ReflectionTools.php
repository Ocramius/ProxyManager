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
     * @param string|closure $function
     * @return string
     */
    public static function getFunctionContent($function)
    {
        /** ZF2 PR : https://github.com/zendframework/zf2/pull/5245 */
        $reflectionFunction = new ReflectionFunction($function);

        $lines = array_slice(
            file($reflectionFunction->getFileName(), FILE_IGNORE_NEW_LINES),
            $reflectionFunction->getStartLine() - 1,
            ($reflectionFunction->getEndLine() - ($reflectionFunction->getStartLine() - 1)),
            true
        );
        
        $functionLine = implode("\n", $lines);
        
        $body = false;
        if ($reflectionFunction->isClosure()) {
            preg_match('#function\s*\([^\)]*\)\s*\{(.*\;)\s*\}#s', $functionLine, $matches);
            if ($matches[1]) {
                $body = $matches[1];
            }
        } else {
            $name = substr($reflectionFunction->getName(), strrpos($reflectionFunction->getName(), '\\')+1);
            preg_match('#function\s+' . $name . '\s*\([^\)]*\)\s*{([^{}]+({[^}]+})*[^}]+)}#', $functionLine, $matches);
            if ($matches[1]) {
                $body = $matches[1];
            }
        }

        return $body;
         /** ZF2 PR end */
    }
}
