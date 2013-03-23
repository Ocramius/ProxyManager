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

namespace ProxyManager\FileLocator;

use ProxyManager\Exception\InvalidProxyDirectoryException;

/**
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class FileLocator implements FileLocatorInterface
{
    /**
     * @var string
     */
    protected $proxiesDirectory;

    /**
     * @param string $proxiesDirectory
     *
     * @throws \ProxyManager\Exception\InvalidProxyDirectoryException
     */
    public function __construct($proxiesDirectory)
    {
        $this->proxiesDirectory = realpath($proxiesDirectory);

        if (false === $this->proxiesDirectory) {
            throw InvalidProxyDirectoryException::proxyDirectoryNotFound($proxiesDirectory);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getProxyFileName($className)
    {
        return $this->proxiesDirectory . DIRECTORY_SEPARATOR . str_replace('\\', '', $className) . '.php';
    }
}
