<?php
/**
 * Bootstrap file for Cyr-To-Lat phpunit tests.
 *
 * @package cyr-to-lat
 */

use tad\FunctionMocker\FunctionMocker;

/**
 * Plugin test dir.
 */
define( 'PLUGIN_TESTS_DIR', __DIR__ );

/**
 * Plugin main file.
 */
define( 'PLUGIN_MAIN_FILE', __DIR__ . '/../../cyr-to-lat.php' );

/**
 * Plugin path.
 */
define( 'PLUGIN_PATH', dirname( PLUGIN_MAIN_FILE ) );

require_once PLUGIN_PATH . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	/**
	 * WordPress ABSPATH.
	 */
	define( 'ABSPATH', PLUGIN_PATH . '/../../../' );
}

/**
 * Plugin version.
 */
define( 'CYR_TO_LAT_VERSION', 'test-version' );

/**
 * Path to the plugin dir.
 */
define( 'CYR_TO_LAT_PATH', dirname( PLUGIN_MAIN_FILE ) );

/**
 * Plugin dir url.
 */
define( 'CYR_TO_LAT_URL', 'http://site.org/wp-content/plugins/cyr2lat' );

/**
 * Main plugin file.
 */
define( 'CYR_TO_LAT_FILE', PLUGIN_MAIN_FILE );

/**
 * Plugin prefix.
 */
define( 'CYR_TO_LAT_PREFIX', 'cyr_to_lat' );

/**
 * Post conversion action.
 */
define( 'CYR_TO_LAT_POST_CONVERSION_ACTION', 'post_conversion_action' );

/**
 * Term conversion action.
 */
define( 'CYR_TO_LAT_TERM_CONVERSION_ACTION', 'term_conversion_action' );

/**
 * Minimum required php version.
 */
define( 'CYR_TO_LAT_MINIMUM_PHP_REQUIRED_VERSION', '5.6' );

/**
 * Minimum required max_input_vars value.
 */
define( 'CYR_TO_LAT_REQUIRED_MAX_INPUT_VARS', 1000 );

FunctionMocker::init(
	[
		'blacklist'             => [
			realpath( CYR_TO_LAT_PATH ),
		],
		'whitelist'             => [
			realpath( CYR_TO_LAT_PATH . '/classes' ),
		],
		'redefinable-internals' => [ 'function_exists', 'ini_get', 'mb_strtolower', 'phpversion', 'realpath', 'time' ],
	]
);

\WP_Mock::bootstrap();
