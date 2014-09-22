<?php

namespace CacheTool\Proxy;

use CacheTool\Adapter\AdapterInterface;
use CacheTool\Code;

class PhpProxy implements ProxyInterface
{
    /**
     * @var AdapterInterface
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

            '_eval',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setAdapter(AdapterInterface $adapter)
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
     * @param  string $what An optional extension name
     * @return string
     */
    public function phpversion($expression = null)
    {
        $code = new Code();
        $code->addStatement(sprintf(
            "return phpversion(%s);",
            var_export($expression, true)
        ));

        return $this->adapter->run($code);
    }


    /**
     * Evaluate a string as PHP code
     *
     * @param  string $code Evaluates the given code as PHPEvaluates the given code as PHPEvaluates the given code as PHPEvaluates the given code as PHP
     * @return mixed
     */
    public function _eval($php)
    {
        $code = new Code();
        $code->addStatement($php);

        return $this->adapter->run($code);
    }
}
