<?php

namespace CacheTool;

use CacheTool\Adapter\AdapterInterface;
use CacheTool\Proxy\ProxyInterface;

class CacheTool
{
    const VERSION = '@package_version@';

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $proxies = array();

    /**
     * @var array
     */
    protected $functions = null;

    /**
     * @param  AdapterInterface $adapter
     * @return CacheTool
     */
    public static function factory(AdapterInterface $adapter = null)
    {
        $cacheTool = new static();
        $cacheTool->addProxy(new Proxy\ApcProxy());
        $cacheTool->addProxy(new Proxy\PhpProxy());
        $cacheTool->addProxy(new Proxy\OpcacheProxy());

        if ($adapter instanceof AdapterInterface) {
            $cacheTool->setAdapter($adapter);
        }

        return $cacheTool;
    }

    /**
     * {@inheritdoc}
     */
    public function setAdapter(AdapterInterface $adapter)
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
            $this->functions = array();

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
