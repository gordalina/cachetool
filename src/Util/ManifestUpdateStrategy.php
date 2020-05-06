<?php
/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Util;

use Humbug\SelfUpdate\Updater;
use Humbug\SelfUpdate\VersionParser;
use Humbug\SelfUpdate\Exception\HttpRequestException;
use Humbug\SelfUpdate\Exception\InvalidArgumentException;

class ManifestUpdateStrategy extends \Humbug\SelfUpdate\Strategy\ShaStrategy
{
    /**
     * @var string
     */
    protected $manifestUrl;

    /**
     * Retrieve the current manifest available remotely.
     *
     * @param Updater $updater
     * @return string|bool
     */
    public function getCurrentRemoteVersion(Updater $updater)
    {
        /** Switch remote request errors to HttpRequestExceptions */
        set_error_handler(array($updater, 'throwHttpRequestException'));
        $json = humbug_get_contents($this->getManifestUrl());
        $manifest = json_decode($json, true);
        restore_error_handler();
        if (!is_array($manifest) || !isset($manifest[count($manifest) - 1]['sha1'])) {
            throw new HttpRequestException(sprintf(
                'Request to URL did not return a manifest.json file: %s', $this->getManifestUrl()
            ));
        }

        $versions = new VersionParser(array_map(function($version) { return $version['version']; }, $manifest));
        $latest = $versions->getMostRecentStable();
        if (empty($latest)) {
            throw new HttpRequestException(
                'Manifest did not contain a recent stable version'
            );
        }
        $latestVersionArray = array_filter($manifest, function($version) use($latest) { return $version['version'] === $latest; });
        $latestVersion = array_shift($latestVersionArray);
        $latestHash = $latestVersion['sha1'];
        if (!preg_match('%^[a-z0-9]{40}$%', $latestHash, $matches)) {
            throw new HttpRequestException(
                'Manifest did not contain a valid SHA1 signature in the latest entry'
            );
        }

        $this->setPharUrl($latestVersion['url']);

        return $latestHash;
    }

    /**
     * Set URL to manifest file
     *
     * @param string $url
     */
    public function setManifestUrl($url)
    {
        if (!$this->validateAllowedUrl($url)) {
            throw new InvalidArgumentException(
                sprintf('Invalid url passed as argument: %s.', $url)
            );
        }
        $this->manifestUrl = $url;
    }

    /**
     * Get URL for manifest file
     *
     * @return string
     */
    public function getManifestUrl()
    {
        return $this->manifestUrl;
    }

}
