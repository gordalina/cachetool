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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SelfUpdateCommand extends Command
{
    const RELEASE_URL = 'https://api.github.com/repos/gordalina/cachetool/releases/latest';

    public function __construct(
        private string $version,
        private string $pharPath,
        private ?HttpClientInterface $client = null,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('self:update')
            ->setAliases(['self-update', 'update'])
            ->setDescription('Updates cachetool.phar to the latest stable version')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!is_writable($this->pharPath) || !is_writable(dirname($this->pharPath))) {
            throw new \RuntimeException("{$this->pharPath} is not writable");
        }

        $client = $this->client ?? HttpClient::create(['headers' => ['User-Agent' => 'cachetool']]);
        $release = $client->request('GET', self::RELEASE_URL)->toArray();
        $latest = ltrim($release['tag_name'], 'v');

        if (version_compare($this->version, $latest, '>=')) {
            $output->writeln("cachetool {$this->version} is the latest version");
            return self::SUCCESS;
        }

        $asset = current(array_filter($release['assets'], fn ($a) => $a['name'] === 'cachetool.phar'));
        if (!$asset) {
            throw new \RuntimeException("Release {$latest} has no cachetool.phar");
        }

        $output->writeln("Downloading cachetool {$latest}");
        $tmp = dirname($this->pharPath) . '/cachetool-' . $latest . '.phar';
        file_put_contents($tmp, $client->request('GET', $asset['browser_download_url'])->getContent());

        try {
            new \Phar($tmp);
        } catch (\UnexpectedValueException $e) {
            unlink($tmp);
            throw new \RuntimeException("The download is not a valid phar: {$e->getMessage()}");
        }

        chmod($tmp, fileperms($this->pharPath));
        rename($tmp, $this->pharPath);
        $output->writeln("<info>Updated cachetool to {$latest}</info>");

        // The running phar is gone. Exit before PHP loads a class from it.
        if (\Phar::running()) {
            exit(0); // @codeCoverageIgnore
        }

        return self::SUCCESS;
    }
}
