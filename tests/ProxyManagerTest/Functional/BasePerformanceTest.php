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

namespace ProxyManagerTest\Functional;

use PHPUnit_Framework_TestCase;

/**
 * Base performance test logic
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Performance
 * @coversNothing
 */
abstract class BasePerformanceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var float time when last capture was started
     */
    private $startTime   = 0;

    /**
     * @var int bytes when last capture was started
     */
    private $startMemory = 0;

    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass()
    {
        $header = "Performance test - " . get_called_class() . ":";

        echo "\n\n" . str_repeat('=', strlen($header)) . "\n" . $header . "\n\n";
    }

    /**
     * Start profiler snapshot
     */
    protected function startCapturing()
    {
        $this->startMemory = memory_get_usage();
        $this->startTime   = microtime(true);
    }

    /**
     * Echo current profiler output
     *
     * @param string $messageTemplate
     *
     * @return array
     */
    protected function endCapturing($messageTemplate)
    {
        $time     = microtime(true) - $this->startTime;
        $memory   = memory_get_usage() - $this->startMemory;

        if (gc_enable()) {
            gc_collect_cycles();
        }

        echo sprintf($messageTemplate, $time, $memory / 1024) . "\n";

        return array(
            'time'   => $time,
            'memory' => $memory
        );
    }

    /**
     * Display comparison between two profiles
     *
     * @param array $baseProfile
     * @param array $proxyProfile
     */
    protected function compareProfile(array $baseProfile, array $proxyProfile)
    {
        $baseMemory     = max(1, $baseProfile['memory']);
        $timeOverhead   = (($proxyProfile['time'] / $baseProfile['time']) - 1) * 100;
        $memoryOverhead = (($proxyProfile['memory'] / $baseMemory) - 1) * 100;

        echo sprintf('Comparison time / memory: %.2f%% / %.2f%%', $timeOverhead, $memoryOverhead) . "\n\n";
    }
}
