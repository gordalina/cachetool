# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

CacheTool is a PHP CLI tool and library. It manages APCu, OPcache, and the file status cache of a remote PHP process, usually PHP-FPM. It requires PHP >= 8.4. It uses Symfony 8 components and PHPUnit 9.

## Commands

```sh
composer install
composer test                                                 # all tests (runs phpunit)
./vendor/bin/phpunit                                          # all tests
./vendor/bin/phpunit tests/Command/OpcacheStatusCommandTest.php   # one file
./vendor/bin/phpunit --filter testMethodName                  # one test
./vendor/bin/phpunit --coverage-text                          # coverage report (needs pcov or xdebug)
php bin/cachetool opcache:status --cli                        # run from source
box compile                                                   # build cachetool.phar (needs humbug/box)
```

There is no linter or static analysis config.

## Test environment

- Line coverage of `src/` must stay at 100%. If you add or change code, add tests for each new line. Check the result with `--coverage-text`.
- If a line cannot run in a test, such as `exit()`, mark it with `// @codeCoverageIgnore`. Use this only when no test can reach the line.
- Many tests run real code in a PHP subprocess. They need `apcu` loaded and these ini values: `apc.enable_cli=1`, `opcache.enable=1`, `opcache.enable_cli=1`. CI also sets `opcache.jit=disable`. `tests/cachetool.ini` has a sample.
- If an extension is not available, `CommandTest::assertHasApcu()` and `assertHasOpcache()` skip the test.
- `tests/Adapter/FastCGITest.php` starts a real `php-fpm` through `tests/PhpFpmRunner.php`. If `php-fpm` is not on the `PATH`, the tests that need it are skipped.
- The HTTP client tests resolve hostnames such as `_.127.0.0.1.sslip.io`. These tests need a DNS server that knows sslip.io.

## Architecture

The tool does not read the cache of the local process. It generates PHP source code, sends that code to the target PHP process, and reads back a serialized result.

1. **`CacheTool`** (`src/CacheTool.php`) is the facade. `__call()` sends each method call to the proxy that declares the function name in `getFunctions()`. The `@method` docblock lists all of these functions. If you add a proxy function, update that docblock.
2. **Proxies** (`src/Proxy/`) implement `ProxyInterface`. Each method builds a `Code` object that contains PHP statements. Arguments go into the code through `var_export()`. The method then calls `$this->adapter->run($code)`.
3. **`Code`** (`src/Code.php`) wraps the statements in a template. The template catches errors and exceptions and echoes `serialize(['result' => ..., 'errors' => ...])`. `writeTo()` writes the file and checks its MD5 sum.
4. **Adapters** (`src/Adapter/`) extend `AbstractAdapter` and implement `doRun()`. `doRun()` writes the code to a temporary file, runs it, and returns the raw output. `AbstractAdapter::run()` unserializes the output. If the `errors` array is not empty, it throws a `RuntimeException`.
   - `Cli` runs the file with `PHP_BINARY` in a subprocess.
   - `FastCGI` sends the file path to PHP-FPM. The file must be in a directory that FPM can read. The `--fcgi-chroot` option removes a path prefix before the request.
   - `Web` writes the file into a document root and fetches it over HTTP through `Adapter/Http/*`.
5. **Commands** (`src/Command/`) extend `AbstractCommand`. They get `CacheTool` from a Symfony DI container with `$this->getCacheTool()`. Opcache commands extend `AbstractOpcacheCommand` and call `ensureSuccessfulOpcacheCall()`.
6. **`Console\Application`** registers the commands in `getDefaultCommands()`. It filters APCu and OPcache commands by the `extensions` config value. If you add a command, register it there and add it to the command list in `README.md`. `buildContainer()` merges the CLI options into `Config`, selects the adapter, and creates the `CacheTool` instance.
7. **`Console\Config`** holds the defaults. `Config::factory()` searches for `.cachetool.yml` or `.cachetool.yaml` in the current directory and each parent, then in `$HOME`, then in `/etc`.

### Command tests

Command tests extend `tests/Command/CommandTest.php`. `runCommand($cmd, $mockData)` runs the real `Application` with the `Cli` adapter. If `$mockData` is set, the adapter does not execute code. It returns `$mockData` as the result.

## Release

- Add an entry to `CHANGELOG.md` for each version.
- If you push a tag, CI tests the code, builds the `.phar`, `.phar.gz`, and `.phar.bz2` files with box, pushes Docker images, creates a GitHub release, and runs `bin/update-gh-pages.sh`.
- Box replaces `@package_version@` in `Application` with the git version.
