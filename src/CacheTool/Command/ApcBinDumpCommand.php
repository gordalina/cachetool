<?php

namespace CacheTool\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApcBinDumpCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('apc:bin:dump')
            ->setDescription('Get a binary dump of files and user variables')
            ->addOption('--file', '-f', InputOption::VALUE_OPTIONAL)
            ->setHelp('');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apc');

        $file = $input->getOption('file');
        $dump = $this->getCacheTool()->apc_bin_dump(null, null);

        if ($file) {
            file_put_contents($file, $dump);
        } else {
            echo $dump;
        }
    }
}
