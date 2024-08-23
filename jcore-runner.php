<?php
/**
 * Plugin Name: JCORE Script Runner
 * Description: A WordPress plugin to easily allow manual running of scripts for maintenance and utility.
 * Plugin URI: https://github.com/JCO-Digital/jcore-runner#readme
 * Author: JCO Digital
 * Version: 4.0.1-rc.0-rc.4-rc.3-rc.2-rc.1-rc.0-2-1-0
 * Author URI: http://jco.fi
 * Text Domain: jcore-runner
 * Domain Path: /languages
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

register_deactivation_hook( __FILE__, '\Jcore\Runner\cron_deactivate' );

add_action( 'admin_menu', '\Jcore\Runner\add_menu' );
add_action( 'init', '\Jcore\Runner\load_textdomain' );

require_once 'utils.php';
require_once 'cron.php';
require_once 'rest-runner.php';
require_once 'classes/class-arguments.php';
require_once 'classes/class-runnertable.php';
require_once 'ui/ui.php';

/**
 * Load translations.
 *
 * @return void
 */
function load_textdomain() {
	load_plugin_textdomain( 'jcore-runner', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

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

	$script = get_script_from_url( 'script' );

	if ( ! $script ) {
		$table = new RunnerTable();
		$table->prepare_items();

		$table->display();
	} else {
		render_script_page( $script );
	}
}

/**
 * Register the cron schedule actions.
 *
 * @return void
 */
function handle_cron_action(): void {
	$schedule = get_script_from_url( 'schedule' );
	if ( $schedule ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'hourly';
		schedule_action( $schedule['id'], $action );
		$location = add_query_arg(
			array(
				'page' => 'jcore-runner',
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $location );
		exit();
	}
}
add_action( 'admin_init', '\Jcore\Runner\handle_cron_action' );

// Handles setting the title to be the script name to better see which is which when multiple runners are open.
add_filter(
	'admin_title',
	static function ( $admin_title, $title ) {
		$script = get_script_from_url( 'script' );
		if ( ! $script ) {
			return $admin_title;
		}
		// translators: %1$s: Script name, %2$s: Original title.
		return sprintf( __( '%1$s &#8212; %2$s', 'jcore-runner' ), $script['title'], $title );
	},
	10,
	2
);
