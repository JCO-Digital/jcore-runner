<?php
/**
 * Rest backend functions.
 *
 * @package jcore_runner
 */

namespace Jcore\Runner;

// Disable Warnings in rest API because why would you ever want that?
// phpcs:ignore
error_reporting( E_ERROR | E_PARSE );

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

	$logname = gmdate( 'Y-m-d' ) . '-' . $json['script'];
	$log     = new File( $logname, 'logs', 'log' );
	// Capture output from function by starting output buffer.
	ob_start();
	// Execute function, passing the json body to it.
	$return = call_user_func( $callback, new Arguments( $json ) );
	// Store output in variable, and discard and end the buffer.
	$output = ob_get_clean();
	if ( $request['clear'] ) {
		// Add timestamp to first run.
		$log->append_file_data( "\n----- " . gmdate( 'H:i:s' ) . " -----\n" );
	}
	$log->append_file_data( $output );

	if ( ! $return instanceof \Jcore\Runner\Arguments || ! $return->check_status() ) {
		$response->set_status( 400 );
		$response->set_data( $return );
	} else {
		$response->set_data( $return->return_data( $output ) );
	}

	return $response;
}
