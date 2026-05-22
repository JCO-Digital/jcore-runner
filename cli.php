<?php
/**
 * WP-CLI integration.
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

add_action( 'plugins_loaded', '\Jcore\Runner\register_cli_commands', 999 );

/**
 * Registers runner scripts as WP-CLI commands.
 *
 * @return void
 */
function register_cli_commands(): void {
	if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
		return;
	}

	\WP_CLI::add_command( 'runner list', '\Jcore\Runner\cli_list_scripts' );
	\WP_CLI::add_command( 'runner run', '\Jcore\Runner\cli_run_script_by_arg' );

	foreach ( get_valid_scripts() as $script => $options ) {
		if ( in_array( $script, array( 'list', 'run' ), true ) ) {
			continue;
		}

		\WP_CLI::add_command(
			'runner ' . $script,
			static function ( array $args, array $assoc_args ) use ( $script ): void {
				cli_run_script( $script, $args, $assoc_args );
			}
		);
	}
}

/**
 * Lists scripts registered with JCORE Runner.
 *
 * ## EXAMPLES
 *
 *     wp runner list
 *
 * @param array $args       Positional arguments.
 * @param array $assoc_args Associative arguments.
 *
 * @return void
 */
function cli_list_scripts( array $args, array $assoc_args ): void {
	$items = array();
	foreach ( get_valid_scripts() as $script => $options ) {
		$items[] = array(
			'id'     => $script,
			'title'  => $options['title'] ?? $script,
			'inputs' => implode( ',', array_keys( $options['input'] ?? array() ) ),
		);
	}

	\WP_CLI\Utils\format_items( $assoc_args['format'] ?? 'table', $items, array( 'id', 'title', 'inputs' ) );
}

/**
 * Runs a script by ID.
 *
 * ## OPTIONS
 *
 * <script>
 * : Script ID to run.
 *
 * [--page=<page>]
 * : Starting page. Defaults to 1.
 *
 * [--max-pages=<max-pages>]
 * : Maximum pages to process before stopping. Defaults to 100.
 *
 * [--export-file=<export-file>]
 * : Existing export file basename to append to.
 *
 * [--export-file-extension=<extension>]
 * : Export file extension. Defaults to json.
 *
 * [--<field>=<value>]
 * : Input fields defined by the runner script.
 *
 * ## EXAMPLES
 *
 *     wp runner run cleanup_transients
 *     wp runner run process_event_queue --limit=50
 *
 * @param array $args       Positional arguments.
 * @param array $assoc_args Associative arguments.
 *
 * @return void
 */
function cli_run_script_by_arg( array $args, array $assoc_args ): void {
	if ( empty( $args[0] ) ) {
		\WP_CLI::error( 'Missing script ID.' );
	}

	cli_run_script( (string) $args[0], array_slice( $args, 1 ), $assoc_args );
}

/**
 * Runs a registered runner script.
 *
 * @param string $script     Script ID.
 * @param array  $args       Positional arguments.
 * @param array  $assoc_args Associative arguments.
 *
 * @return void
 */
