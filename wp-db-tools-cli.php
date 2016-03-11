<?php # -*- coding: utf-8 -*-

/**
 * Plugin Name: DB tools for WP-CLI
 * Description: <code>$wp help db-table</code>
 * Plugin URL:
 * Author:      Inpsyde GmbH
 * Author URL:  http://inpsyde.com/
 * Version:     1.0.0-alhpa
 * License:     MIT
 * Network:     true
 */

namespace WpDbToolsCli;

use
	WP_CLI;

add_action( 'wp_loaded', function() {

	$autoload = __DIR__ . '/vendor/autoload.php';
	if ( file_exists( $autoload ) )
		require_once $autoload;

	if ( is_wp_cli() )
		WP_CLI::add_command( 'db-table', WpCliCommand\DbTable::class );
} );

/**
 * Checks if WP-CLI is present
 *
 * @return bool
 */
function is_wp_cli() {

	return
		defined( 'WP_CLI' )
		&& WP_CLI
		&& class_exists( 'WP_CLI' )
		&& class_exists( 'WP_CLI_Command' );
}

