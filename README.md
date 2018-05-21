CacheTool - Manage cache in the CLI
===================================

[![Build Status](https://img.shields.io/travis/gordalina/cachetool.svg)](https://travis-ci.org/gordalina/cachetool)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/gordalina/cachetool.svg)](https://scrutinizer-ci.com/g/gordalina/cachetool/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/gordalina/cachetool.svg)](https://scrutinizer-ci.com/g/gordalina/cachetool/?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/595c9feb-3f4d-473a-a575-81c7e97eb672.svg)](https://insight.sensiolabs.com/projects/595c9feb-3f4d-473a-a575-81c7e97eb672)
[![Codacy Badge](https://img.shields.io/codacy/2d4176f2526d4251a51b691249c4d3e1.svg)](https://www.codacy.com/app/gordalina/cachetool/dashboard)

CacheTool allows you to work with `apc`, `opcache`, and the file status cache through the cli.
It will connect to a fastcgi server (like php-fpm) and operate it's cache.

Why is this useful?
- Maybe you want to clear the bytecode cache without reloading php-fpm or using a web endpoint
- Maybe you want to have a cron which deals with cache invalidation
- Maybe you want to see some statistics right from the console
- And many more...

Note that, unlike APC and Opcache, the file status cache is per-process rather than stored in shared memory. This means that running `stat:clear` against PHP-FPM will only affect whichever FPM worker responds to the request, not the whole pool. [Julien Pauli has written a post](http://blog.jpauli.tech/2014/06/30/realpath-cache.html) with more details on how the file status cache operates.

CacheTool works with PHP 5.5.9+, if you want to use with PHP 5.3.3, you need to use CacheTool 1.x

Installation
------------

```sh
$ curl -sO http://gordalina.github.io/cachetool/downloads/cachetool.phar
$ chmod +x cachetool.phar
```

Usage (as an application)
-------------------------

CacheTool requires an adapter to connect to, it can be `cli`, `fcgi`, and `web`.
The `fcgi` adapter is the most common, as it connects directly to php-fpm.

You can pass an IP address or a unix socket to the `--fcgi` adapter, or leave it blank and CacheTool will try to find the php-fpm socket for you. If it can't find it, it will default to `127.0.0.1:9000`.

  * You can let CacheTool find the unix socket for you, or default to IP.

```sh
$ php cachetool.phar apc:cache:info --fcgi
```

  * You can connect to a fastcgi server using an IP address

```sh
$ php cachetool.phar apc:cache:info --fcgi=127.0.0.1:9000
```

  * You can connect to a fastcgi server using a unix socket

```sh
$ php cachetool.phar opcache:status --fcgi=/var/run/php5-fpm.sock
```

  * To connect to a chrooted fastcgi server you need to set `--fcgi-chroot` and `--tmp-dir` parameters

```sh
$ php cachetool.phar opcache:status --fcgi=/var/run/php5-fpm.sock --fcgi-chroot=/path/to/chroot --tmp-dir=/path/to/chroot/tmp
```

  * Using the CLI

```sh
$ php cachetool.phar opcache:status --cli
```

  * Using an HTTP interface

```sh
$ php cachetool.phar opcache:status --web --web-path=/path/to/your/document/root --web-url=http://url-to-your-document.root
```

You have some useful commands that you can use

```sh
 apc
  apc:bin:dump                Get a binary dump of files and user variables
  apc:bin:load                Load a binary dump into the APC file and user variables
  apc:cache:clear             Clears APC cache (user, system or all)
  apc:cache:info              Shows APC user & system cache information
  apc:cache:info:file         Shows APC file cache information
  apc:key:delete              Deletes an APC key
  apc:key:exists              Checks if an APC key exists
  apc:key:fetch               Shows the content of an APC key
  apc:key:store               Store an APC key with given value
  apc:regexp:delete           Deletes all APC key matching a regexp
  apc:sma:info                Show APC shared memory allocation information
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
  opcache:compile:scripts     Compile scripts from path to the opcode cache
  opcache:configuration       Get configuration information about the cache
  opcache:invalidate:scripts  Remove scripts from the opcode cache
  opcache:reset               Resets the contents of the opcode cache
  opcache:status              Show summary information about the opcode cache
  opcache:status:scripts      Show scripts in the opcode cache
 stat
  stat:clear                  Clears the file status cache, including the realpath cache
  stat:realpath_get           Show summary information of realpath cache entries
  stat:realpath_size          Display size of realpath cache
```

Configuration File
------------------

You can have a configuration file with the adapter configuration, allowing you to
call CacheTool without `--fcgi`, `--cli`, or `--web` option.

The file must be named `.cachetool.yml`. CacheTool will look for this file on the
current directory and in any parent directory until it finds one.
If the paths above fail it will try to load `/etc/cachetool.yml` configuration file.

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

You can define the supported extensions in the config file. By default, `apc`, `apcu`, and
`opcache` are enabled. To disable `apc`, add this to your config file:

```yml
extensions: [apcu, opcache]
```

Usage (as a library)
--------------------

Add it as a dependency

```sh
$ composer require gordalina/cachetool
```

If you want to use it in a Symfony 2.x project, require the `1.x` version

```sh
$ composer require gordalina/cachetool:~1.0
```

Create instance

```php
use CacheTool\Adapter\FastCGI;
use CacheTool\CacheTool;

$adapter = new FastCGI('127.0.0.1:9000', $tempDir = '/tmp');
$cache = CacheTool::factory($adapter);
```

You can use `apc` and `opcache` functions

```php
$cache->apc_clear_cache('both');
$cache->opcache_reset();
```

Proxies
-------

CacheTool depends on `Proxies` to provide functionality, by default when creating a CacheTool instance from the factory
all proxies are enabled [`ApcProxy`](https://github.com/gordalina/cachetool/blob/master/src/CacheTool/Proxy/ApcProxy.php), [`OpcacheProxy`](https://github.com/gordalina/cachetool/blob/master/src/CacheTool/Proxy/OpcacheProxy.php) and [`PhpProxy`](https://github.com/gordalina/cachetool/blob/master/src/CacheTool/Proxy/PhpProxy.php), you can customize it or extend to your will like the example below:

```php
use CacheTool\Adapter\FastCGI;
use CacheTool\CacheTool;
use CacheTool\Proxy;

$adapter = new FastCGI('/var/run/php5-fpm.sock');
$cache = new CacheTool();
$cache->setAdapter($adapter);
$cache->addProxy(new Proxy\ApcProxy());
$cache->addProxy(new Proxy\PhpProxy());
```

Updating CacheTool
------------------

Running `php cachetool.phar self-update` will update a phar install with the latest version.

Testing
-------

After running `composer install`, run `./vendor/bin/phpunit`

Troubleshooting
---------------

> [RuntimeException]
> Error: Unable to open primary script: /dev/shm/cachetool-584743c678dbb.php (No such file or directory)
> Status: 404 Not Found
> Content-type: text/html; charset=UTF-8
> No input file specified.

This means that cachetool could not write to `/dev/shm` provide a directory that cachetool can write to through `php cachetool.phar --tmp-dir=/writable/dir` or configuration.

License
-------

CacheTool is licensed under the MIT License - see the [LICENSE](LICENSE) for details