function cli_run_script( string $script, array $args, array $assoc_args ): void {
	$options = is_valid_script( $script );
	if ( false === $options ) {
		\WP_CLI::error( "Unknown or uncallable runner script: {$script}" );
	}

	$page      = max( 1, absint( $assoc_args['page'] ?? 1 ) );
	$max_pages = max( 1, absint( $assoc_args['max-pages'] ?? 100 ) );
	$data      = array();
	$processed = 0;
	$arguments = array(
		'script'              => $script,
		'page'                => $page,
		'input'               => cli_build_input( $options, $assoc_args ),
		'data'                => $data,
		'exportFile'          => $assoc_args['export-file'] ?? '',
		'exportFileExtension' => $assoc_args['export-file-extension'] ?? 'json',
	);

	do {
		++$processed;
		$arguments['page'] = $page;
		$arguments['data'] = $data;

		\WP_CLI::log( "Running {$script}, page {$page}." );

		ob_start();
		$return = call_user_func( $options['callback'], new Arguments( $arguments ) );
		$output = ob_get_clean();

		if ( '' !== trim( $output ) ) {
			\WP_CLI::line( wp_strip_all_tags( $output ) );
		}

		if ( ! $return instanceof Arguments || ! $return->check_status() ) {
			\WP_CLI::error( 'Runner script failed.' );
		}

		if ( ! empty( $return->return ) ) {
			foreach ( $return->return as $key => $value ) {
				\WP_CLI::log( sprintf( '%s: %s', $key, cli_format_value( $value ) ) );
			}
		}

		$export_file = $return->write_export();
		if ( ! empty( $export_file ) ) {
			$arguments['exportFile']          = $export_file;
			$arguments['exportFileExtension'] = $return->export->get_extension();
			\WP_CLI::log( sprintf( 'Export: %s.%s', $export_file, $return->export->get_extension() ) );
		}

		$data = $return->data;
		$page = $return->next_page;

		if ( ! empty( $page ) && $processed >= $max_pages ) {
			\WP_CLI::warning( "Stopped after {$max_pages} pages. Re-run with --page={$page} to continue." );
			break;
		}
	} while ( ! empty( $page ) );

	\WP_CLI::success( "Runner script complete: {$script}" );
}

/**
 * Builds runner input values from WP-CLI associative arguments.
 *
 * @param array $options    Runner script options.
 * @param array $assoc_args WP-CLI associative arguments.
 *
 * @return array
 */
function cli_build_input( array $options, array $assoc_args ): array {
	$reserved = array(
		'page',
		'max-pages',
		'export-file',
		'export-file-extension',
		'format',
	);
	$input    = array();
	$fields   = $options['input'] ?? array();

	foreach ( $fields as $field => $settings ) {
		$type  = $settings['type'] ?? 'text';
		$value = $assoc_args[ $field ] ?? cli_get_default_input_value( $settings );

		if ( 'checkbox' === $type ) {
			$input[ $field ] = \WP_CLI\Utils\get_flag_value( $assoc_args, $field, (bool) $value );
			continue;
		}

		if ( 'select' === $type ) {
			$input[ $field ] = is_array( $value ) ? $value : array_map( 'trim', explode( ',', (string) $value ) );
			continue;
		}

		if ( 'file' === $type && ! empty( $value ) ) {
			$path = (string) $value;
			if ( ! file_exists( $path ) ) {
				\WP_CLI::error( "File not found for --{$field}: {$path}" );
			}
			$input[ $field ] = array(
				'name'     => basename( $path ),
				'type'     => '',
				'tmp_name' => $path,
				'error'    => 0,
				'size'     => filesize( $path ),
			);
			continue;
		}

		$input[ $field ] = $value;
	}

	foreach ( $assoc_args as $field => $value ) {
		if ( in_array( $field, $reserved, true ) || array_key_exists( $field, $input ) ) {
			continue;
		}
		$input[ $field ] = $value;
	}

	return $input;
}

/**
 * Gets the default input value for a registered runner field.
 *
 * @param array $settings Input field settings.
 *
 * @return mixed
 */
function cli_get_default_input_value( array $settings ): mixed {
	if ( array_key_exists( 'default', $settings ) ) {
		return $settings['default'];
	}

	if ( 'select' === ( $settings['type'] ?? '' ) && ! empty( $settings['options'] ) ) {
		return array_key_first( $settings['options'] );
	}

	return '';
}

/**
 * Returns callable runner scripts.
 *
 * @return array
 */
function get_valid_scripts(): array {
	$scripts = array();
	foreach ( \apply_filters( 'jcore_runner_functions', array() ) as $script => $options ) {
		if ( ! empty( $options['callback'] ) && is_callable( $options['callback'] ) ) {
			$scripts[ $script ] = $options;
		}
	}

	return $scripts;
}

/**
 * Formats a return value for CLI output.
 *
 * @param mixed $value Value to format.
 *
 * @return string
 */
function cli_format_value( mixed $value ): string {
	if ( is_scalar( $value ) || null === $value ) {
		return (string) $value;
	}

	return wp_json_encode( $value );
}
