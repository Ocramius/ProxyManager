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

namespace ProxyManager\ProxyGenerator\Util\ReflectionTools;

use ReflectionFunction;

/**
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class FunctionArgumentsParsing
{
    /**
     * Arguments parsing
     * @param ReflectionFunction $reflection
     * @return string
     */
    protected static function parse(ReflectionFunction $reflection)
    {
        $prototype = array();
        foreach($reflection->getParameters() as $parameter) {
            if ($parameter->isArray()) {
                $prototype[] = 'array $' . $parameter->getName();
            } else if(PHP_VERSION_ID >= 50400 && $parameter->isCallable()) {
                $prototype[] = 'callable $' . $parameter->getName();
            } else {
                $class = $parameter->getClass();
                $prototype[] = ($class ? '\\' . $class->getName() . ' ' : '') . '$' . $parameter->getName();
            }
        }
        
        return $prototype;
    }
   
    /**
     * Get arguments string description
     * @param ReflectionFunction $reflection
     * @return type
     */
    public static function toString(ReflectionFunction $reflection)
    {
       $prototype = self::parse($reflection);
       return implode(',', $prototype);
    }
    
    /**
     * Get arguments string identifier
     * @param ReflectionFunction $reflection
     * @return type
     */
    public static function toIdentifiableString(ReflectionFunction $reflection)
    {
       $string = self::toString($reflection);
       return $string ? preg_replace('#\$[^\s,\$]+#', '$', $string) : 'void';
    }
}
