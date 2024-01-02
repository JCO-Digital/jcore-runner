<?php
/**
 * Rest backend functions.
 *
 * @package jcore_runner
 */

namespace Jcore\Runner;

use WP_REST_Response;

add_action( 'rest_api_init', 'Jcore\Runner\add_endpoints' );

const NS = 'jcore_runner/v1';

/**
 * Define rest endpoints.
 *
 * @return void
 */
function add_endpoints(): void {
	register_rest_route(
		NS,
		'/run',
		array(
			'methods'             => 'POST',
			'callback'            => 'Jcore\Runner\run_script',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
		)
	);
}

/**
 * Endpoint that runs the defined function.
 *
 * @param mixed $request Rest request.
 * @return WP_REST_Response
 */
function run_script( $request ) {
	$functions = \apply_filters( 'jcore_runner_functions', array() );
	$response  = new \WP_REST_Response();
	$json      = $request->get_json_params();

	if ( empty( $functions[ $json['script'] ] ) ) {
		$response->set_status( 404 );
		return $response;
	}

	$callback = $functions[ $json['script'] ]['callback'] ?? false;
	if ( ! $callback || ! is_callable( $callback ) ) {
		$response->set_status( 404 );
		return $response;
	}

	// Capture output from function by starting output buffer.
	ob_start();
	// Execute function, passing the json body to it.
	$return = call_user_func( $callback, $json );
	// Store output in variable, and discard and end the buffer.
	$output = ob_get_clean();

	if ( false === $return || ! empty( $return['status'] ) && 'ok' !== $return['status'] ) {
		$response->set_status( 400 );
		$response->set_data( $return );
	} else {
		$data = array(
			'status' => 'ok',
			'output' => wp_strip_all_tags( $output ),
			'return' => $return['return'] ?? array(),
		);
		if ( ! empty( $return['export'] ) ) {
			// Function exports data.
			$export = new Export( $json['script'], $json['exportFile'] ?? '' );
			foreach ( $return['export'] as $row ) {
				$export->add_row( $row );
			}
			$export->write_file_data();
			$data['exportFile'] = $export->get_filename();
		}
		if ( ! empty( $return['input'] ) ) {
			$data['input'] = $return['input'];
		}
		if ( ! empty( $return['next_page'] ) ) {
			$data['nextPage'] = $return['next_page'];
		}

		$response->set_data( $data );
	}

	return $response;
}
