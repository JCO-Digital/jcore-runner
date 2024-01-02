<?php
/**
 * Plugin Name: JCORE Script Runner
 * Description: A WordPress plugin to easily allow manual running of scripts for maintenance and utility.
 * Plugin URI: https://github.com/JCO-Digital/jcore-runner#readme
 * Author: JCO Digital
 * Version: 1.1.2
 * Author URI: http://jco.fi
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

add_action( 'admin_menu', '\Jcore\Runner\add_menu' );

require_once 'utils.php';
require_once 'rest-runner.php';
require_once 'export-data.php';

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
	script_register( 'jcore_runner', '/js/jcore-runner.js', array( 'wp-api-request' ) );
	style_register( 'jcore_runner', '/css/jcore-runner.css' );
	wp_enqueue_script( 'jcore_runner' );
	wp_enqueue_style( 'jcore_runner' );

	echo '<h2>' . esc_html( apply_filters( 'jcore_runner_title', 'Script Runner' ) ) . '</h2>';
	echo '<div id="jcore-runner-buttons">';
	foreach ( \apply_filters( 'jcore_runner_functions', array() ) as $name => $data ) {
		if ( $data['input'] ) {
			foreach ( $data['input'] as $field => $input ) {
				$type = match ( $input['type'] ) {
					'number' => 'number',
					default => 'text',
				};
				echo esc_html( $input['title'] ) . ': <input type="' . esc_html( $type ) . '" data-jcore-input="' . esc_html( $name ) . '" name="' . esc_html( $field ) . '" value="' . esc_html( $input['default'] ) . '" />';
			}
		}
		echo '<button data-jcore-script="' . esc_html( $name ) . '">';
		echo esc_html( $data['title'] );
		echo '</button>';
	}
	echo '</div>';
	echo '<div id="jcore-runner-spinner"></div>';
	echo '<div id="jcore-runner-progress">Nothing running</div>';
	echo '<div id="jcore-runner-return">';
	foreach ( get_status() as $id => $data ) {
		echo '<h3>' . esc_html( $data['title'] ) . '</h3>';
		echo '<div id="jcore-runner-return-' . esc_html( $id ) . '">';
		echo esc_html( $data['content'] );
		echo '</div>';
	}
	echo '</div>';
	echo '<pre id="jcore-runner-output"></pre>';
}

/**
 * Get default status for the different scripts.
 */
function get_status() {
	$default = array(
		'status' => __( 'Status', 'jcore_runner' ),
	);
	$status  = array();
	foreach ( \apply_filters( 'jcore_runner_status', $default ) as $key => $value ) {
		$status[ $key ] = array(
			'title'   => $value,
			'content' => \apply_filters( 'jcore_runner_status_' . $key, '' ),
		);
	}
	return $status;
}
