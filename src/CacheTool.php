<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool;

use CacheTool\Adapter\AbstractAdapter;
use CacheTool\Proxy\ProxyInterface;
use Psr\Log\LoggerInterface;
use Monolog\Logger;

/**
 * @method mixed apcu_add(mixed $key, mixed $var, int $ttl = 0)
 * @method boolean apcu_cache_info(boolean $limited = false)
 * @method boolean|array apcu_regexp_get_keys(?string $regexp = null)
 * @method boolean apcu_cas(string $key, int $old, int $new)
 * @method boolean apcu_clear_cache()
 * @method mixed apcu_dec(string $key, int $step = 1, \stdClass $ref = false)
 * @method mixed apcu_delete(mixed $key)
 * @method boolean apcu_regexp_delete(?string $regexp = null)
 * @method mixed apcu_exists(mixed $regexp = null)
 * @method mixed apcu_fetch(mixed $key, \stdClass $ref = false)
 * @method mixed apcu_inc(string $key, int $step = 1, \stdClass $ref = false)
 * @method boolean apcu_sma_info(boolean $limited = false)
 * @method boolean apcu_store(mixed $key, mixed $var = null, int $ttl = 0)
 * @method string apcu_version()
 * @method boolean opcache_compile_file(string $file)
 * @method boolean opcache_compile_files(array $files)
 * @method array opcache_get_configuration()
 * @method array opcache_get_status(boolean $get_scripts = true)
 * @method boolean opcache_invalidate(string $filename, boolean $force)
 * @method array<boolean> opcache_invalidate_many(array $scripts, boolean $force)
 * @method boolean opcache_reset()
 * @method string opcache_version()
 * @method boolean extension_loaded(string $name)
 * @method string ini_get(string $varname)
 * @method string ini_set(string $varname, string $newvalue)
 * @method string phpversion(string $extension = null)
 * @method array stat_realpath_get()
 * @method int stat_realpath_size()
 * @method void stat_cache_clear()
 * @method mixed _eval(string $expression)
 */
class CacheTool
{
    /**
     * @var AbstractAdapter
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $proxies = [];

    /**
     * @var array
     */
    protected $functions = [];

    /**
     * @var string
     */
    protected $tempDir;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param string          $tempDir
     * @param LoggerInterface $logger
     */
    public function __construct($tempDir = null, LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new Logger('cachetool');
        $this->tempDir = $this->getWritableTempDir($tempDir);
    }

    /**
     * @param  AbstractAdapter $adapter
     * @param  string          $tempDir
     * @param  LoggerInterface $logger
     * @return CacheTool
     */
    public static function factory(AbstractAdapter $adapter = null, $tempDir = null, LoggerInterface $logger = null)
    {
        $cacheTool = new static($tempDir, $logger);
        $cacheTool->addProxy(new Proxy\ApcuProxy());
        $cacheTool->addProxy(new Proxy\PhpProxy());
        $cacheTool->addProxy(new Proxy\OpcacheProxy());

        if ($adapter instanceof AbstractAdapter) {
            $cacheTool->setAdapter($adapter);
        }

        return $cacheTool;
    }


    /**
     * @param  AbstractAdapter $adapter
     * @return CacheTool
     */
    public function setAdapter(AbstractAdapter $adapter)
    {
        $this->logger->info(sprintf('Setting adapter: %s', get_class($adapter)));

        $this->adapter = $adapter;
        $this->adapter->setLogger($this->logger);
        $this->adapter->setTempDir($this->tempDir);

        return $this;
    }

    /**
     * @return AbstractAdapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    public function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * @param ProxyInterface $proxy
     * @return CacheTool
     */
    public function addProxy(ProxyInterface $proxy)
    {
        $this->logger->info(sprintf('Adding Proxy: %s', get_class($proxy)));

        $this->proxies[] = $proxy;

        // reset functions (to be built when needed)
        $this->functions = [];

        return $this;
    }

    /**
     * @return array
     */
    public function getProxies()
    {
        return $this->proxies;
    }

    /**
     * @param  LoggerInterface $logger
     * @return CacheTool
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        if ($this->adapter instanceof AbstractAdapter) {
            $this->adapter->setLogger($logger);
        }

        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Calls proxy functions
     *
     * @param  string $name
     * @param  array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $this->logger->notice(sprintf('Executing: %s(%s)', $name, implode(', ', array_map('json_encode', $arguments))));

        $function = $this->getFunction($name);
        if ($function) {
            return $function(...$arguments);
        }
    }

    /**
     * Initializes functions and return callable
     *
     * @param  string $name
     * @return callable
     */
    protected function getFunction($name)
    {
        if (empty($this->functions)) {
            foreach ($this->proxies as $proxy) {
                $this->logger->info(sprintf('Loading Proxy: %s', get_class($proxy)));

                // lazily set adapter
                $proxy->setAdapter($this->adapter);

                foreach ($proxy->getFunctions() as $fn) {
                    $this->logger->debug(sprintf('Loading Function: %s', $fn));
                    $this->functions[$fn] = [$proxy, $fn];
                }
            }
        }

        if (isset($this->functions[$name])) {
            return $this->functions[$name];
        }

        throw new \InvalidArgumentException("Function with name: {$name} is not provided by any Proxy.");
    }

    /**
     * @param  string $tempDir
     * @return string
     */
    protected function getWritableTempDir($tempDir = null) {
        if (is_null($tempDir)) {
            $tempDirs = ['/dev/shm', '/var/run', sys_get_temp_dir()];
            foreach ($tempDirs as $dir) {
                if (is_dir($dir) && is_writable($dir)) {
                    $tempDir = $dir;
                    break;
                }
            }
        }

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0700, true);
        }

        return $tempDir;
    }
}
