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

namespace ProxyManager\ProxyGenerator\OverloadingObject\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;

/**
 * Implementation for overloading objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class GetPrototypeFromClosure extends MethodGenerator
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('getPrototypeFromClosure');
        parent::setVisibility(parent::VISIBILITY_PROTECTED);
        
        $interceptor = new ParameterGenerator('closure');

        $interceptor->setType('Closure');
        
        $this->setParameter($interceptor);
        $this->setDocblock('{@inheritDoc}');
        
        $body = 
              '$prototype = array();' . "\n"
            . '$r = new \ReflectionFunction($closure);' . "\n"
            . 'foreach($r->getParameters() as $arg) {' . "\n"
            . '    if ($arg->isArray()) {' . "\n"
            . '        $prototype[] = \'array $\' . $arg->getPosition();' . "\n"
            . (PHP_VERSION_ID >= 50400 ?
              '    } else if ($arg->isCallable()) {' . "\n"
            . '        $prototype[] = \'callable $\' . $arg->getPosition();' . "\n"
              : '')
            . '    } else {' . "\n"
            . '        $class = $arg->getClass();' . "\n"
            . '        $prototype[] = ($class ? $class->getName() . \' \' : \'\') . \'$\' . $arg->getPosition();' . "\n"
            . '    }' . "\n"
            . '}' . "\n"
            . '$prototype = $prototype ? implode(\',\', $prototype): \'void\';' . "\n"
            . 'return $prototype;';
        
        $this->setBody($body);
    }
}
