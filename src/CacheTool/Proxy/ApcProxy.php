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

class ApcProxy implements ProxyInterface
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
            'apc_add',
            'apc_bin_dump',
            'apc_bin_dumpfile',
            'apc_bin_load',
            'apc_bin_loadfile',
            'apc_cas',
            'apc_cache_info',
            'apc_clear_cache',
            'apc_compile_file',
            'apc_dec',
            'apc_define_constants',
            'apc_delete_file',
            'apc_delete',
            'apc_exists',
            'apc_fetch',
            'apc_inc',
            'apc_load_constants',
            'apc_sma_info',
            'apc_store',

            'apc_version'
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
     * Note: Unlike many other mechanisms in PHP, variables stored using apc_add() will persist between requests
     *       (until the value is removed from the cache).
     *
     * @since  3.0.13
     * @param  mixed $key Store the variable using this name. keys are cache-unique, so attempting to use apc_add() to
     *                    store data with a key that already exists will not overwrite the existing data, and will
     *                    instead return FALSE. (This is the only difference between apc_add() and apc_store().)
     *                    If $key is an array set Names in key, variables in value
     * @param  mixed $var The variable to store
     *                    If $key is an array, this parameter is unused and set to NULL
     * @param  int $ttl   Time To Live; store var in the cache for ttl seconds. After the ttl has passed, the stored
     *                    variable will be expunged from the cache (on the next request). If no ttl is supplied
     *                    (or if the ttl is 0), the value will persist until it is removed from the cache manually,
     *                    or otherwise fails to exist in the cache (clear, restart, etc.).
     * @return mixed
     */
    public function apc_add($key, $var = null, $ttl = 0)
    {
        if (is_array($key) && $var === null) {
            throw new \InvalidArgumentException('When $key is set $var cannot be null');
        }

        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_add(%s, %s, %s);',
            var_export($key, true),
            var_export($var, true),
            var_export($ttl, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Get a binary dump of the given files and user variables
     *
     * Returns a binary dump of the given files and user variables from the APC cache. A NULL for files or user_vars
     * signals a dump of every entry, whereas array() will dump nothing
     *
     * @since  3.1.4
     * @param  mixed $files     The files. Passing in NULL signals a dump of every entry, while passing in array() will
     *                          dump nothing.
     * @param  mixed $user_vars The user vars. Passing in NULL signals a dump of every entry, while passing in array()
     *                          will dump nothing.
     * @return mixed            Returns a binary dump of the given files and user variables from the APC cache, FALSE if
     *                          APC is not enabled, or NULL if an unknown error is encountered.
     */
    public function apc_bin_dump(array $files = null, $user_vars = null)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_bin_dump(%s, %s);',
            var_export($files, true),
            var_export($user_vars, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Outputs a binary dump of the given files and user variables from the APC cache to the named file.
     *
     * @since  3.1.4
     * @param  array    $files     The file names being dumped
     * @param  array    $user_vars The user variables being dumped
     * @param  string   $filename  The filename where the dump is being saved
     * @param  integer  $flags     Flags passed to the filename stream. See the file_put_contents() documentation for
     *                             details.
     * @param  resource $context   The context passed to the filename stream. See the file_put_contents() documentation
     *                             for details.
     * @return integer             The number of bytes written to the file, otherwise FALSE if APC is not enabled,
     *                             filename is an invalid file name, filename can't be opened, the file dump can't be
     *                             completed (e.g., the hard drive is out of disk space), or an unknown error was
     *                             encountered.
     */
    public function apc_bin_dumpfile(array $files, array $user_vars, $filename, $flags = 0, $context = null)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_bin_dumpfile(%s, %s, %s, %s, %s);',
            var_export($files, true),
            var_export($user_vars, true),
            var_export($filename, true),
            var_export($flags, true),
            var_export($context, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Loads the given binary dump into the APC file/user cache
     *
     * @since  3.1.4
     * @param  string  $data  The binary dump being loaded, likely from apc_bin_dump().
     * @param  integer $flags Either APC_BIN_VERIFY_CRC32, APC_BIN_VERIFY_MD5, or both.
     * @return boolean        Returns TRUE if the binary dump data was loaded with success, otherwise FALSE is returned.
     *                        FALSE is returned if APC is not enabled, or if the data is not a valid APC binary dump (e.g., unexpected size)
     */
    public function apc_bin_load($data, $flags = 0)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_bin_load(%s, %s);',
            var_export($data, true),
            var_export($flags, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Load a binary dump from a file into the APC file/user cache
     *
     * @since  3.1.4
     * @param  string   $filename The file name containing the dump, likely from apc_bin_dumpfile().
     * @param  resource $context  The files context.
     * @param  integer  $flags    Either APC_BIN_VERIFY_CRC32, APC_BIN_VERIFY_MD5, or both.
     * @return boolean            Returns TRUE on success, otherwise FALSE Reasons it may return FALSE include APC is not
     *                            enabled, filename is an invalid file name or empty, filename can't be opened, the file
     *                            dump can't be completed, or if the data is not a valid APC binary dump (e.g.,
     *                            unexpected size).
     */
    public function apc_bin_loadfile($filename, $context = null, $flags = 0)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_bin_loadfile(%s, %s, %s);',
            var_export($filename, true),
            var_export($context, true),
            var_export($flags, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Updates an old value with a new value
     * apc_cas() updates an already existing integer value if the old parameter matches the currently stored value with the value of the new parameter.
     *
     * @since  3.1.1
     * @param  string $key The key of the value being updated.
     * @param  int    $old The old value (the value currently stored).
     * @param  int    $new The new value to update to
     * @return boolean     Returns TRUE on success or FALSE on failure.
     */
    public function apc_cas($key, $old, $new)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_cas(%s, %s, %s);',
            var_export($key, true),
            var_export($old, true),
            var_export($new, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Retrieves cached information from APC's data store
     *
     * @since  2.0.0
     * @param  string  $cache_type If cache_type is "user", information about the user cache will be returned.
     *                             If cache_type is "filehits", information about which files have been served from the
     *                             bytecode cache for the current request will be returned. This feature must be enabled
     *                             at compile time using --enable-filehits .
     *                             If an invalid or no cache_type is specified, information about the system cache
     *                             (cached files) will be returned.
     * @param  boolean $limited    If limited is TRUE, the return value will exclude the individual list of cache
     *                             entries. This is useful when trying to optimize calls for statistics gathering.
     * @return boolean             Array of cached data (and meta-data) or FALSE on failure
     */
    public function apc_cache_info($cache_type = "", $limited = false)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_cache_info(%s, %s);',
            var_export($cache_type, true),
            var_export($limited, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Clears the user/system cache
     *
     * @since  2.0.0
     * @param  string  $cache_type If cache_type is "user", the user cache will be cleared; otherwise, the system cache
     *                             (cached files) will be cleared.
     * @return boolean             Always returns true
     */
    public function apc_clear_cache($cache_type = "")
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_clear_cache(%s);',
            var_export($cache_type, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Stores a file in the bytecode cache, bypassing all filters.
     *
     * @since  3.0.13
     * @param  string  $filename Full or relative path to a PHP file that will be compiled and stored in the bytecode cache.
     * @param  boolean $atomic   defaults to true
     * @return boolean           Returns TRUE on success or FALSE on failure
     */
    public function apc_compile_file($filename, $atomic = true)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_compile_file(%s, %s);',
            var_export($filename, true),
            var_export($atomic, true)
        ));

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
    public function apc_dec($key, $step = 1, $ref = false)
    {
        $code = new Code();
        $code->addStatement('$success = false;');
        $code->addStatement(sprintf(
            '$result = apc_dec(%s, %s, $success);',
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
     * Defines a set of constants for retrieval and mass-definition
     *
     * define() is notoriously slow. Since the main benefit of APC is to increase the performance of
     * scripts/applications, this mechanism is provided to streamline the process of mass constant definition. However,
     * this function does not perform as well as anticipated.
     *
     * @since  3.0.0
     * @param  string  $key            The key serves as the name of the constant set being stored. This key is used to
     *                                 retrieve the stored constants in apc_load_constants().
     * @param  array   $constants      An associative array of constant_name => value pairs. The constant_name must
     *                                 follow the normal constant naming rules. value must evaluate to a scalar value.
     * @param  boolean $case_sensitive The default behaviour for constants is to be declared case-sensitive; i.e.
     *                                 CONSTANT and Constant represent different values. If this parameter evaluates to
     *                                 FALSE the constants will be declared as case-insensitive symbols.
     * @return boolean                 Returns TRUE on success or FALSE on failure.
     */
    public function apc_define_constants($key, array $constants, $case_sensitive = true)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_define_constants(%s, %s, %s);',
            var_export($key, true),
            var_export($constants, true),
            var_export($case_sensitive, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Deletes files from the opcode cache
     *
     * @since  3.1.1
     * @param  mixed $keys The files to be deleted. Accepts a string, array of strings, or an APCIterator object.
     * @return mixed       Returns TRUE on success or FALSE on failure. Or if keys is an array, then an empty array is
     *                     returned on success, or an array of failed files is returned.
     */
    public function apc_delete_file($keys)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_delete_file(%s);',
            var_export($keys, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Removes a stored variable from the cache
     *
     * @since  3.1.1
     * @param  mixed $key The key used to store the value (with apc_store()).
     * @return mixed      Returns TRUE on success or FALSE on failure.
     */
    public function apc_delete($key)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_delete(%s);',
            var_export($key, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Checks if one or more APC keys exist.
     *
     * @since  3.1.4
     * @param  mixed $keys A string, or an array of strings, that contain keys.
     * @return mixed       Returns TRUE if the key exists, otherwise FALSE Or if an array was passed to keys, then an
     *                     array is returned that contains all existing keys, or an empty array if none exist.
     */
    public function apc_exists($keys)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_exists(%s);',
            var_export($keys, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Fetch a stored variable from the cache
     *
     * @since  3.0.0
     * @param  mixed     $key The key used to store the value (with apc_store()). If an array is passed then each element is fetched and returned.
     * @param  \stdClass $ref success is set to TRUE in success and FALSE in failure
     * @return mixed          The stored variable or array of variables on success; FALSE on failure
     */
    public function apc_fetch($key, $ref = false)
    {
        $code = new Code();
        $code->addStatement('$success = false;');
        $code->addStatement(sprintf('$result = apc_fetch(%s, $success);', var_export($key, true)));
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
    public function apc_inc($key, $step = 1, $ref = false)
    {
        $code = new Code();
        $code->addStatement('$success = false;');
        $code->addStatement(sprintf(
            '$result = apc_inc(%s, %s, $success);',
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
     * Loads a set of constants from the cache
     *
     * @since  3.0.0
     * @param  mixed   $key            The name of the constant set (that was stored with apc_define_constants()) to be
     *                                 retrieved.
     * @param  boolean $case_sensitive The default behaviour for constants is to be declared case-sensitive; i.e.
     *                                 CONSTANT and Constant represent different values. If this parameter evaluates to
     *                                 FALSE the constants will be declared as case-insensitive symbols.
     * @return boolean                 Returns TRUE on success or FALSE on failure
     */
    public function apc_load_constants($key, $case_sensitive = true)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_load_constants(%s, %s);',
            var_export($key, true),
            var_export($case_sensitive, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Retrieves APC's Shared Memory Allocation information
     *
     * @since  2.0.0
     * @param  boolean $limited When set to FALSE (default) apc_sma_info() will return a detailed information about
     *                          each segment.
     * @return boolean          Array of Shared Memory Allocation data; FALSE on failure
     */
    public function apc_sma_info($limited = false)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_sma_info(%s);',
            var_export($limited, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * Cache a variable in the data store
     *
     * Note: Unlike many other mechanisms in PHP, variables stored using apc_store() will persist between requests
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
    public function apc_store($key, $var = null, $ttl = 0)
    {
        if (is_array($key) && $var === null) {
            throw new \InvalidArgumentException('When $key is set $var cannot be null');
        }

        $code = new Code();
        $code->addStatement(sprintf(
            'return apc_store(%s, %s, %s);',
            var_export($key, true),
            var_export($var, true),
            var_export($ttl, true)
        ));

        return $this->adapter->run($code);
    }

    /**
     * @return string
     */
    public function apc_version()
    {
        $code = new Code();
        $code->addStatement('return phpversion("apc");');

        return $this->adapter->run($code);
    }
}
