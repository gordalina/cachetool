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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApcRegexpDeleteCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apc:regexp:delete')
            ->setDescription('Deletes all APC key matching a regexp')
            ->addArgument('regexp', InputArgument::REQUIRED)
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apc');

        $regexp = $input->getArgument('regexp');

        $user = $this->getCacheTool()->apc_cache_info('user');

        $keys = array();
        foreach ($user['cache_list'] as $key) {
            $string = $key['info'];
            if (preg_match('|' . $regexp . '|', $string)) {
                $keys[] = $key;
            }
        }
        $cpt = 0;
        $table = $this->getHelper('table');
        $table->setHeaders(array('Key', 'TTL', ));
        $table->setRows($keys);
        $table->render($output);
        foreach ($keys as $key) {
            $success = $this->getCacheTool()->apc_delete($key['info']);
            if ($output->isVerbose()) {
                if ($success) {
                    $output->writeln("<comment>APC key <info>{$key['info']}</info> was deleted</comment>");
                } else {
                    $output->writeln("<comment>APC key <info>{$key['info']}</info> could not be deleted.</comment>");
                }
            }
            $cpt ++;
        }
        if ($output->isVerbose()) {
            $output->writeln("<comment>APC key <info>{$cpt}</info> keys treated.</comment>");
        }
        return 1;
    }
}