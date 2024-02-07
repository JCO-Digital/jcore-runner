<?php
/**
 * Class for handling file operations.
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

/**
 * Class for handling file operations.
 */
class File {
	/**
	 * Static name of folder in uploads.
	 *
	 * @var string $dir
	 */
	protected static string $dir = '/jcore-runner';


	/**
	 * The file being operated on.
	 *
	 * @var string $filename
	 */
	protected string $filename;

	/**
	 * The file extension.
	 *
	 * @var string $extension
	 */
	protected string $extension;

	/**
	 * A containing folder for files.
	 *
	 * @var string $section
	 */
	protected string $section;

	/**
	 * Pass the filename and section.
	 *
	 * @param string $filename Name of file.
	 * @param string $section Section that file should be placed in.
	 * @param string $extension File extension.
	 * @return void
	 */
	public function __construct( string $filename, string $section = '', $extension = 'json' ) {
		$this->filename  = $filename;
		$this->extension = $extension;
		$this->section   = $section;
	}


	/**
	 * Returns the work directory for the export files.
	 *
	 * @return string[] Array containing path and url.
	 */
	public function get_upload_dir() {
		$upload = wp_upload_dir( null, false );
		$dir    = static::$dir;
		if ( ! is_dir( $upload['basedir'] . $dir ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
			mkdir( $upload['basedir'] . $dir );
		}
		if ( ! empty( $this->section ) ) {
			$dir .= '/' . $this->section;
			if ( ! is_dir( $upload['basedir'] . $dir ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
				mkdir( $upload['basedir'] . $dir );
			}
		}

		return array(
			'path' => $upload['basedir'] . $dir,
			'url'  => $upload['baseurl'] . $dir,
		);
	}

	/**
	 * Get full path of export file.
	 *
	 * @return string The absolute path of the file.
	 */
	public function get_filepath() {
		$upload = static::get_upload_dir();
		return $upload['path'] . '/' . $this->filename . '.' . $this->extension;
	}

	/**
	 * Get the filename of the export file.
	 *
	 * @return string
	 */
	public function get_filename(): string {
		return $this->filename . '.' . $this->extension;
	}

	/**
	 * Read file content from temporary file.
	 *
	 * @param mixed $default_value Default value to return.
	 * @return array
	 */
	public function read_file_data( mixed $default_value = array() ) {
		$json_filename = $this->get_filepath();
		if ( file_exists( $json_filename ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$json = file_get_contents( $json_filename );
			return json_decode( $json, true );
		}
		return $default_value;
	}

	/**
	 * Write data to file.
	 *
	 * @param array $data Data to write to file.
	 * @return void
	 */
	public function write_file_data( $data ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $this->get_filepath(), wp_json_encode( $data ) );
	}

	/**
	 * Append data to file.
	 *
	 * @param string $data Data to write to file.
	 */
	public function append_file_data( string $data ) {
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$file = fopen( $this->get_filepath(), 'a' );
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		fwrite( $file, $data );
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $file );
	}
}
