<?php

namespace jcore_runner;

add_action( 'rest_api_init', 'jcore_runner\add_endpoints' );

const NS = 'jcore_runner/v1';

/**
 * @return void
 */
function add_endpoints(): void {
	register_rest_route(
		NS,
		'/run',
		array(
			'methods'             => 'POST',
			'callback'            => 'jcore_runner\run_script',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
		)
	);
}

function run_script( $request ) {
	$functions = \apply_filters( 'jcore_runner_functions', array() );
	$response  = new \WP_REST_Response();
	$json      = $request->get_json_params();

	if ( empty( $functions[ $json['script'] ] ) ) {
		$response->set_code( 404 );
		return $response;
	}

	$callback = $functions[ $json['script'] ]['callback'] ?? false;
	if ( ! $callback || ! function_exists( $callback ) ) {
		$response->set_code( 404 );
		return $response;
	}

	// Capture output from function by starting output buffer.
	ob_start();
	// Execute function, passing the json body to it.
	$return = $callback( $json );
	// Store output in variable, and discard and end the buffer.
	$output = ob_get_clean();

	if ( $return === false || ! empty( $return['status'] ) && $return['status'] !== 'ok' ) {
		$response->set_code( 400 );
		$response->set_data( $return );
	} else {
		$data = array(
			'status' => 'ok',
			'output' => strip_tags( $output ),
			'return' => $return['return'] ?? array(),
		);
		if ( ! empty( $return['next_page'] ) ) {
			$data['nextPage'] = $return['next_page'];
		}

		$response->set_data( $data );
	}

	return $response;
}
