<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Adapter;

use CacheTool\Code;

abstract class AbstractAdapter
{
    /**
     * @param  Code   $code
     * @return string
     */
    abstract protected function doRun(Code $code);

    /**
     * @param  Code   $code
     * @throws \RuntimeException
     * @return mixed
     */
    public function run(Code $code)
    {
        $result = @unserialize($this->doRun($code));

        if (!is_array($result)) {
            throw new \RuntimeException('Could not unserialize output');
        }

        if (empty($result['errors'])) {
            return $result['result'];
        }

        $msgs = array();

        foreach ($result['errors'] as $error) {
            $msgs[] = "{$error['str']} (error code: {$error['no']})";
        }

        throw new \RuntimeException(implode(PHP_EOL, $msgs));
    }

    /**
     * @return string
     */
    protected function createTemporaryFile()
    {
        $file = sprintf("%s/cachetool-%s.php", sys_get_temp_dir(), uniqid());

        touch($file);
        chmod($file, 0666);

        return $file;
    }
}
