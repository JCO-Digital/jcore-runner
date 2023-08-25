<?php

/*
Plugin Name: JCORE Script Runner
Description: Plugin for running functions from the backend.
Plugin URI: http://jco.fi
Author: JCO Digital
Version: 0.1.0
Author URI: http://jco.fi
*/

namespace jcore_runner;

add_action( 'admin_menu', '\jcore_runner\add_menu' );

require_once 'utils.php';
require_once 'rest-runner.php';

/**
 * Adds menu to WP Admin
 */
function add_menu() {
	add_submenu_page(
		'tools.php', // Parent slug.
		'Script Runner', // Page Title.
		'JCORE Script Runner', // Menu Title.
		'manage_options', // Capabilities.
		'jcore-runner', // Menu Slug.
		'\jcore_runner\show_admin_page' // Page render callback.
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

	echo '<h2>JCORE Runner Scripts</h2>';
	echo '<div id="jcore-runner-buttons">';
	foreach ( \apply_filters( 'jcore_runner_functions', array() ) as $name => $data ) {
		echo '<button data-jcore-script="' . $name . '">';
		echo $data['title'];
		echo '</button>';
	}
	echo '</div>';
	echo '<div id="jcore-runner-spinner"></div>';
	echo '<div id="jcore-runner-progress">Nothing running</div>';
	echo '<pre id="jcore-runner-output"></pre>';
}


