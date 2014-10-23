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

use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
use Herrera\Phar\Update\Update;
use Herrera\Version\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Command
{
    const MANIFEST_FILE = 'http://gordalina.github.io/cachetool/manifest.json';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setAliases(array('selfupdate'))
            ->setDescription('Updates cachetool.phar to the latest version')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manifest = Manifest::loadFile(self::MANIFEST_FILE);

        $currentVersion = Parser::toVersion($this->getApplication()->getVersion());
        $update = $manifest->findRecent($currentVersion, true);

        if (false === $update instanceof Update) {
            $output->writeln(sprintf('You are already using the latest version: <info>%s</info>', $currentVersion));

            return 0;
        }

        $output->writeln(sprintf('Updating to version <info>%s</info>', $update->getVersion()));

        $manager = new Manager($manifest);
        $manager->update($this->getApplication()->getVersion(), true);

        $output->writeln(sprintf('SHA1 verified <info>%s</info>', $update->getSha1()));
    }
}
