<?php # -*- coding: utf-8 -*-

namespace WpDbToolsCli\WpCliCommand;

use
	WpDbTypes\Type,
	WpDbTools\Action,
	WpDbTools\Db,
	WP_CLI,
	WP_CLI_Command;

/**
 * Provides commands to maintain database tables
 *
 * @package WpDbToolsCli\WpCliCommand
 */
class DbTable extends WP_CLI_Command {

	/**
	 * Copy a table
	 *
	 * ## Options
	 *
	 * <TABLE>
	 * : Table to copy
	 *
	 * <NEW_TABLE>
	 * : Table name to copy to
	 *
	 * [--content]
	 * : whether to copy also content
	 *
	 * @synopsis <TABLE> <NEW_TABLE> [--content]
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function copy( array $args, array $assoc_args ) {

		$with_content = FALSE;
		if ( isset( $assoc_args[ 'content' ] ) ) {
			$with_content = TRUE;
		}
		$table = isset( $args[ 0 ] )
			? new Type\NamedTable( $args[ 0 ] )
			: NULL;

		$new_table = isset( $args[ 1 ] )
			? new Type\NamedTable( $args[ 1 ] )
			: NULL;

		if ( ! $table || ! $new_table ) {
			WP_CLI::error( 'Missing argument. See `wp help db-table copy`' );
		}

		$adapter = new Db\WpDbAdapter( $GLOBALS[ 'wpdb' ] );
		$lookup  = new Action\MySqlTableLookup( $adapter );
		$copier  = new Action\MySqlTableCopier( $adapter );

		if ( $lookup->table_exists( $new_table ) ) {
			WP_CLI::error( "Table {$new_table} already exists" );
		}
		if ( ! $lookup->table_exists( $table ) ) {
			WP_CLI::error( "Table {$table} does not exist" );
		}

		if ( $with_content ) {
			$copier->copy( $table, $new_table );
		} else {
			$copier->copy_structure( $table, $new_table );
		}

		if ( $GLOBALS[ 'wpdb' ]->last_error ) {
			WP_CLI::error( "Database Error:", FALSE );
			WP_CLI::line( $GLOBALS[ 'wpdb' ]->last_error );
			exit( 1 );
		}
		if ( $with_content )
			WP_CLI::success( "Table {$new_table} created with content" );
		else
			WP_CLI::success( "Empty table {$new_table} created" );
	}

	/**
	 * Deletes a database table
	 *
	 * Not implemented yet
	 *
	 * ## Options
	 *
	 * <TABLE>
	 *  :table name to delete
	 *
	 * @synopsis <TABLE> --no_confirm
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function delete( array $args, array $assoc_args ) {

		WP_CLI::line( 'Command not implemented yet. Aborting!' );
		exit( 1 );
	}

	/**
	 * Creates a database table
	 *
	 * not implemented yet
	 *
	 * @synopsis <TABLE> [--file=path/to/schema.json]
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function create( array $args, array $assoc_args ) {

		WP_CLI::line( 'Command not implemented yet. Aborting!' );
		exit( 1 );
	}
}