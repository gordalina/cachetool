<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Console;

use Symfony\Component\Yaml\Parser;

class Config implements \ArrayAccess
{
    private $config = array(
        'adapter' => 'fastcgi',
        'extensions' => ['apcu', 'opcache'],
        'fastcgi' => null,
        'temp_dir' => null
    );

    public function __construct(array $config = array())
    {
        if (!empty($config)) {
            $this->config = array_replace($this->config, $config);

            if (!isset($this->config['temp_dir'])) {
                $this->config['temp_dir'] = sys_get_temp_dir();
            }
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->config[$offset];
        }
    }

    public function offsetSet($offset, $value)
    {
        $this->config[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }

    public static function factory() {
        $previous = null;
        $path = getcwd();
        $paths = array();
        $yaml = new Parser();

        while (($path = realpath($path)) && $path !== $previous) {
            $paths[] = "{$path}/.cachetool.yml";
            $previous = $path;
            $path .= '/../';
        }

        if ($home = static::getUserHomeDir()) {
          $paths[] = "{$home}/.cachetool.yml";
        }

        $paths[] = '/etc/cachetool.yml';

        foreach ($paths as $path) {
            if (is_file($path)) {
                $config = $yaml->parse(file_get_contents($path));
                return new Config($config);
            }
        }

        return new Config();
    }

    /**
     * Return the user's home directory.
     * From drush
     *
     * @return string
     */
    protected static function getUserHomeDir() {
        // Cannot use $_SERVER superglobal since that's empty during UnitUnishTestCase
        // getenv('HOME') isn't set on Windows and generates a Notice.
        $home = getenv('HOME');

        if (!empty($home)) {
            // home should never end with a trailing slash.
            $home = rtrim($home, '/');
        } elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
            // home on windows
            $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
            // If HOMEPATH is a root directory the path can end with a slash. Make sure
            // that doesn't happen.
            $home = rtrim($home, '\\/');
        }

        return empty($home) ? NULL : $home;
    }
}
