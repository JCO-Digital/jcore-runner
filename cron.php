<?php
/**
 * Cron functions.
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

add_filter( 'cron_schedules', '\Jcore\Runner\add_cron_interval', 20 );
add_action( 'init', '\Jcore\Runner\register_cron_actions' );

/**
 * Add actions for the cron hooks.
 *
 * @return void
 */
function register_cron_actions() {
	foreach ( \apply_filters( 'jcore_runner_functions', array() ) as $key => $value ) {
		add_action(
			get_hook_name( $key, true ),
			function () use ( $key ) {
				cron_runner( $key );
			}
		);
		add_action(
			get_hook_name( $key, false ),
			function () use ( $key ) {
				cron_manager( $key );
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
		unschedule_action( get_hook_name( $key, false ) );
		unschedule_action( get_hook_name( $key, true ) );
	}
}

/**
 * Unschedule all actions for a specific hook.
 *
 * @param string $hook Cron hook name.
 * @param mixed  $arguments Optional arguments to clean up old hooks. This can be removed later.
 * @return void
 */
function unschedule_action( string $hook, $arguments = null ) {
	while ( $timestamp = wp_next_scheduled( $hook ) ) {
		// Unschedule all hooks.
		wp_unschedule_event( $timestamp, $hook );
	}

	// Cleanup hooks created by bug. This can be removed later.
	if ( ! empty( $arguments ) ) {
		while ( $timestamp = wp_next_scheduled( $hook, $arguments ) ) {
			// Unschedule all hooks.
			wp_unschedule_event( $timestamp, $hook, $arguments );
		}
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
	if ( ! in_array( $action, array( 'hourly', 'daily', 'weekly', 'unschedule' ), true ) ) {
		return;
	}
	$hook = get_hook_name( $script );
	unschedule_action( $hook, array( $script ) );
	if ( ! wp_next_scheduled( $hook ) && 'unschedule' !== $action ) {
		wp_schedule_event( time(), $action, $hook );
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
	$schedules['hourly']       = array(
		'interval' => 3600,
		'display'  => __( 'Hourly' ),
	);
	$schedules['daily']        = array(
		'interval' => 86400,
		'display'  => __( 'Daily' ),
	);
	$schedules['weekly']       = array(
		'interval' => 604800,
		'display'  => __( 'Weekly' ),
	);
	return $schedules;
}


/**
 * Cron handler triggered by WP that starts execution of the script.
 *
 * @param string $script The script to run.
 * @return void
 */
function cron_manager( string $script ) {
	$options = is_valid_script( $script );
	if ( false === $options ) {
		return;
	}

	// Start cron run.
	set_setting(
		$script,
		'arguments',
		array(
			'script' => $script,
			'page'   => 1,
		)
	);
	$runner = get_hook_name( $script, true );
	// Clean up if there are remaining hooks.
	unschedule_action( $runner, array( $script ) );
	// Schedule next runner.
	wp_schedule_single_event( time(), $runner );
}

/**
 * Cron handler from the actual running of the scripts.
 *
 * @param string $script The script to run.
 * @return void
 */
function cron_runner( string $script ) {
	$options = is_valid_script( $script );
	if ( false === $options ) {
		return;
	}
	$runner = get_hook_name( $script, true );

	// Unschedule any extra runners in case of overlap. Redundancy only.
	unschedule_action( $runner );

	$arguments = get_setting( $script, 'arguments', array() );
	if ( empty( $arguments ) ) {
		// Abort if no arguments are found. Redundancy only.
		return;
	}
	// Create log file handle.
	$log = new File( gmdate( 'Y-m-d' ) . '-cron-' . $script, 'logs', 'log' );
	if ( 1 === $arguments['page'] ) {
		// Add timestamp to first run.
		$log->append_file_data( "\n----- " . gmdate( 'H:i:s' ) . " -----\n" );
	}

	// Capture output from function by starting output buffer.
	ob_start();
	// Execute function, passing the json body to it.
	$return = call_user_func( $options['callback'], new Arguments( $arguments ) );
	// Store output in variable, and discard and end the buffer.
	$output = ob_get_clean();
	// Write output to log file.
	$log->append_file_data( $output );

	if ( ! $return instanceof \Jcore\Runner\Arguments || ! $return->check_status() ) {
		// Fail.
		$log->append_file_data( 'Script failed' );
		delete_setting( $script, 'arguments' );
		return;
	} else {
		$arguments['exportFile'] = $return->write_export();
		if ( ! empty( $return->next_page ) ) {
			// Next page is defined.
			$arguments['page'] = $return->next_page;
			$arguments['data'] = $return->data;
			// Save script settings.
			set_setting( $script, 'arguments', $arguments );
			// Schedule next runner.
			wp_schedule_single_event( time(), $runner );
		} else {
			// Delete script settings if no more pages.
			delete_setting( $script, 'arguments' );
		}
	}
}

/**
 * Check for valid script, and return options.
 *
 * @param string $script Script name.
 * @return false|array
 */
function is_valid_script( string $script ) {
	$functions = \apply_filters( 'jcore_runner_functions', array() );

	if ( empty( $functions[ $script ] ) ) {
		return false;
	}

	$callback = $functions[ $script ]['callback'] ?? false;
	if ( ! $callback || ! is_callable( $callback ) ) {
		return false;
	}

	return $functions[ $script ];
}
