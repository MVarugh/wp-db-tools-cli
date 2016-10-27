<?php # -*- coding: utf-8 -*-

namespace WpDbToolsCli\WpCliCommand;

use wpdb;

/**
 * Provide tools for DB and data management
 *
 * @package WpDbToolsCli\WpCliCommand
 */
class DbTool {

	/**
	 * @var wpdb
	 */
	private $db;

	/**
	 * Constructor. Sets up the properties.
	 */
	public function __construct() {

		$this->db = $GLOBALS['wpdb'];
	}

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
			$this->db->posts,
			$post_types
		);

		$result = $this->db->get_col( $query );
		if ( empty( $result ) || ! is_array( $result ) ) {
			return;
		}
		echo implode( PHP_EOL, $result ) . PHP_EOL;
	}

	/**
	 * Lists the IDs of all meta entries without existing object.
	 *
	 * ## OPTIONS
	 *
	 * [--type=<type>]
	 * : Object type (possible ones are "comment", "post", "term" and "user"). Defaults to "post".
	 *
	 * [--delete]
	 * : Delete all found entries.
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function orphan_meta( array $args, array $assoc_args ) {

		if ( empty( $assoc_args['type'] ) ) {
			$type = 'post';
		} elseif ( in_array( [ 'comment', 'post', 'term', 'user' ], $assoc_args['type'], true ) ) {
			$type = $assoc_args['type'];
		} else {
			return;
		}

		$query_values = $this->get_orphan_meta_query_values( $type );
		if ( ! $query_values ) {
			return;
		}

		$query = vsprintf(
			'SELECT `%2$s` FROM `%1$s` WHERE `%3$s` NOT IN ( SELECT `%5$s` FROM `%4$s` )',
			$query_values
		);

		$results = $this->db->get_col( $query );
		if ( ! $results ) {
			echo 'No entries found.' . PHP_EOL;

			return;
		}

		echo implode( PHP_EOL, $results ) . PHP_EOL;

		if ( array_key_exists( 'delete', $assoc_args ) ) {
			$query = vsprintf(
				'DELETE FROM `%1$s` WHERE `%2$s` IN ( ' . implode( ',', $results ) . ' )',
				$query_values
			);

			$this->db->query( $query );
		}
	}

	/**
	 * Returns the values needed by orphan_meta() for the given type.
	 *
	 * @param string $type Object type.
	 *
	 * @return array Values needed by orphan_meta().
	 */
	private function get_orphan_meta_query_values( $type ) {

		switch ( $type ) {
			case 'comment':
				return [
					$this->db->commentmeta,
					'meta_id',
					'comment_id',
					$this->db->comments,
					'comment_ID',
				];

			case 'post':
				return [
					$this->db->postmeta,
					'meta_id',
					'post_id',
					$this->db->posts,
					'ID',
				];

			case 'term':
				return [
					$this->db->termmeta,
					'meta_id',
					'term_id',
					$this->db->terms,
					'term_id',
				];

			case 'user':
				return [
					$this->db->usermeta,
					'umeta_id',
					'user_id',
					$this->db->users,
					'ID',
				];
		}

		return [];
	}
}
