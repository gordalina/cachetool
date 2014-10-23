CacheTool - Manage cache in the CLI
===================================

CacheTool allows you to work with `apc` and `opcache` through the cli.
It will connect to a fastcgi server (like php-fpm) and operate it's cache.

Why is this useful?
- Maybe you want to clear the bytecode cache without reloading php-fpm or using a web endpoint
- Maybe you want to have a cron which deals with cache invalidation
- Maybe you want to see some statistics right from the console
- And many more...

Installation
------------

```sh
$ curl -sO http://gordalina.github.io/cachetool/downloads/cachetool.phar
$ chmod +x cachetool.phar
```

Usage (as an application)
-------------------------

1. You can connect to a automatically guessed fastcgi server (if `/var/run/php5-fpm.sock` is a file or `127.0.0.1:9000`)

```sh
$ php cachetool.phar apc:cache:info --fcgi
```

2. You can connect to a fastcgi server through ip

```sh
$ php cachetool.phar apc:cache:info --fcgi=127.0.0.1:9000
```

3. Or by socket

```sh
$ php cachetool.phar opcache:status --fcgi=/var/run/php5-fpm.sock
```

4. Or to the cli

```sh
$ php cachetool.phar opcache:status --cli
```

5. You have some useful commands that you can you

```sh
apc
  apc:bin:dump             Get a binary dump of files and user variables
  apc:bin:load             Load a binary dump into the APC file and user variables
  apc:cache:clear          Clears APC cache (user, system or all)
  apc:cache:info           Shows APC user & system cache information
  apc:cache:info:file      Shows APC file cache information
  apc:key:delete           Deletes an APC key
  apc:key:exists           Checks if an APC key exists
  apc:key:fetch            Shows the content of an APC key
  apc:key:store            Store an APC key with given value
  apc:sma:info             Show APC shared memory allocation information
opcache
  opcache:configuration    Get configuration information about the cache
  opcache:reset            Resets the contents of the opcode cache
  opcache:status           Show summary information about the opcode cache
  opcache:status:scripts   Show scripts in the opcode cache
```

Configuration File
------------------

You can have a configuration file with the adapter configuration, allowing you to
call CacheTool withouth `--fcgi` or `--cli` option.

The file must be named `.cachetool.yml`. CacheTool will look for this file on the
current directory and in any parent directory until it finds one.
If the paths above fail it will try to load `/etc/cachetool.yml` configuration file.

An example of what this file might look like is:

1. Will connect to fastcgi at 127.0.0.1:9000

```yml
adapter: fastcgi
fastcgi: 127.0.0.1:9000
```

2. Will connect to cli (disregarding fastcgi configuration)

```yml
adapter: cli
fastcgi: /var/run/php5-fpm.sock
```

Usage (as a library)
--------------------

1. Add it as a dependency

```sh
$ composer require gordalina/cachetool=~1.0
```

2. Create instance

```php
use CacheTool\Adapter\FastCGI;
use CacheTool\CacheTool;

$adapter = new FastCGI('127.0.0.1:9000');
$cache = CacheTool::factory($adapter);
```

3. You can use `apc` and `opcache` functions

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

Requirements
------------

PHP 5.3.3

License
-------

CacheTool is licensed under the MIT License - see the [LICENSE]() for details
