<?php
/**
 * UI functions.
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

/**
 * Render the script runner view.
 *
 * @param array $params The script parameters.
 *
 * @return void
 */
function render_script_page( array $params ) {
	script_register( 'jcore_runner', '/js/jcore-runner.js', array( 'wp-api-request' ) );
	wp_enqueue_script( 'jcore_runner' );

	echo '<h2>' . esc_html( $params['title'] ) . '</h2>';
	echo '<div id="jcore-runner-input">';
	if ( ! empty( $params['input'] ) ) {
		foreach ( $params['input'] as $field => $input ) {
			$type = match ( $input['type'] ) {
				'number' => 'number',
				default => 'text',
			};
			echo esc_html( $input['title'] ) . ': <input type="' . esc_html( $type ) . '" data-jcore-input="' . esc_html( $params['id'] ) . '" name="' . esc_html( $field ) . '" value="' . esc_html( $input['default'] ) . '" />';
		}
	}
	echo '</div>';
	echo '<div id="jcore-runner-buttons">';
	echo '<button class="icon" data-jcore-script="' . esc_html( $params['id'] ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80V432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.7 23-24.2 23-41s-8.7-32.2-23-41L73 39z"/></svg></span></button>';
	echo '<button class="icon" data-jcore-script="' . esc_html( $params['id'] ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M52.5 440.6c-9.5 7.9-22.8 9.7-34.1 4.4S0 428.4 0 416V96C0 83.6 7.2 72.3 18.4 67s24.5-3.6 34.1 4.4l192 160L256 241V96c0-17.7 14.3-32 32-32s32 14.3 32 32V416c0 17.7-14.3 32-32 32s-32-14.3-32-32V271l-11.5 9.6-192 160z"/></svg></button>';
	echo '<button class="icon" data-jcore-script="' . esc_html( $params['id'] ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M48 64C21.5 64 0 85.5 0 112V400c0 26.5 21.5 48 48 48H80c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H48zm192 0c-26.5 0-48 21.5-48 48V400c0 26.5 21.5 48 48 48h32c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H240z"/></svg></button>';
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
