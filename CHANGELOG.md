# 10.0.0

- [#261](https://github.com/gordalina/cachetool/pull/261) Use `sys_get_temp_dir()` as default temporary directory

# 9.2.1

- Nothing changed

# 9.2.0

- [#254](https://github.com/gordalina/cachetool/pull/254) Allow excluding files in opcache:status:scripts command
- Bump symfony/http-client from 6.1.6 to 6.4.15
- Bump symfony/http-foundation from 6.1.6 to 6.4.14
- Bump symfony/process from 6.1.3 to 6.4.14
- [#252](https://github.com/gordalina/cachetool/pull/252) Fix some typos

# 9.1.0

- Fixed an issue where SELinux would prevent writing files to the temp directory.

# 9.0.3

- Lock `consolidation/self-update` dependency to `~2.1.0`

# 9.0.2

- Overwrite v8 as latest

# 9.0.1

- [#223](https://github.com/gordalina/cachetool/pull/223) Refactored parsing command line arguments (@sideshowcoder)
- [#222](https://github.com/gordalina/cachetool/pull/222) Skip running tests if sslip dns isn't configured (@sideshowcoder)
- [#221](https://github.com/gordalina/cachetool/pull/221) Parse --web option correctly (@sideshowcoder)

# 9.0.0

- **Breaking Change:** Remove support for PHP 8.0
- **Breaking Change:** Remove support for Symfony < 6.x
- **Breaking Change:** Remove support for Monolog < 3.x
- **Breaking Change:** Remove support for psr/log < 2.x
- [#215](https://github.com/gordalina/cachetool/pull/215) Add support for monolog 3.0

# 8.6.1

- Lock `consolidation/self-update` dependency to `~2.1.0`.

# 8.5.1

- [#229](https://github.com/gordalina/cachetool/pull/229) Fix deprecated string interpolation in PHP 8.

# 8.5.0

- [#206](https://github.com/gordalina/cachetool/pull/206) Add phpdoc for proxy classes (@myfluxi)
- [#212](https://github.com/gordalina/cachetool/issues/212) Fix inconsistency when setting temp dir.
- [#214](https://github.com/gordalina/cachetool/issues/214) Update documentation of exclude path in opcache:invalidate:scripts.
- [#216](https://github.com/gordalina/cachetool/issues/216) Do not display errors when running the CLI.
- Update GitHub actions & add Ubuntu 22.04 as a target

# 8.4.1

- [#211](https://github.com/gordalina/cachetool/pull/211) Always use full paths in `opcache:*:scripts` commands
- [#206](https://github.com/gordalina/cachetool/pull/206) Typo & documentation fixes

# 8.4.0

- PHP 8.1 compatibility

# 8.3.0

- Allow underscores in web url
- Display realpath stat expiration in human-readable UTC date

# 8.2.0

- [#203](https://github.com/gordalina/cachetool/pull/203) Update psr/log requirements to ^1|^2|^3
- [#201](https://github.com/gordalina/cachetool/pull/201) Cleanup unused use statements
- [#200](https://github.com/gordalina/cachetool/pull/200) Preserve Symfony 6 compatibility

# 8.1.0

- Added support for Symfony 6.x
- Added support for preview self-updates
- Updated dependencies

# 8.0.2

- Added building amd64 and arm64 docker images

# 8.0.1

- Changed dependency to PHP >=8.0.0
- Added docker builds

# 8.0.0

- [#190](https://github.com/gordalina/cachetool/pull/190) Use server timezone when displaying dates (@LeoShivas)
- Removed support for PHP 7.x

# 7.1.0

- Allow underscores in web url

# 7.0.0

- Add compatibility to Symfony 5.3
- Removes dependency pin on `psr/container@1.0.0`

# 6.6.0

- Add `opcache_invalidate_many()` to invalidate many scripts within the same request.
- Update `opcache:invalidate:scripts` to issue only one request for all files to be invalidated.

# 6.5.0

- [#185](https://github.com/gordalina/cachetool/pull/185) Support adding host header to web adapter (@jorissteyn)
- Default web Http adapter to `FileGetContents` if none is provided.

# 6.4.0

- [#183](https://github.com/gordalina/cachetool/pull/183) Pin psr/container to 1.0.0 & Throw error if files can't be written

# 6.3.1

- [#178](https://github.com/gordalina/cachetool/pull/178) Fix web cli parameter option (@nlemoine)

# 6.3.0

- [#177](https://github.com/gordalina/cachetool/pull/177) Add SymfonyHttpClient file configuration (@nlemoine)

# 6.2.0

- [#176](https://github.com/gordalina/cachetool/issues/172) Add SymfonyHttpClient Adapter (@marvinhinz)

# 6.1.2

- [#173](https://github.com/gordalina/cachetool/issues/172) Fix self update and add self-update alias command.

# 6.1.1

- [#172](https://github.com/gordalina/cachetool/pull/172) Use stable releases for consolidation/self-update
- Remove dev minimum-stability in composer.json

# 6.1.0
- [#169](https://github.com/gordalina/cachetool/pull/169) Use consolidation/self-update instead of padraic/phar-updater

# 6.0.0

- [#159](https://github.com/gordalina/cachetool/issues/159) Fix missing curl redirect flag
- [#167](https://github.com/gordalina/cachetool/issues/167) Release compressed phars
- [#166](https://github.com/gordalina/cachetool/issues/159) Add support to PHP 8.0
- Remove support for PHP 7.2

# 5.1.3

- Fix issue where `cachetool.yml` files were not being loaded

# 5.1.2

- Add logging information about version & configuration during startup
- [#157](https://github.com/gordalina/cachetool/issues/157) Add documentation about docker images. Thanks to @NITEMAN and @jonhattan
- Ensure http://gordalina.github.io/cachetool/downloads/cachetool.phar is updated with latest version

# 5.1.1

- Add more information when file_get_contents() fail with web adapter
- Correctly delete files when calling `opcache:reset:file-cache`

# 5.1.0

- [#77](https://github.com/gordalina/cachetool/issues/77) Add IPv6 support
- [#156](https://github.com/gordalina/cachetool/pull/156) Add `--config` argument & yaml extension (@rayderua)
- [#151](https://github.com/gordalina/cachetool/pull/152) Generate unique identifiers with more entropy (@jonhattan)
- [#146](https://github.com/gordalina/cachetool/pull/146) Add Eval & File commands
- [#144, #149](https://github.com/gordalina/cachetool/pull/149) Replace `herrera-io/phar-update` with `padriac/phar-updated`
- Use GitHub Actions to issue releases and to self-update the binary

# 5.0.0

- **Breaking Change**: PHP 7.2 is now required
- [#140](https://github.com/gordalina/cachetool/issues/140) Add `opcache:reset:file-cache` command that clears the file cache directory contents
- [#88](https://github.com/gordalina/cachetool/issues/88), [#92](https://github.com/gordalina/cachetool/issues/92) Add support for `opcache.file_cache_only`
- [#87](https://github.com/gordalina/cachetool/issues/87) Fix `self-update` command
- [#135](https://github.com/gordalina/cachetool/issues/135) Automatically creates temporary directory
- [#142](https://github.com/gordalina/cachetool/issues/142) Migrating to assertStringContainsString PHPUnit 8.x
- [#141](https://github.com/gordalina/cachetool/issues/141) @expectedException annotation deprecated on PHPUnit 8.x
- [#137](https://github.com/gordalina/cachetool/pull/137) Update to dependencies to support Symfony 5
- [#138](https://github.com/gordalina/cachetool/pull/138) Add dependency check before running cachetool
- [#139](https://github.com/gordalina/cachetool/pull/139) [BUGFIX] Correct link to Julien Pauli´s blog post #139
- [#133](https://github.com/gordalina/cachetool/pull/133) Switch FastCGI library (hollodotme/fast-cgi-client)
- [#132](https://github.com/gordalina/cachetool/pull/132) CI in PHP 7.4
- [#129](https://github.com/gordalina/cachetool/pull/129) Implement --batch and --exclude options for opcache:compile:scripts command
- [#131](https://github.com/gordalina/cachetool/pull/131) Add SERVER_ADDR, REMOTE_ADDR, REMOTE_PORT in FCGI call
- Update `http` to `https` in documentation

# 4.1.0

- Support for APC cache has been removed.
- Use PSR-4 autoloading.
- Use `PHP_BINARY` in the CLI adapter to execute PHP.
- Passing `--tmp-dir` a directory that is not writable now fails rather than
  falling back to the default directories.
- Add workaround for `opcache_reset()` bug
  <https://bugs.php.net/bug.php?id=71621>.
- Add documentation on how to get older cachetool versions
- Fixed web adapter from config

# 4.0.0

- Supports only PHP>=7.1
- Supports only Symfony 4

# 3.2.1

- Uses Symfony 3.x

# 3.1.0

- Test with PHP 7.2
- [#65](https://github.com/gordalina/cachetool/pull/65) Add support for symfony4 (@daniel-iwaniec)
- [#61](https://github.com/gordalina/cachetool/pull/61) Possibility to communicate with chrooted FastCGI (@rusnak)
- [#63](https://github.com/gordalina/cachetool/pull/63) allow to force scripts invalidation (@jaymecd)
- [#69](https://github.com/gordalina/cachetool/pull/69) Allow configuration file to specify which commands are available. (@bangpound)
- [#64](https://github.com/gordalina/cachetool/pull/64) compile file into opcache (@jaymecd)

# 3.0.0

- [#57](https://github.com/gordalina/cachetool/pull/57) Web Adapter (@scuben)
- [#56](https://github.com/gordalina/cachetool/pull/56) Update useful commands list (@borisbabic)
- [#54](https://github.com/gordalina/cachetool/pull/54) Removed exceptions thrown when storing NULL values (@sebastien-fauvel)
- [#52](https://github.com/gordalina/cachetool/pull/52) Added APCIterator logic (@MikeSorokin)
- [#49](https://github.com/gordalina/cachetool/pull/49) Making cachetool compatible w/ php7 (@jrmbrgs)
- [#46](https://github.com/gordalina/cachetool/pull/46) Use cachetool.yml config when empty fcgi option passed (@vigneshgurusamy)
- [3125bae](https://github.com/gordalina/cachetool/commit/3125bae) Glob sockets when fcgi adapter is supplied but with no socket defined (from #39)
- [714bc36](https://github.com/gordalina/cachetool/commit/714bc36) Add php 7.1 to travis test matrix
