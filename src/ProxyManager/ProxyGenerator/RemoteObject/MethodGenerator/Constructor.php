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

namespace ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator;

use ReflectionClass;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * The `__construct` implementation for lazy loading proxies
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class Constructor extends MethodGenerator
{
    /**
     * Constructor
     * 
     * @param ReflectionClass   $originalClass          Reflection of the class to proxy
     * @param PropertyGenerator $classname		        Classname property
     * @param PropertyGenerator $adapter		        Adapater property
     */
    public function __construct(ReflectionClass $originalClass, PropertyGenerator $classname, PropertyGenerator $adapter)
    {
        parent::__construct('__construct');

        $classnameName 	= $classname->getName();
        $adapterName	= $adapter->getName();
        $this->setParameters(array(new ParameterGenerator($classnameName), new ParameterGenerator($adapterName, '\ProxyManager\Factory\RemoteObject\AdapterInterface')));

        $this->setDocblock("@override constructor for remote object control\n\n@param string \$classname\n\n@param \\ProxyManager\\Factory\\RemoteObject\\AdapterInterface \$adapter");
        $this->setBody('$this->' . $classnameName . ' = "' . $originalClass->getName() . '";' . "\n" . '$this->' . $adapterName . ' = $' . $adapterName . ';' . "\n");
    }
}
