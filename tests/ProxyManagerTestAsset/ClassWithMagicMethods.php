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

namespace ProxyManagerTestAsset;

/**
 * Base test class to play around with pre-existing magic methods
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithMagicMethods
{
    /**
     * {@inheritDoc}
     */
    public function __set($name, $value)
    {
        return array($name => $value);
    }

    /**
     * {@inheritDoc}
     */
    public function __get($name)
    {
        return $name;
    }

    /**
     * {@inheritDoc}
     */
    public function __isset($name)
    {
        return (bool) $name;
    }

    /**
     * {@inheritDoc}
     */
    public function __unset($name)
    {
        return (bool) $name;
    }

    /**
     * {@inheritDoc}
     */
    public function __sleep()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function __wakeup()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function __clone()
    {
    }
}
