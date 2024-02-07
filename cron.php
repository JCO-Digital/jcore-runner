<?php
/**
 * Cron functions.
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

add_filter( 'cron_schedules', '\Jcore\Runner\add_cron_interval' );
add_action( 'init', '\Jcore\Runner\register_cron_actions' );

/**
 * Add actions for the cron hooks.
 *
 * @return void
 */
function register_cron_actions() {
	foreach ( \apply_filters( 'jcore_runner_functions', array() ) as $key => $value ) {
		$hook = get_hook_name( $key );
		add_action(
			$hook,
			function () use ( $key ) {
				cron_runner( $key );
			}
		);
	}
}

/**
 * Unregister all cron jobs when deactivating plugin.
 *
 * @return void
 */
function cron_deactivate() {
	foreach ( \apply_filters( 'jcore_runner_functions', array() ) as $key => $value ) {
		unschedule_action( get_hook_name( $key ) );
	}
}

/**
 * Unschedule all actions for a specific hook.
 *
 * @param mixed $hook Cron hook name.
 * @return void
 */
function unschedule_action( $hook ) {
		// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
	while ( $timestamp = wp_next_scheduled( $hook ) ) {
		// Unschedule all hooks.
		wp_unschedule_event( $timestamp, $hook );
	}
}

/**
 * Schedule the cron job.
 *
 * @param string $script Name of script to schedule.
 * @param string $action The action or interval to use.
 * @return void
 */
function schedule_action( string $script, string $action ) {
	$hook = get_hook_name( $script );
	unschedule_action( $hook );
	if ( ! wp_next_scheduled( $hook ) ) {
		wp_schedule_event( time(), 'every_minute', $hook );
	}
	$interval = match ( $action ) {
		default => 3600,
		'daily' => 86400,
		'weekly' => 604800,
	};

	$data_file        = new File( $script, 'data' );
	$data             = $data_file->read_file_data();
	$data['next']     = time() + $interval;
	$data['interval'] = $interval;
	$data_file->write_file_data( $data );
}

/**
 * Add custom cron interval.
 *
 * @param mixed $schedules Cron intervals.
 * @return mixed
 */
function add_cron_interval( $schedules ) {
	$schedules['every_minute'] = array(
		'interval' => 60,
		'display'  => __( 'Every Minute' ),
	);
	return $schedules;
}

/**
 * Cron handler from the actual running of the scripts.
 *
 * @param string $script The script to run.
 * @return void
 */
function cron_runner( string $script ) {
	$functions = \apply_filters( 'jcore_runner_functions', array() );

	if ( empty( $functions[ $script ] ) ) {
		return;
	}

	$callback = $functions[ $script ]['callback'] ?? false;
	if ( ! $callback || ! is_callable( $callback ) ) {
		return;
	}

	$data_file = new File( $script, 'data' );
	$data      = $data_file->read_file_data();

	while ( $data['next'] < time() ) {
		$data['next'] += $data['interval'];
		// Start cron run.
		$data['args']  = array(
			'script' => $script,
			'page'   => 1,
		);
		$data['clear'] = true;
	}

	if ( ! empty( $data['args'] ) ) {
		// Create log file handle.
		$log = new File( gmdate( 'Y-m-d' ) . '-cron-' . $script, 'logs', 'log' );
		if ( $data['clear'] ) {
			// Add timestamp to first run.
			$log->append_file_data( "\n----- " . gmdate( 'H:i:s' ) . " -----\n" );
			$data['clear'] = false;
		}

		// Capture output from function by starting output buffer.
		ob_start();
		// Execute function, passing the json body to it.
		$return = call_user_func( $callback, new Arguments( $data['args'] ) );
		// Store output in variable, and discard and end the buffer.
		$output = ob_get_clean();
		// Write output to log file.
		$log->append_file_data( $output );

		if ( ! $return instanceof \Jcore\Runner\Arguments || ! $return->check_status() ) {
			// Fail.
			$log->append_file_data( 'Script failed' );
			$data['args'] = array();
		} else {
			$data['args']['exportFile'] = $return->write_export();
			if ( empty( $return->next_page ) ) {
				$data['args'] = array();
			} else {
				$data['args']['page'] = $return->next_page;
				$data['args']['data'] = $return->data;
			}
		}
	}
	$data_file->write_file_data( $data );
}
