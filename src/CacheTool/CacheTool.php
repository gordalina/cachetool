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

class CacheTool
{
    const VERSION = '@package_version@';

    /**
     * @var AbstractAdapter
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $proxies = array();

    /**
     * @var array
     */
    protected $functions = array();

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new Logger('cachetool');
    }

    /**
     * @param  AbstractAdapter $adapter
     * @param  LoggerInterface $logger
     * @return CacheTool
     */
    public static function factory(AbstractAdapter $adapter = null, LoggerInterface $logger = null)
    {
        $cacheTool = new static($logger);
        $cacheTool->addProxy(new Proxy\ApcProxy());
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

        return $this;
    }

    /**
     * @return AbstractAdapter
     */
    public function getAdapter()
    {
        return $this->adapter;
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
        $this->functions = array();

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

        if ($this->adapter instanceof AdapterInterface) {
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

        if ($this->getFunction($name)) {
            return call_user_func_array($this->getFunction($name), $arguments);
        }
    }

    /**
     * Initializes functions and return callable
     *
     * @param  string $name
     * @return array
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
                    $this->functions[$fn] = array($proxy, $fn);
                }
            }
        }

        return $this->functions[$name];
    }
}
