<?php # -*- coding: utf-8 -*-

namespace WpDbToolsCli\WpCliCommand;

/**
 * Provide tools for DB and data management
 *
 * @package WpDbToolsCli\WpCliCommand
 */
class DbTool {

	/**
	 * Find orphaned posts
	 *
	 * ## Options
	 *
	 * [--post_tpye] Comma separated list of post types. Default is to lookup every post type
	 *
	 * @synopsis [--post_type=<POST_TYPE>]
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function post_orphans( array $args, array $assoc_args ) {

		$post_types = isset( $assoc_args[ 'post_type' ] )
			? trim( $assoc_args[ 'post_type' ] )
			: '';
		if ( $post_types ) {
			$post_types = explode( ',', $post_types );
			$post_types = array_map(
				function ( $pt ) {
					$pt = trim( $pt );
					$pt = esc_sql( $pt );
					$pt = "'{$pt}'";
					return $pt;
				},
				$post_types
			);
			$post_types = sprintf( 'AND posts.post_type IN ( %s )', implode(',', $post_types ) );
		}

		$query = <<<'SQL'
SELECT ID FROM %1$s AS posts
WHERE
	1=1
	%2$s
	AND NOT EXISTS (
		SELECT ID FROM %1$s AS parents
		WHERE 
			parents.ID = posts.post_parent
	)
SQL;

		$query = sprintf(
			$query,
			$GLOBALS[ 'wpdb' ]->posts,
			$post_types
		);

		$result = $GLOBALS[ 'wpdb' ]->get_col( $query );
		if ( empty( $result ) || ! is_array( $result ) ) {
			return;
		}
		echo implode( PHP_EOL, $result ) . PHP_EOL;
	}
}