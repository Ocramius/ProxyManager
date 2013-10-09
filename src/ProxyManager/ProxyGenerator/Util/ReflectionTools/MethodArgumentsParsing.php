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

use Zend\Code\Reflection\MethodReflection;
use Zend\Code\Generator\MethodGenerator;

/**
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class MethodArgumentsParsing
{
    /**
     * @var MethodReflection
     */
    protected $reflection;
    
    public function __construct(MethodReflection $reflection)
    {
        $this->reflection = $reflection;
    }
    
    /**
     * Arguments parsing
     * @return string
     */
    protected function parse()
    {
        $prototype  = array();
        $reflection = MethodGenerator::fromReflection($this->reflection);
        
        foreach($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type && $type != 'array') {
                $type = '\\' . $type;
            }
            $prototype[] = ($type ? $type . ' ' : '') . '$' . $parameter->getName();
        }
        
        return $prototype;
    }
   
    /**
     * Get arguments string description
     * @return type
     */
    public function toString()
    {
       $prototype = $this->parse();
       return implode(',', $prototype);
    }
    
    /**
     * Get arguments string identifier
     * @return type
     */
    public function toIdentifiableString()
    {
       $string = $this->toString();
       return $string ? preg_replace('#\$[^\s,\$]+#', '$', $string) : 'void';
    }
}
