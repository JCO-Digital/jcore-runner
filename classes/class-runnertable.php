<?php
/**
 * Bootstrap Class
 *
 * This class is responsible for the course list.
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

if ( ! class_exists( 'WP_List_Table' ) && file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
use WP_List_Table;

/**
 * Class RunnerTable
 *
 * The RunnerTable class displays a list of the runners with options.
 */
class RunnerTable extends WP_List_Table {

	/**
	 * Initializes the object by calling the constructor of the parent class with the necessary parameters.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'runner',
				'plural'   => 'runners',

				'ajax'     => false,
			)
		);
	}

	/**
	 * Retrieves the list of columns.
	 *
	 * @return array An associative array containing the list of columns.
	 */
	public function get_columns(): array {
		return array(
			'name'   => __( 'Name', 'jcore-runner' ),
			'cron'   => __( 'Cron', 'jcore-runner' ),
			'log'    => __( 'Logs', 'jcore-runner' ),
			'export' => __( 'Exports', 'jcore-runner' ),
		);
	}

	/**
	 * Returns the value for the specified column in the given item.
	 *
	 * @param mixed  $item The item to get the column value from.
	 * @param string $column_name The name of the column to retrieve the value for.
	 *
	 * @return mixed The value of the specified column in the item. If the column name is 'id', 'name',
	 *               'product_ids', or 'product', the corresponding value from the item will be returned.
	 *               Otherwise, an empty string will be returned.
	 */
	public function column_default( $item, $column_name ) {
		$content = '';
		$actions = array();
		if ( 'name' === $column_name ) {
			// Name Column.
			$content = sprintf(
				'<a href="%s">%s</a>',
				add_query_arg(
					array(
						'page'   => 'jcore-runner',
						'script' => esc_attr( $item['id'] ),
					),
					admin_url( 'tools.php' )
				),
				$item['title']
			);
		} elseif ( 'cron' === $column_name ) {
			// Cron Column.
			$next = wp_next_scheduled( get_hook_name( $item['id'] ) );
			if ( false === $next ) {
				$schedules = wp_get_schedules();
				$schedules = array_filter(
					$schedules,
					static function ( $schedule ) {
						return true === $schedule['is_jcore_runner'];
					}
				);
				$content   = __( 'Not scheduled', 'jcore-runner' );
				$actions   = array_map(
					static function ( $key ) use ( $schedules, $item ) {
						$schedule = $schedules[ $key ];
						return sprintf(
							'<a href="%s">%s</a>',
							add_query_arg(
								array(
									'page'     => 'jcore-runner',
									'schedule' => esc_attr( $item['id'] ),
									'action'   => $key,
								),
								admin_url( 'admin.php' )
							),
							$schedule['display']
						);
					},
					array_keys( $schedules ),
				);
			} else {
				$time = wp_date( get_option( 'time_format' ), $next );
				if ( wp_date( get_option( 'date_format' ), $next ) !== wp_date( get_option( 'date_format' ) ) ) {
					$time .= ' ' . wp_date( get_option( 'date_format' ), $next );
				}
				// translators: Time and possible date.
				$content = sprintf( __( 'Next Scheduled Run: %s', 'jcore-runner' ), $time );
				if ( wp_next_scheduled( get_hook_name( $item['id'], true ) ) ) {
					$content .= ' (' . __( 'In progress', 'jcore-runner' ) . ')';
				}

				$actions = array(
					'unschedule' => sprintf(
						'<a href="%s">%s</a>',
						add_query_arg(
							array(
								'page'     => 'jcore-runner',
								'schedule' => esc_attr( $item['id'] ),
								'action'   => 'unschedule',
							),
							admin_url( 'admin.php' )
						),
						__( 'Unschedule', 'jcore-runner' )
					),
				);
			}
		} elseif ( 'log' === $column_name ) {
			foreach ( File::get_files( 'logs', $item['id'] . '.log', 2 ) as $file ) {
				$content .= sprintf(
					'<a href="%s">%s</a><br/>',
					$file['url'],
					$file['name'],
				);
			}
		} elseif ( 'export' === $column_name ) {
			foreach ( File::get_files( 'export', $item['id'] . '-', 2 ) as $file ) {
				$content .= sprintf(
					'<a href="%s">%s</a><br/>',
					$file['url'],
					$file['name'],
				);
			}
		}

		return $content . $this->row_actions( $actions );
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		$columns               = $this->get_columns();
		$hidden                = array();
		$this->_column_headers = array( $columns, $hidden, $this->get_sortable_columns() );

		$scripts = array();
		foreach ( \apply_filters( 'jcore_runner_functions', array() ) as $key => $item ) {
			$item['id'] = $key;
			$scripts[]  = $item;
		}

		$per_page     = 10;
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$this->set_pagination_args(
			array(
				'total_items' => count( $scripts ),
				'per_page'    => $per_page,
			)
		);
		$scripts     = array_slice( $scripts, $offset, $per_page );
		$this->items = $scripts;
	}
}
