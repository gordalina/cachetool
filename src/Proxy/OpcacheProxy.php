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

class OpcacheProxy implements ProxyInterface
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
            'opcache_compile_file',
            'opcache_get_configuration',
            'opcache_get_status',
            'opcache_invalidate',
            'opcache_reset',

            'opcache_version'
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
     * Compiles and caches a PHP script without executing it
     *
     * This function compiles a PHP script and adds it to the opcode cache without executing it. This can be used to
     * prime the cache after a Web server restart by pre-caching files that will be included in later requests.
     *
     * @since  5.5.5
     * @since  7.0.2
     * @param  string $file The path to the PHP script to be compiled.
     * @return boolean      Returns TRUE if file was compiled successfully or FALSE on failure.
     */
    public function opcache_compile_file($file)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return opcache_compile_file(%s);',
            var_export($file, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Get configuration information about the cache
     *
     * @since  5.5.5
     * @since  7.0.2
     * @return array Returns an array of information, including ini, blacklist and version
     */
    public function opcache_get_configuration()
    {
        $code = new Code();
        $code->addStatement('return opcache_get_configuration();');

        return $this->adapter->run($code);
    }

    /**
     * Get status information about the cache
     *
     * @since  5.5.5
     * @since  7.0.2
     * @param  boolean $get_scripts Include script specific state information
     * @return array                Returns an array of information, optionally containing script specific state
     *                              information
     */
    public function opcache_get_status($get_scripts = true)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return opcache_get_status(%s);',
            var_export($get_scripts, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Get status information about the cache
     *
     * @since  5.5.0
     * @since  7.0.0
     * @param  string  $script The path to the script being invalidated.
     * @param  boolean $force  If set to TRUE, the script will be invalidated regardless of whether invalidation is
     *                         necessary.
     * @return boolean         Returns TRUE if the opcode cache for script was invalidated or if there was nothing to
     *                         invalidate, or FALSE if the opcode cache is disabled.
     */
    public function opcache_invalidate($script, $force = false)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return opcache_invalidate(%s, %s);',
            var_export($script, true),
            var_export($force, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Resets the contents of the opcode cache
     *
     * @since  5.5.0
     * @since  7.0.0
     * @return boolean Returns TRUE if the opcode cache was reset, or FALSE if the opcode cache is disabled.
     */
    public function opcache_reset()
    {
        $code = new Code();
        $code->addStatement('return opcache_reset();');

        return $this->adapter->run($code);
    }

    /**
     * @return string
     */
    public function opcache_version()
    {
        $code = new Code();
        $code->addStatement('return phpversion("Zend OPcache");');

        return $this->adapter->run($code);
    }
}
