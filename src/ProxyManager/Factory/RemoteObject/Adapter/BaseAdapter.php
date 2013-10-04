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
use Zend\Uri\Http as HttpUri;
use Zend\Server\Client;

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
     * @var HttpUri 
     */
    protected $uri;
    
    /**
     * Adapter client
     * @var \Zend\Json\Server\Client
     */
    protected $client;
    
    /**
     * Constructor
     * 
     * @param string $uri
     */
    public function __construct($uri)
    {
        $this->uri = new HttpUri($uri);
        if (! $this->uri->isValid()) {
            throw new RemoteObjectException(sprintf('Uri "%s" is not a valid HTTP uri', $uri));
        }
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
     *
     * @param string $wrappedClass
     * @param string $method
     *
     * @return string Service name
     */
    abstract protected function assemble($wrappedClass, $method);
    
    /**
     * Get adapter client
     *
     * @return \Zend\Server\Client
     */
    abstract public function getClient();
    
    /**
     * Set adapter client
     *
     * @return \Zend\Server\Client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }
}
