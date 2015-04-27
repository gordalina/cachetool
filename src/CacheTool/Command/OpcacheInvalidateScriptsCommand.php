<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Command;

use CacheTool\Util\Formatter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpcacheInvalidateScriptsCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('opcache:invalidate:scripts')
            ->setDescription('Remove scripts from the opcode cache')
            ->addArgument('path', InputArgument::REQUIRED)
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('Zend OPcache');
        $path = $input->getArgument('path');

        $info = $this->getCacheTool()->opcache_get_status(true);

        if ($info === false) {
            throw new \RuntimeException('opcache_get_status(): No Opcache status info available.  Perhaps Opcache is disabled via opcache.enable or opcache.enable_cli?');
        }

        $table = $this->getHelper('table');
        $table
            ->setHeaders(array(
                'Cleaned',
                'Filename'
            ))
            ->setRows($this->processFilelist($info['scripts'], $path))
        ;

        $table->render($output);
    }

    protected function processFileList(array $cacheList, $path)
    {
        $list = array();

        sort($cacheList);

        foreach ($cacheList as $item) {
            $filename = $this->processFilename($item['full_path']);
            if (preg_match('|' . $path . '|', $filename)) {
                $list[] = array(
                    $this->getCacheTool()->opcache_invalidate($filename),
                    $filename
                );
            }
        }

        return $list;
    }

    protected function processFilename($filename)
    {
        $dir = getcwd();

        if (0 === strpos($filename, $dir)) {
            return "." . substr($filename, strlen($dir));
        }

        return $filename;
    }
}
