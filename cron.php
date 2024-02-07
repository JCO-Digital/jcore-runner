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
		$hook = RunnerTable::get_hook_name( $key );
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
		unschedule_action( RunnerTable::get_hook_name( $key ) );
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

	$data = array(
		'script' => $script,
		'page'   => 1,
	);

	// Create log file handle.
	$log = new File( gmdate( 'Y-m-d' ) . '-cron-' . $script, 'logs', 'log' );
	// Capture output from function by starting output buffer.
	ob_start();
	// Execute function, passing the json body to it.
	$return = call_user_func( $callback, new Arguments( $data ) );
	// Store output in variable, and discard and end the buffer.
	$output = ob_get_clean();
	// Write output to log file.
	$log->append_file_data( $output );

	if ( ! $return instanceof \Jcore\Runner\Arguments || ! $return->check_status() ) {
		// Fail.
		$log->append_file_data( 'Script failed' );
	}
}
