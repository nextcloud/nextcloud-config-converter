# Nextcloud config.sample.php to RST converter

This script creates a RST file from the comments inside of config.sample.php.

## Production setup

The live environment that updates https://docs.nextcloud.com/server/latest/admin_manual/configuration_server/config_sample_php_parameters.html is running on the translation sync server based on this docker image:
https://github.com/nextcloud/docker-ci/blob/master/translations/Dockerfile-Documentation-Sync
So after performing changes in this repository here, a new docker image has to be created in the docker-ci repository.

## Requirements

Install the dependencies with `composer`:

```sh
composer install
```

## How to use

Just call following in your Nextcloud server repo:

```sh
php -f convert.php path/to/config.sample.php path/to/config_sample_php_parameters.rst
```

This will create a file `sample_config.rst` which was generated from `config/config.sample.php`

### Supported feature set

Currently this relies on following

 * all comments need to start with `/**` and end with ` */` - each on their own line
 * add a `@see CONFIG_INDEX` to copy a previously described config option also to this line
 * everything between the ` */` and the next `/**` will be treated as the config option

### Options to set

You can set following options:

The tag which invokes to copy a config description to the current position

```php
$COPY_TAG = 'see';
```

The file which should be parsed

```php
$CONFIG_SAMPLE_FILE = 'config/config.sample.php';
```

The file to put output in

```php
$OUTPUT_FILE = 'sample_config.rst';
```
