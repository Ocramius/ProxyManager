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

namespace ProxyManager\Factory\RemoteObject\Adapter;

use ProxyManager\Factory\RemoteObject\AdapterInterface;

/**
 * Remote Object base adapter
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
abstract class BaseAdapter implements AdapterInterface
{
    /**
     * URI of the webservice endpoint
     * @var string 
     */
    protected $uri;
    
    /**
     * Constructor
     * @param string $uri
     */
    public function __construct($uri)
    {
        $this->uri = $uri;
    }
    
    /**
     * {@inheritDoc}
     */
    public function call($wrappedClass, $method, array $params = array())
    {
        $client      = $this->getClient();
        $serviceName = $this->assemble($wrappedClass, $method);
        
        return $client->call($serviceName, $params);
    }
    
    /**
     * Assembly of the service name will be used by the adapter
     * @return string Service name
     */
    abstract protected function assemble($wrappedClass, $method);
    
    /**
     * Build webservices client
     * @return \Zend\Server\Client
     */
    abstract public function getClient();
}
