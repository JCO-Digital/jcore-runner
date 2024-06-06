<?php
/**
 * UI functions.
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

/**
 * Checks if file exists and includes it with the variables.
 *
 * @param string $filename
 * @param array  $variables
 * @param bool   $output
 *
 * @return void|bool|string
 */
function include_template( string $filename, array $variables, bool $output = true ) {
	extract( $variables );
	if ( file_exists( $filename ) ) {
		if ( $output ) {
			include $filename;
		} else {
			ob_start();
			include $filename;
			return ob_get_clean();
		}
	}
}

/**
 * Add all scripts needed to be registered here, depending on the type of input.
 *
 * @param mixed $type
 *
 * @return void
 */
function register_input_scripts( mixed $type ): void {
	switch ( $type ) {
		case 'select':
			wp_enqueue_script(
				'select2',
				'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
				array( 'jquery' ),
				'4.0.13',
				false
			);
			wp_enqueue_style(
				'select2',
				'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
				array(),
				'4.0.13',
			);
			break;
		case 'date':
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css', array(), '1.13.2' );
			wp_enqueue_style( 'jquery-ui' );
			break;
	}
}

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
	$export_dir = File::get_upload_dir( 'export' )['url'];
	wp_add_inline_script(
		'jcore_runner',
		'const jcore_export_url = "' . esc_js( trailingslashit( $export_dir ) ) . '";',
		'before'
	);

	if ( ! empty( $params['input'] ) ) {
		foreach ( $params['input'] as $field => $input ) {
			register_input_scripts( $input['type'] );
		}
	}

	printf(
		'<a class="back" href="%s">%s</a>',
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_url(
			add_query_arg(
				array(
					'page' => 'jcore-runner',
				),
				admin_url( 'admin.php' )
			)
		),
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.2 288 416 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0L214.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/></svg>'
	);

	echo '<h2>' . esc_html( $params['title'] ) . '</h2>';
	echo '<div id="jcore-runner-input">';
	if ( ! empty( $params['input'] ) ) {
		foreach ( $params['input'] as $field => $input ) {
			$type = match ( $input['type'] ) {
				'date'   => 'date',
				'select' => 'select',
				default => 'generic',
			};
			$type     = sanitize_file_name( $type );
			$filename = __DIR__ . '/ui/inputs/' . $type . '.php';
			include_template(
				$filename,
				array(
					'params' => $params,
					'field'  => $field,
					'input'  => $input,
				)
			);
		}
	}
	echo '</div>';
	echo '<div id="jcore-runner-buttons">';
	echo '<button class="icon" data-jcore-script="' . esc_html( $params['id'] ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80V432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.7 23-24.2 23-41s-8.7-32.2-23-41L73 39z"/></svg></span></button>';
	// echo '<button class="icon" data-jcore-script="' . esc_html( $params['id'] ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M52.5 440.6c-9.5 7.9-22.8 9.7-34.1 4.4S0 428.4 0 416V96C0 83.6 7.2 72.3 18.4 67s24.5-3.6 34.1 4.4l192 160L256 241V96c0-17.7 14.3-32 32-32s32 14.3 32 32V416c0 17.7-14.3 32-32 32s-32-14.3-32-32V271l-11.5 9.6-192 160z"/></svg></button>';
	// echo '<button class="icon" data-jcore-script="' . esc_html( $params['id'] ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M48 64C21.5 64 0 85.5 0 112V400c0 26.5 21.5 48 48 48H80c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H48zm192 0c-26.5 0-48 21.5-48 48V400c0 26.5 21.5 48 48 48h32c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H240z"/></svg></button>';
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
