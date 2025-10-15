<?php

/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */

$CONFIG = [
	/**
	 * Default Parameters
	 *
	 * These parameters are configured by the Nextcloud installer, and are required
	 * for your Nextcloud server to operate.
	 */

	/**
	 * Maintenance
	 *
	 * These options are for halting user activity when you are performing server
	 * maintenance.
	 */

	/**
	 * Enable maintenance mode to disable Nextcloud
	 *
	 * If you want to prevent users from logging in to Nextcloud before you start
	 * doing some maintenance work, you need to set the value of the maintenance
	 * parameter to true. Please keep in mind that users who are already logged in
	 * are kicked out of Nextcloud instantly.
	 *
	 * Defaults to ``false``
	 */
	'maintenance' => false,

	/**
	 * Connection details for a Redis Cluster.
	 *
	 * Redis Cluster support requires the PHP module phpredis in version 3.0.0 or
	 * higher.
	 *
	 * Available failover modes:
	 *  - ``\RedisCluster::FAILOVER_NONE`` - only send commands to master nodes (default)
	 *  - ``\RedisCluster::FAILOVER_ERROR`` - failover to slaves for read commands if master is unavailable (recommended)
	 *  - ``\RedisCluster::FAILOVER_DISTRIBUTE`` - randomly distribute read commands across master and slaves
	 *
	 * WARNING: ``\RedisCluster::FAILOVER_DISTRIBUTE`` is a not recommended setting, and we strongly
	 * suggest to not use it if you use Redis for file locking. Due to the way Redis
	 * is synchronized, it could happen that the read for an existing lock is
	 * scheduled to a slave that is not fully synchronized with the connected master
	 * which then causes a FileLocked exception.
	 *
	 * See https://redis.io/topics/cluster-spec for details about the Redis cluster
	 *
	 * Authentication works with phpredis version 4.2.1+. See
	 * https://github.com/phpredis/phpredis/commit/c5994f2a42b8a348af92d3acb4edff1328ad8ce1
	 */
	'redis.cluster' => [],

	/**
	 * Federated Cloud Sharing
	 */

	/**
	 * Allow self-signed certificates for federated shares
	 */
	'sharing.federation.allowSelfSignedCertificates' => false,

	/**
	 * During setup, if requirements are met (see below), this setting is set to true
	 * to enable MySQL to handle 4-byte characters instead of 3-byte characters.
	 *
	 * To convert an existing 3-byte setup to a 4-byte setup, configure the MySQL
	 * parameters as described below and run the migration command:
	 * ``./occ db:convert-mysql-charset``
	 * This config setting will be automatically updated after a successful migration.
	 *
	 * Refer to the documentation for more details.
	 *
	 * MySQL requires specific settings for longer indexes (> 767 bytes), which are
	 * necessary for 4-byte character support::
	 *
	 *     [mysqld]
	 *     innodb_large_prefix=ON
	 *     innodb_file_format=Barracuda
	 *     innodb_file_per_table=ON
	 *
	 * Tables will be created with:
	 *  * character set: ``utf8mb4``
	 *  * collation:     ``utf8mb4_bin``
	 *  * row_format:    ``dynamic``
	 *
	 * See:
	 *  * https://dev.mysql.com/doc/refman/5.7/en/charset-unicode-utf8mb4.html
	 *  * https://dev.mysql.com/doc/refman/5.7/en/innodb-parameters.html#sysvar_innodb_large_prefix
	 *  * https://mariadb.com/kb/en/mariadb/xtradbinnodb-server-system-variables/#innodb_large_prefix
	 *  * http://www.tocker.ca/2013/10/31/benchmarking-innodb-page-compression-performance.html
	 *  * http://mechanics.flite.com/blog/2014/07/29/using-innodb-large-prefix-to-avoid-error-1071/
	 */
	'mysql.utf8mb4' => false,

	/**
	 * List of user agents exempt from SameSite cookie protection due to non-standard
	 * HTTP behavior.
	 *
	 * WARNING: Use only if you understand the implications.
	 *
	 * Defaults to::
	 * - ``/^WebDAVFS/`` (OS X Finder)
	 * - ``/^Microsoft-WebDAV-MiniRedir/`` (Windows WebDAV drive)
	 */
	'csrf.optout' => [],

	/**
	 * Include all query parameters in the query log when set to `yes`.
	 *
	 * Requires `query_log_file` to be set.
	 * WARNING: This may log sensitive data in plain text.
	 */
	'query_log_file_parameters' => '',

	/**
	 * Log all Redis requests to a file.
	 *
	 * WARNING: This significantly reduces server performance and is intended only
	 * for debugging or profiling Redis interactions. Sensitive data may be logged in
	 * plain text.
	 */
	'redis_log_file' => '',

	/**
	 * Override default scopes for account data. Valid properties and scopes are
	 * defined in ``OCP\Accounts\IAccountManager``. Values are merged with defaults
	 * from ``OC\Accounts\AccountManager``.
	 *
	 * Example: Set phone property to private scope:
	 * ``[\OCP\Accounts\IAccountManager::PROPERTY_PHONE => \OCP\Accounts\IAccountManager::SCOPE_PRIVATE]``
	 */
	'account_manager.default_property_scope' => [],

	/**
	 * Directories where Nextcloud searches for external binaries (e.g., LibreOffice,
	 * sendmail, ffmpeg).
	 *
	 * Defaults to:
	 *
	 * - /usr/local/sbin
	 * - /usr/local/bin
	 * - /usr/sbin
	 * - /usr/bin
	 * - /sbin
	 * - /bin
	 * - /opt/bin
	 */
	'binary_search_paths' => [],
];
