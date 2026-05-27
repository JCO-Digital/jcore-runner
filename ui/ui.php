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
 * @param string $filename  Template filename.
 * @param array  $variables Variables to extract for the template.
 * @param bool   $output    Whether to output the template immediately.
 *
 * @return void|bool|string
 */
function include_template( string $filename, array $variables, bool $output = true ) {
	// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
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
 * @param mixed $type Input type.
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
		'<a class="jcore-runner-back" href="%s">%s<span>%s</span></a>',
		esc_url(
			add_query_arg(
				array(
					'page' => 'jcore-runner',
				),
				admin_url( 'tools.php' )
			)
		),
		'<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.2 288 416 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0L214.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z"/></svg>',
		esc_html__( 'All scripts', 'jcore-runner' )
	);

	echo '<div class="jcore-runner-script-header">';
	echo '<h1>' . esc_html( $params['title'] ) . '</h1>';
	echo '<div id="jcore-runner-progress" class="jcore-runner-progress" role="status" aria-live="polite">' . esc_html__( 'Ready', 'jcore-runner' ) . '</div>';
	echo '</div>';
	echo '<div class="jcore-runner-panel jcore-runner-controls">';
	echo '<div id="jcore-runner-input">';
	if ( ! empty( $params['input'] ) ) {
		foreach ( $params['input'] as $field => $input ) {
			$type = 'generic';
			if ( 'date' === $input['type'] || 'select' === $input['type'] ) {
				$type = $input['type'];
			}
			$type     = sanitize_file_name( $type );
			$filename = __DIR__ . '/inputs/' . $type . '.php';
			include_template(
				$filename,
				array(
					'params' => $params,
					'field'  => $field,
					'input'  => $input,
				)
			);
		}
	} else {
		echo '<p class="jcore-runner-empty">' . esc_html__( 'This script does not require input.', 'jcore-runner' ) . '</p>';
	}
	echo '</div>';
	echo '<div id="jcore-runner-buttons">';
	echo '<button type="button" class="button button-primary jcore-runner-run" data-jcore-script="' . esc_attr( $params['id'] ) . '"><svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80V432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.7 23-24.2 23-41s-8.7-32.2-23-41L73 39z"/></svg><span>' . esc_html__( 'Run script', 'jcore-runner' ) . '</span></button>';
	echo '</div>';
	echo '<div id="jcore-runner-spinner"></div>';
	echo '</div>';
	echo '<div id="jcore-runner-return" class="jcore-runner-panel jcore-runner-status">';
	foreach ( get_status() as $id => $data ) {
		echo '<h3>' . esc_html( $data['title'] ) . '</h3>';
		echo '<div id="jcore-runner-return-' . esc_attr( $id ) . '">';
		echo esc_html( $data['content'] );
		echo '</div>';
	}
	echo '</div>';
	echo '<pre id="jcore-runner-output" aria-live="polite"></pre>';
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
