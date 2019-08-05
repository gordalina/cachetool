<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Proxy;

use CacheTool\Adapter\AbstractAdapter;
use CacheTool\Code;

class PhpProxy implements ProxyInterface
{
    /**
     * @var AbstractAdapter
     */
    protected $adapter;

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'extension_loaded',
            'ini_get',
            'ini_set',
            'phpversion',
            'stat_realpath_get',
            'stat_realpath_size',
            'stat_cache_clear',

            '_eval',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setAdapter(AbstractAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Find out whether an extension is loaded
     *
     * @param  string $name The extension name. This parameter is case-insensitive
     * @return boolean      Returns TRUE if the extension identified by name is loaded, FALSE otherwise
     */
    public function extension_loaded($name)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            "return extension_loaded(%s);",
            var_export($name, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Gets the value of a configuration option
     *
     * @param  string $varname The configuration option name
     * @return string          Returns the value of the configuration option as a string on success, or an empty string
     *                         for null values. Returns FALSE if the configuration option doesn't exist.
     */
    public function ini_get($varname)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            "return ini_get(%s);",
            var_export($varname, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Gets the value of a configuration option
     *
     * @param  string $varname  Not all the available options can be changed using ini_set(). There is a list of all
     *                          available options in the appendix.
     * @param  string $newvalue The new value for the option
     * @return string           Returns the old value on success, FALSE on failure
     */
    public function ini_set($varname, $newvalue)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            "return ini_set(%s, %s);",
            var_export($varname, true),
            var_export($newvalue, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Returns a string containing the version of the currently running PHP parser or extension.
     *
     * @param  string $extension An optional extension name
     * @return string
     */
    public function phpversion($extension = null)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            "return phpversion(%s);",
            var_export($extension, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Get contents of the realpath cache
     *
     * @since  5.3.2
     * @return array Returns an array of realpath cache entries. The keys are original path entries, 
     * and the values are arrays of data items, containing the resolved path, expiration date, and 
     * other options kept in the cache.
     */
    public function stat_realpath_get()
    {
        $code = new Code();
        $code->addStatement('return realpath_cache_get();');

        return $this->adapter->run($code);
    }

    /**
     * Returns how much memory realpath cache is using. 
     *
     * @since  5.3.2
     * @return int Memory usage in bytes
     */
    public function stat_realpath_size()
    {
        $code = new Code();
        $code->addStatement('return realpath_cache_size();');

        return $this->adapter->run($code);
    }

    /**
     * Resets the contents of the file status cache, including the realpath cache
     *
     * @return void
     */
    public function stat_cache_clear()
    {
        $code = new Code();
        $code->addStatement('return clearstatcache(true);');

        return $this->adapter->run($code);
    }

    /**
     * Evaluate a string as PHP code
     *
     * @param  string $expression Evaluates the given code as PHP
     * @return mixed
     */
    public function _eval($expression)
    {
        $code = new Code();
        $code->addStatement($expression);

        return $this->adapter->run($code);
    }
}
