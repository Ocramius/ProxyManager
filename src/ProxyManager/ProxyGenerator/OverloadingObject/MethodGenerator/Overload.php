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
use Zend\Code\Generator\PropertyGenerator;

/**
 * Implementation for overloading objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class Overload extends MethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(PropertyGenerator $prototypes)
    {
        parent::__construct('overload');

        $interceptor = new ParameterGenerator('closure');

        $interceptor->setType('Closure');
        $interceptor->setDefaultValue(null);
        
        $this->setParameter(new ParameterGenerator('methodName'));
        $this->setParameter($interceptor);
        $this->setDocblock('{@inheritDoc}');
        
        $body = 
              '$prototype = $this->getPrototypeFromClosure($closure);' . "\n"
            . 'if (isset($this->' . $prototypes->getName() . '[$methodName][$prototype])) {'
            . '    throw new \ProxyManager\Proxy\Exception\OverloadingObjectException("An other method ($methodName) with the same prototype already exists");'
            . '}'
            . '$this->' . $prototypes->getName() . '[$methodName][$prototype] = $closure;';
        $this->setBody($body);
    }
}
