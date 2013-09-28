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

use Zend\XmlRpc\Client;
use ProxyManager\Proxy\Exception\RemoteObjectException;

/**
 * Remote Object XML RPC adapter
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class XmlRpc extends BaseAdapter
{
    /**
     * XmlRpc client
     * @var \Zend\XmlRpc\Client
     */
    private $client;

    /**
     * Rpc client building
     * @param string $uri
     */
    public function __construct($uri)
    {
        if (! class_exists('Zend\XmlRpc\Client')) {
            throw new RemoteObjectException('XmlRpc adapter does not exists. Please install zend-xmlrpc package.');
        }
        if (empty($uri)) {
            throw new RemoteObjectException('Webservices URI is required');
        }
        parent::__construct($uri);
    }

    /**
     * {@inheritDoc}
     */
    protected function assemble($wrappedClass, $method)
    {
        return $wrappedClass . '.' . $method;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getClient()
    {
        if ($this->client) {
            return $this->client;
        }
        $this->client = new Client($this->uri);
        
        return $this->client;
    }
}
