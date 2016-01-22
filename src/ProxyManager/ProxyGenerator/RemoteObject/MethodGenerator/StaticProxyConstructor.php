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

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator;

use ProxyManager\Factory\RemoteObject\AdapterInterface;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator;
use ReflectionClass;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * The `staticProxyConstructor` implementation for remote object proxies
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class StaticProxyConstructor extends MethodGenerator
{
    /**
     * Constructor
     *
     * @param ReflectionClass   $originalClass Reflection of the class to proxy
     * @param PropertyGenerator $adapter       Adapter property
     */
    public function __construct(ReflectionClass $originalClass, PropertyGenerator $adapter)
    {
        $adapterName = $adapter->getName();

        parent::__construct(
            'staticProxyConstructor',
            [new ParameterGenerator($adapterName, AdapterInterface::class)],
            MethodGenerator::FLAG_PUBLIC | MethodGenerator::FLAG_STATIC,
            null,
            'Constructor for remote object control\n\n'
            . '@param \\ProxyManager\\Factory\\RemoteObject\\AdapterInterface \$adapter'
        );

        $body = 'static $reflection;' . "\n\n"
            . '$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);' . "\n"
            . '$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();' . "\n\n"
            . '$instance->' . $adapterName . ' = $' . $adapterName . ";\n\n"
            . UnsetPropertiesGenerator::generateSnippet(Properties::fromReflectionClass($originalClass), 'instance');

        $this->setBody($body . "\n\nreturn \$instance;");
    }
}
