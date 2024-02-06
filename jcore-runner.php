<?php
/**
 * Plugin Name: JCORE Script Runner
 * Description: A WordPress plugin to easily allow manual running of scripts for maintenance and utility.
 * Plugin URI: https://github.com/JCO-Digital/jcore-runner#readme
 * Author: JCO Digital
 * Version: 2.0.1
 * Author URI: http://jco.fi
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

add_action( 'admin_menu', '\Jcore\Runner\add_menu' );

require_once 'utils.php';
require_once 'cron.php';
require_once 'rest-runner.php';
require_once 'classes/class-arguments.php';
require_once 'classes/class-runnertable.php';
require_once 'ui.php';

/**
 * Adds menu to WP Admin
 */
function add_menu() {
	add_submenu_page(
		'tools.php', // Parent slug.
		apply_filters( 'jcore_runner_title', 'Script Runner' ), // Page Title.
		apply_filters( 'jcore_runner_menu', 'JCORE Script Runner' ), // Menu Title.
		'manage_options', // Capabilities.
		'jcore-runner', // Menu Slug.
		'\Jcore\Runner\show_admin_page' // Page render callback.
	);
}

/**
 * Main function hooked to the tools menu.
 */
function show_admin_page() {
	style_register( 'jcore_runner', '/css/jcore-runner.css' );
	wp_enqueue_style( 'jcore_runner' );

	$script   = get_script_from_url( 'script' );
	$schedule = get_script_from_url( 'schedule' );

	if ( $schedule ) {
		$hook = RunnerTable::get_hook_name( $schedule['id'] );
		unschedule_action( $hook );
		if ( ! wp_next_scheduled( $hook ) ) {
			wp_schedule_event( time(), 'every_minute', $hook );
		}

		$location = add_query_arg(
			array(
				'page' => 'jcore-runner',
			),
			admin_url( 'admin.php' )
		);

		header( 'Location: ' . $location );
		exit();
	}

	if ( ! $script ) {
		$table = new RunnerTable();
		$table->prepare_items();

		$table->display();
	} else {
		render_script_page( $script );
	}
}
