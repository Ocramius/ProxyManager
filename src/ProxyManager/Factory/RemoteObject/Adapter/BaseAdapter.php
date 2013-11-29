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
     *
     * @var \Zend\Server\Client
     */
    protected $client;

    /**
     * Service name mapping
     *
     * @var string[]
     */
    protected $map = array();

    /**
     * Constructor
     *
     * @param Client $client
     * @param array  $map    map of service names to their aliases
     */
    public function __construct(Client $client, array $map = array())
    {
        $this->client = $client;
        $this->map    = $map;
    }

    /**
     * {@inheritDoc}
     */
    public function call($wrappedClass, $method, array $params = array())
    {
        $serviceName = $this->getServiceName($wrappedClass, $method);

        if (isset($this->map[$serviceName])) {
            $serviceName = $this->map[$serviceName];
        }

        return $this->client->call($serviceName, $params);
    }

    /**
     * Get the service name will be used by the adapter
     *
     * @param string $wrappedClass
     * @param string $method
     *
     * @return string Service name
     */
    abstract protected function getServiceName($wrappedClass, $method);
}
