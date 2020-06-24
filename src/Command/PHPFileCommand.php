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

class PHPFileCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('php:file')
            ->setDescription('Run a specified file')
            ->addArgument('file', InputArgument::REQUIRED)
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');
        if (!file_exists($file)) {
            throw new \RuntimeException("file not found");
            return 1;
        }

        $code = file_get_contents($file);
        $code = str_replace('<?php','',$code); 
        $success = $this->getCacheTool()->_eval($code);
        $output->writeln(sprintf("<comment>File Output:\n <info>%s</info></comment>", $success));
        return 0;
    }
}
