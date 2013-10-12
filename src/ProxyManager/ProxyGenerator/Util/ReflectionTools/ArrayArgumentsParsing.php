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

/**
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class ArrayArgumentsParsing
{
    /**
     * Arguments parsing
     * @param array $argument
     * @return string
     */
    protected static function parse(array $argument)
    {
       $prototype = array();
       $position = 0;
       foreach($argument as $p => $value) {
           if (is_array($value)) {
               $prototype[] = 'array $' . $position++;
           } else if (PHP_VERSION_ID >= 50400 && is_callable($value)) {
               $prototype[] = 'callable $' . $position++;
           } else {
               $class = is_object($value) ? get_class($value) : '';
               $prototype[] = ($class ? '\\' . $class . ' ' : '') . '$' . $position++;
           }
       }
       
       return $prototype;
    }
   
    /**
     * Get arguments string description
     * @param array $argument
     * @return type
     */
    public static function toString(array $argument)
    {
       $prototype = self::parse($argument);
       return implode(',', $prototype);
    }
    
    /**
     * Get arguments string identifier
     * @param array $argument
     * @return type
     */
    public static function toIdentifiableString(array $argument)
    {
       $string = self::toString($argument);
       return $string ? preg_replace('#\$[^\s,\$]+#', '$', $string) : 'void';
    }
}
