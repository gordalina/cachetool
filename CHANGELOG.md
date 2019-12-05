# Unreleased

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
