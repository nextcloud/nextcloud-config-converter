#!/bin/sh

# verbose and exit on error
set -xe

# import GPG keys
gpg --import /gpg/nextcloud-bot.public.asc
gpg --allow-secret-key-import --import /gpg/nextcloud-bot.asc
gpg --list-keys

currentDir=$(pwd)

if [[ -d /tmp/nextcloud-documentation ]]
then
	rm -rf /tmp/nextcloud-documentation
fi

# fetch documentation repo
git clone git@github.com:nextcloud/documentation.git /tmp/nextcloud-documentation
cd /tmp/nextcloud-documentation

for branch in stable24 stable25 stable26 master
do
	git checkout  $branch
	cd $currentDir

	# download current version of config.sample.php
	curl -o /tmp/config.sample.php https://raw.githubusercontent.com/nextcloud/server/$branch/config/config.sample.php

	# use that to generate the documentation
	php convert.php --input-file=/tmp/config.sample.php --output-file=/tmp/nextcloud-documentation/admin_manual/configuration_server/config_sample_php_parameters.rst

	cd /tmp/nextcloud-documentation
	# invokes an output if something has changed
	status=$(git status -s)

	if [ -n "$status" ]; then
		echo "Push $branch"
		git commit -am 'generate documentation from config.sample.php'
		git push
	fi

	# cleanup
	rm -rf /tmp/config.sample.php
done
