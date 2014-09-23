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
     * @param  AbstractAdapter $adapter
     * @return CacheTool
     */
    public static function factory(AbstractAdapter $adapter = null)
    {
        $cacheTool = new static();
        $cacheTool->addProxy(new Proxy\ApcProxy());
        $cacheTool->addProxy(new Proxy\PhpProxy());
        $cacheTool->addProxy(new Proxy\OpcacheProxy());

        if ($adapter instanceof AbstractAdapter) {
            $cacheTool->setAdapter($adapter);
        }

        return $cacheTool;
    }

    /**
     * {@inheritdoc}
     */
    public function setAdapter(AbstractAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param ProxyInterface $proxy
     */
    public function addProxy(ProxyInterface $proxy)
    {
        $this->proxies[] = $proxy;

        // reset functions (to be built when needed)
        $this->functions = array();
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
                // lazily set adapter
                $proxy->setAdapter($this->adapter);

                foreach ($proxy->getFunctions() as $fn) {
                    $this->functions[$fn] = array($proxy, $fn);
                }
            }
        }

        return $this->functions[$name];
    }
}
