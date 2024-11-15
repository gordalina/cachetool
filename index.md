# CacheTool - Manage cache in the CLI

[![Build Status](https://img.shields.io/github/actions/workflow/status/gordalina/cachetool/ci.yml?branch=main&style=flat-square)](https://github.com/gordalina/cachetool/actions)
[![Coverage Status](https://img.shields.io/coveralls/github/gordalina/cachetool/master?style=flat-square)](https://coveralls.io/github/gordalina/cachetool?branch=master)
[![Version](https://img.shields.io/github/v/tag/gordalina/cachetool?sort=semver&style=flat-square)](https://github.com/gordalina/cachetool/releases)
[![Downloads](https://img.shields.io/packagist/dt/gordalina/cachetool?style=flat-square)](https://packagist.org/packages/gordalina/cachetool)

CacheTool allows you to work with APCu, OPcache, and the file status cache
through the CLI. It will connect to a FastCGI server (like PHP-FPM) and operate
on its cache.

Why is this useful?

- Maybe you want to clear the bytecode cache without reloading php-fpm or using a web endpoint
- Maybe you want to have a cron which deals with cache invalidation
- Maybe you want to see some statistics right from the console
- And many more...

Note that, unlike APCu and Opcache, the file status cache is per-process rather than stored in shared memory. This means that running `stat:clear` against PHP-FPM will only affect whichever FPM worker responds to the request, not the whole pool. [Julien Pauli has written a post](http://blog.jpauli.tech/2014-06-30-realpath-cache-html/) with more details on how the file status cache operates.

## Installation - Latest version

```sh
curl -sLO https://github.com/gordalina/cachetool/releases/latest/download/cachetool.phar
chmod +x cachetool.phar
```

You can alternatively download a compressed phar by using the URLs below.

```sh
# if your php installation has the zlib extension enabled
https://github.com/gordalina/cachetool/releases/latest/download/cachetool.phar.gz

# if your php installation has the bzip2 extension enabled
https://github.com/gordalina/cachetool/releases/latest/download/cachetool.phar.bz2
```

CacheTool is also packaged as a docker container available in [docker hub](https://hub.docker.com/r/gordalina/cachetool) and [github](https://github.com/gordalina/cachetool/pkgs/container/cachetool) container registries.

See below for [docker usage instructions](#usage-via-docker).

## Installation - old versions

Use tag name in the binary file name. E.g to download cachetool 3.2.2
which is compatible with PHP `>=5.5.9` use: `cachetool-3.2.2.phar`

```sh
curl -sO https://gordalina.github.io/cachetool/downloads/cachetool-3.2.2.phar
chmod +x cachetool-3.2.2.phar
```

## Usage

CacheTool requires an adapter to connect to, it can be `cli`, `fcgi`, and `web`.
The `fcgi` adapter is the most common, as it connects directly to php-fpm.

You can pass an IP address or a unix socket to the `--fcgi` adapter, or leave it blank and CacheTool will try to find the php-fpm socket for you. If it can't find it, it will default to `127.0.0.1:9000`.

- You can let CacheTool find the unix socket for you, or default to IP.

```sh
php cachetool.phar apcu:cache:info --fcgi
```

- You can connect to a fastcgi server using an IP address

```sh
php cachetool.phar apcu:cache:info --fcgi=127.0.0.1:9000
```

- You can connect to a fastcgi server using a unix socket

```sh
php cachetool.phar opcache:status --fcgi=/var/run/php5-fpm.sock
```

- To connect to a chrooted fastcgi server you need to set `--fcgi-chroot` and `--tmp-dir` parameters

```sh
php cachetool.phar opcache:status --fcgi=/var/run/php5-fpm.sock --fcgi-chroot=/path/to/chroot --tmp-dir=/path/to/chroot/tmp
```

- Using the CLI

```sh
php cachetool.phar opcache:status --cli
```

- Using an HTTP interface

```sh
php cachetool.phar opcache:status --web --web-path=/path/to/your/document/root --web-url=http://url-to-your-document.root
```

- Using SymfonyHttpClient

```sh
php cachetool.phar opcache:status --web=SymfonyHttpClient --web-path=/path/to/your/document/root --web-url=http://url-to-your-document.root
```

You have some useful commands that you can use

```sh
 apcu
  apcu:cache:clear            Clears APCu cache
  apcu:cache:info             Shows APCu user & system cache information
  apcu:cache:info:keys        Shows APCu keys cache information
  apcu:key:delete             Deletes an APCu key
  apcu:key:exists             Checks if an APCu key exists
  apcu:key:fetch              Shows the content of an APCu key
  apcu:key:store              Store an APCu key with given value
  apcu:regexp:delete          Deletes all APCu key matching a regexp
  apcu:sma:info               Show APCu shared memory allocation information
 opcache
  opcache:compile:script      Compile single script from path to the opcode cache
  opcache:compile:scripts     Compile scripts from path to the opcode cache
  opcache:configuration       Get configuration information about the cache
  opcache:invalidate:scripts  Remove scripts from the opcode cache
  opcache:reset               Resets the contents of the opcode cache
  opcache:reset:file-cache    Deletes all contents of the file cache directory
  opcache:status              Show summary information about the opcode cache
  opcache:status:scripts      Show scripts in the opcode cache
 stat
  stat:clear                  Clears the file status cache, including the realpath cache
  stat:realpath_get           Show summary information of realpath cache entries
  stat:realpath_size          Display size of realpath cache
```

## Usage via Docker

Images are available in docker hub and github container registries:

- `gordalina/cachetool:latest`
- `ghcr.io/gordalina/cachetool:latest`

This is an example run with the `web` adapter:

```sh
APPDIR="/var/www/example.com"
DOCROOT="/var/www/example.com/current/web"
URL="http://example.com"

docker run --rm -v $APPDIR:$APPDIR -w $DOCROOT gordalina/cachetool cachetool --web --web-url=$URL [options] [arguments]
```

If the website is behind a proxy and/or load balancer you may want to ask directly the webserver instead of the public facing ip. Additionally, the webserver may be listening in another port. This is an example for running cachetool from the webserver host in such a setup:

```sh
DOMAIN="example.com"
PORT="8008"
APPDIR="/var/www/example.com"
DOCROOT="/var/www/example.com/current/web"
URL="http://$DOMAIN:$PORT"

docker run --rm --add-host $DOMAIN:172.17.0.1 -v $APPDIR:$APPDIR -w $DOCROOT sbitio/cachetool --web --web-url=$URL [options] [arguments]
```

Thank you to @jonhattan and @NITEMAN for the [work with docker](https://github.com/sbitio/docker-cachetool).


## Configuration File

You can have a configuration file with the adapter configuration, allowing you to
call CacheTool without `--fcgi`, `--cli`, or `--web` option.

You can pass a `--config <file>` option to the application or it will choose to load
a file automaically.

The file must be named `.cachetool.yml` or `.cachetool.yaml`. CacheTool will look for
this file on the current directory and in any parent directory until it finds one.
If the paths above fail it will try to load `/etc/cachetool.yml` or `/etc/cachetool.yaml` configuration file.

An example of what this file might look like is:

Will connect to fastcgi at 127.0.0.1:9000

```yml
adapter: fastcgi
fastcgi: 127.0.0.1:9000
```

Will connect to cli (disregarding fastcgi configuration)

```yml
adapter: cli
fastcgi: /var/run/php5-fpm.sock
```

CacheTool writes files to the system temporary directory (given by `sys_get_temp_dir()`)
but if you want to change this, for example, if your fastcgi service is run with PrivateTemp
you can set it on the config file:

```yml
adapter: fastcgi
fastcgi: /var/run/php5-fpm.sock
temp_dir: /dev/shm/cachetool
```

Example for the web adapter:

```yml
adapter: web
webClient: SymfonyHttpClient # defaults to FileGetContents
webUrl: http://example.com
webPath: /var/www/example.com/current/web
webBasicAuth: user:password
```

You can define the supported extensions in the config file. By default, `apcu`,
and `opcache` are enabled. To disable `apcu`, add this to your config file:

```yml
extensions: [opcache]
```

## Usage (as a library)

Add it as a dependency

```sh
composer require gordalina/cachetool
```

If you want to use it in a Symfony 2.x project, require the `1.x` version

```sh
composer require gordalina/cachetool:~1.0
```

Create instance

```php
use CacheTool\Adapter\FastCGI;
use CacheTool\CacheTool;

$adapter = new FastCGI('127.0.0.1:9000');
$cache = CacheTool::factory($adapter, '/tmp');
```

You can use `apcu` and `opcache` functions

```php
$cache->apcu_clear_cache();
$cache->opcache_reset();
```

## Proxies

CacheTool depends on `Proxies` to provide functionality, by default when creating a CacheTool instance from the factory
all proxies are enabled [`ApcuProxy`](https://github.com/gordalina/cachetool/blob/master/src/Proxy/ApcuProxy.php), [`OpcacheProxy`](https://github.com/gordalina/cachetool/blob/master/src/Proxy/OpcacheProxy.php) and [`PhpProxy`](https://github.com/gordalina/cachetool/blob/master/src/Proxy/PhpProxy.php), you can customize it or extend to your will like the example below:

```php
use CacheTool\Adapter\FastCGI;
use CacheTool\CacheTool;
use CacheTool\Proxy;

$adapter = new FastCGI('/var/run/php5-fpm.sock');
$cache = new CacheTool();
$cache->setAdapter($adapter);
$cache->addProxy(new Proxy\ApcuProxy());
$cache->addProxy(new Proxy\PhpProxy());
```

## Updating CacheTool

Running `php cachetool.phar self-update` will update a phar install with the latest version.

## Building cachetool.phar

Cachetool uses [box](https://github.com/box-project/box) to built the phar, see [box-project/installation.md](https://github.com/box-project/box/blob/main/doc/installation.md) on the best way to install it in your situation. To built run `box compile`, which will output `cachetool.phar` in the project root directory.

## Testing

After running `composer install`, run `./vendor/bin/phpunit`

### Troubleshooting test failures

#### sslip.io

Tests in `tests/Adapter/Http/FileGetContentsTest` and `tests/Adapter/Http/SymfonyHttpClientTest` rely on [sslip.io](https://sslip.io/) to resolve hostnames containing an IP to the IP contained. For this to work a nameserver from sslip.io needs to be in the DNS servers configured on the host which runs those tests, otherwise hostnames like `_.127.0.0.1.sslip.io` used for testing will not resolve. The IP addresses for the DNS servers can be found on [sslip.io](https://sslip.io), how to configure them depends on the system used to run the tests.

## Version Compatibility

| CacheTool | PHP
| - | -
| `9.x` | `>=8.1`
| `8.x` | `>=8.0`
| `7.x` | `>=7.3`
| `6.x` | `>=7.3`
| `5.x` | `>=7.2`
| `4.x` | `>=7.1`
| `3.x` | `>=5.5.9`
| `2.x` | `>=5.5.9`
| `1.x` | `>=5.3.3`

## License

CacheTool is licensed under the MIT License - see the [LICENSE](LICENSE) for details
