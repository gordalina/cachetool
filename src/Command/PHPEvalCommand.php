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

class PHPEvalCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('php:eval')
            ->setDescription('Run a specified PHP code')
            ->addArgument('code', InputArgument::REQUIRED)
            ->setHelp('In order to use this your PHP code needs to return the value you want to see
            example: "return gethostname();"');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $code = $input->getArgument('code');
        $success = $this->getCacheTool()->_eval($code);
        $output->writeln(sprintf("<comment>Eval Output:\n <info>%s</info></comment>", $success));
        return 0;
    }
}
