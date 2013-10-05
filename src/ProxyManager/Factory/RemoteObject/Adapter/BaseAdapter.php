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
use ProxyManager\Proxy\Exception\RemoteObjectException;
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
     * Adapter client
     * @var \Zend\Json\Server\Client
     */
    protected $client;
    
    protected $map = array();
    
    /**
     * Constructor
     * 
     * @param Client $client
     */
    public function __construct(Client $client = null, array $map = array())
    {
        if ($client) {
            $this->setClient($client);
        }
        
        if ($map) {
            $this->map = $map;
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function call($wrappedClass, $method, array $params = array())
    {
        $client      = $this->getClient();
        $serviceName = $this->assemble($wrappedClass, $method);
        
        if (isset($this->map[$serviceName])) {
            $serviceName = $this->map[$serviceName];
        }
        
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
    public function getClient()
    {
        if (null === $this->client) {
            throw new RemoteObjectException('You must defined an adapter client');
        }
        return $this->client;
    }
    
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
