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
