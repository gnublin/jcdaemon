CONTENTS OF THIS FILE
---------------------
 * About jcdaemon
 * Requirement
 * Phpredis 
 * Configuration

ABOUT BSI2R
-----------

Jcdaemon is an open source software
distributed with the Gnu GPL v2.

Jcdaemon is a daemon to get all bike stations informations
from the JCDecaux API to a redis-server instance.


Legal informations about jcdaemon:
 * Know your rights when using jcdaemon:
   See LICENSE.txt in the same directory as this document.

REQUIREMENT
-----------

This daemon is write with 2 langages. Init script is write in
bash which manage the php daemon script. Application using
require some softwares on your server :

* bash
* php5-cli 
* phpredis 
* php-services-json 
* redis-server

PHPREDIS
--------
I would like to advise you for the phpredis installation.
In fact, Redis client php module is not support by some 
Linux distribution. Yous should to install this module 
manually on your server.

Installation requirement : 
* redis-server 
* php5-dev 
* build-essential
* xsltproc
* git

Now, you should clone the git project from repository
with this command line :
* git clone git@github.com:nicolasff/phpredis.git

Compile your phpredis extension :
* cd phpredis/
* phpize
* ./configure
* make
* cp modules/redis.so `php-config --extension-dir`
* cp rpm/redis.ini /etc/php5/conf.d/ # 
* restart your webserver 


CONFIGURATION
-------------

