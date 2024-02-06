<?php
/**
 * Cron functions.
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

register_deactivation_hook( __FILE__, '\Jcore\Runner\cron_deactivate' );
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
				cron_runner( $key, true );
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
		'display'  => esc_html__( 'Every Minute' ),
	);
	return $schedules;
}


/**
 * Cron handler from the actual running of the scripts.
 *
 * @param string $script The script to run.
 * @param bool   $initial Is this the initial run.
 * @return void
 */
function cron_runner( string $script, bool $initial = false ) {
	echo $script;
}
