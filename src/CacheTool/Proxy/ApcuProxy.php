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

class ApcuProxy implements ProxyInterface
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
            'apcu_add',
            'apcu_cache_info',
            'apcu_cas',
            'apcu_clear_cache',
            'apcu_dec',
            'apcu_delete',
            'apcu_exists',
            'apcu_fetch',
            'apcu_inc',
            'apcu_sma_info',
            'apcu_store',

            'apcu_version'
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
     * Caches a variable in the data store, only if it's not already stored
     *
     * Note: Unlike many other mechanisms in PHP, variables stored using apcu_add() will persist between requests
     *       (until the value is removed from the cache).
     *
     * @since  3.0.13
     * @param  mixed $key Store the variable using this name. keys are cache-unique, so attempting to use apc_add() to
     *                    store data with a key that already exists will not overwrite the existing data, and will
     *                    instead return FALSE. (This is the only difference between apcu_add() and apc_store().)
     *                    If $key is an array set Names in key, variables in value
     * @param  mixed $var The variable to store
     *                    If $key is an array, this parameter is unused and set to NULL
     * @param  int $ttl   Time To Live; store var in the cache for ttl seconds. After the ttl has passed, the stored
     *                    variable will be expunged from the cache (on the next request). If no ttl is supplied
     *                    (or if the ttl is 0), the value will persist until it is removed from the cache manually,
     *                    or otherwise fails to exist in the cache (clear, restart, etc.).
     * @return mixed
     */
    public function apcu_add($key, $var = null, $ttl = 0)
    {
        if (is_string($key) && $var === null) {
            throw new \InvalidArgumentException('When $key is set $var cannot be null');
        }

        $code = new Code();
        $code->addStatement(sprintf(
            'return apcu_add(%s, %s, %s);',
            var_export($key, true),
            var_export($var, true),
            var_export($ttl, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Updates an old value with a new value
     * apcu_cas() updates an already existing integer value if the old parameter matches the currently stored value with the value of the new parameter.
     *
     * @since  3.1.1
     * @param  string $key The key of the value being updated.
     * @param  int    $old The old value (the value currently stored).
     * @param  int    $new The new value to update to
     * @return boolean     Returns TRUE on success or FALSE on failure.
     */
    public function apcu_cas($key, $old, $new)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apcu_cas(%s, %s, %s);',
            var_export($key, true),
            var_export($old, true),
            var_export($new, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Retrieves cached information from APCu's data store
     *
     * @since  2.0.0
     * @param  boolean $limited    If limited is TRUE, the return value will exclude the individual list of cache
     *                             entries. This is useful when trying to optimize calls for statistics gathering.
     * @return boolean             Array of cached data (and meta-data) or FALSE on failure
     */
    public function apcu_cache_info($limited = false)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apcu_cache_info(%s);',
            var_export($limited, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Clears the user/system cache
     *
     * @since  2.0.0
     * @return boolean             Always returns true
     */
    public function apcu_clear_cache()
    {
        $code = new Code();
        $code->addStatement('return apcu_clear_cache();');

        return $this->adapter->run($code);
    }

    /**
     * Decrease a stored number
     *
     * @since  3.1.1
     * @param  string    $key   The key of the value being decreased.
     * @param  int       $step  The step, or value to decrease.
     * @param  \stdClass $ref   success is set to TRUE in success and FALSE in failure
     * @return mixed            Returns the current value of key's value on success, or FALSE on failure
     */
    public function apcu_dec($key, $step = 1, $ref = false)
    {
        $code = new Code();
        $code->addStatement('$success = false;');
        $code->addStatement(sprintf(
            '$result = apcu_dec(%s, %s, $success);',
            var_export($key, true),
            var_export($step, true)
        ));
        $code->addStatement('return array($result, $success);');

        list($result, $success) = $this->adapter->run($code);

        if (is_object($ref)) {
            $ref->success = $success;
        }

        return $result;
    }

    /**
     * Removes a stored variable from the cache
     *
     * @since  3.1.1
     * @param  mixed $key The key used to store the value (with apcu_store()).
     * @return mixed      Returns TRUE on success or FALSE on failure.
     */
    public function apcu_delete($key)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apcu_delete(%s);',
            var_export($key, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Checks if one or more APCu keys exist.
     *
     * @since  3.1.4
     * @param  mixed $keys A string, or an array of strings, that contain keys.
     * @return mixed       Returns TRUE if the key exists, otherwise FALSE Or if an array was passed to keys, then an
     *                     array is returned that contains all existing keys, or an empty array if none exist.
     */
    public function apcu_exists($keys)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apcu_exists(%s);',
            var_export($keys, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Fetch a stored variable from the cache
     *
     * @since  3.0.0
     * @param  mixed     $key The key used to store the value (with apcu_store()). If an array is passed then each element is fetched and returned.
     * @param  \stdClass $ref success is set to TRUE in success and FALSE in failure
     * @return mixed          The stored variable or array of variables on success; FALSE on failure
     */
    public function apcu_fetch($key, $ref = false)
    {
        $code = new Code();
        $code->addStatement('$success = false;');
        $code->addStatement(sprintf('$result = apcu_fetch(%s, $success);', var_export($key, true)));
        $code->addStatement('return array($result, $success);');

        list($var, $success) = $this->adapter->run($code);

        if (is_object($ref)) {
            $ref->success = $success;
        }

        return $var;
    }

    /**
     * Increase a stored number
     *
     * @since  3.1.1
     * @param  string    $key  The key of the value being increased.
     * @param  int       $step The step, or value to increased.
     * @param  \stdClass $ref  success is set to TRUE in success and FALSE in failure
     * @return mixed           Returns the current value of key's value on success, or FALSE on failure
     */
    public function apcu_inc($key, $step = 1, $ref = false)
    {
        $code = new Code();
        $code->addStatement('$success = false;');
        $code->addStatement(sprintf(
            '$result = apcu_inc(%s, %s, $success);',
            var_export($key, true),
            var_export($step, true)
        ));
        $code->addStatement('return array($result, $success);');

        list($result, $success) = $this->adapter->run($code);

        if (is_object($ref)) {
            $ref->success = $success;
        }

        return $result;
    }

    /**
     * Retrieves APCu's Shared Memory Allocation information
     *
     * @since  2.0.0
     * @param  boolean $limited When set to FALSE (default) apcu_sma_info() will return a detailed information about
     *                          each segment.
     * @return boolean          Array of Shared Memory Allocation data; FALSE on failure
     */
    public function apcu_sma_info($limited = false)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apcu_sma_info(%s);',
            var_export($limited, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Cache a variable in the data store
     *
     * Note: Unlike many other mechanisms in PHP, variables stored using apcu_store() will persist between requests
     * (until the value is removed from the cache).
     *
     * @since  3.0.0
     * @param  mixed $key Store the variable using this name. keys are cache-unique, so storing a second value with the
     *                    same key will overwrite the original value.
     *                    If $key is an array set Names in key, variables in value
     * @param  mixed $var The variable to store
     *                    If $key is an array, this parameter is unused and set to NULL
     * @param  int $ttl   Time To Live; store var in the cache for ttl seconds. After the ttl has passed, the stored
     *                    variable will be expunged from the cache (on the next request). If no ttl is supplied (or if
     *                    the ttl is 0), the value will persist until it is removed from the cache manually, or
     *                    otherwise fails to exist in the cache (clear, restart, etc.).
     * @return boolean    Returns TRUE on success or FALSE on failure. Second syntax returns array with error keys.
     */
    public function apcu_store($key, $var = null, $ttl = 0)
    {
        if (is_string($key) && $var === null) {
            throw new \InvalidArgumentException('When $key is set $var cannot be null');
        }

        $code = new Code();
        $code->addStatement(sprintf(
            'return apcu_store(%s, %s, %s);',
            var_export($key, true),
            var_export($var, true),
            var_export($ttl, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * @return string
     */
    public function apcu_version()
    {
        $code = new Code();
        $code->addStatement('return phpversion("apcu");');

        return $this->adapter->run($code);
    }
}
