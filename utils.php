<?php

namespace Jcore\Runner;

/**
 * Register script wrapper.
 *
 * @param string $name Script name.
 * @param string $file Filename.
 * @param array  $dependencies Dependencies.
 * @param string $version Optional version number.
 */
function script_register( $name, $file, $dependencies = array(), $version = null ) {
	$info = get_file_info( $file, $version );

	if ( false !== $info ) {
		wp_register_script(
			$name,
			$info['uri'],
			$dependencies,
			$info['version'],
			true
		);
	}
}

/**
 * Register style wrapper.
 *
 * @param string $name Style name.
 * @param string $file Filename.
 * @param array  $dependencies Dependencies.
 * @param string $version Optional version number.
 */
function style_register( $name, $file, $dependencies = array(), $version = '' ) {
	$info = get_file_info( $file, $version );

	if ( false !== $info ) {
		wp_register_style(
			$name,
			$info['uri'],
			$dependencies,
			$info['version']
		);
	}
}

/**
 * Get file info for script/style registration.
 *
 * @param string $file Filename.
 * @param string $version Optional version number.
 *
 * @return bool|string[]
 */
function get_file_info( $file, $version = '' ) {
	if ( ! empty( $version ) ) {
		$version .= '-';
	}
	foreach (
		array(
			array(
				'path' => join_path( WP_CONTENT_DIR, $file ),
				'uri'  => join_path( content_url(), $file ),
			),
			array(
				'path' => join_path( plugin_dir_path( __FILE__ ), $file ),
				'uri'  => join_path( plugin_dir_url( __FILE__ ), $file ),
			),
		) as $location ) {
		if ( file_exists( $location['path'] ) ) {
			$version .= filemtime( $location['path'] );

			return array(
				'uri'     => $location['uri'],
				'path'    => $location['path'],
				'version' => $version,
			);
		}
	}
	return false;
}

/**
 * A function that joins together all parts of a path.
 *
 * @param string $path Base path.
 * @param string ...$parts Path parts to be joined.
 *
 * @return string
 */
function join_path( string $path, string ...$parts ): string {
	foreach ( $parts as $part ) {
		$path .= '/' . trim( $part, '/ ' );
	}

	return $path;
}
