<?php
/*
Plugin Name:  Spocket ‑ US & EU Dropshipping
Description:  Spocket - Dropshipping US/European Products
Version: 1.8.0
Author:       Spocket
Author URI:   https://www.spocket.co
Text Domain:  spocket

Woo: 4755824:28bedd807fee898904f7ede3cbef6b24
*/

if (!defined('WPINC')) {
	die;
}

if (file_exists(\dirname(__FILE__) . '/vendor/autoload.php')) {
	include_once dirname(__FILE__) . '/vendor/autoload.php';
}

// .env is a local-development file only and is never shipped to WordPress.org.
// createUnsafeImmutable (phpdotenv v5) is required, not createImmutable: the
// plugin reads its settings with getenv(), and only the "unsafe" variant also
// calls putenv(); createImmutable fills $_ENV/$_SERVER and every getenv() misses.
// The method_exists guard keeps a missing or older bundled phpdotenv from
// taking the whole site down with a fatal error.
if (file_exists(dirname(__FILE__) . '/.env') === true
	&& method_exists('Dotenv\Dotenv', 'createUnsafeImmutable')
) {
	Dotenv\Dotenv::createUnsafeImmutable(__DIR__)->load();
}

if (! defined('SPOCKET_MAKE_PATHS_RELATIVE_FILE')) {
	define('SPOCKET_MAKE_PATHS_RELATIVE_FILE', __FILE__);
}

\add_action(
	'plugins_loaded',
	function () {
		$plugin = new \Spocket\Plugin();
		$plugin->run();
	}
);
