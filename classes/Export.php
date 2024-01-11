<?php
/**
 * Class for exporting data to file.
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

/**
 * Export data.
 *
 * @package Jcore\Runner
 */
class Export {
	/**
	 * The script ID.
	 *
	 * @var string $id
	 */
	protected string $id;
	/**
	 * Name of export file
	 *
	 * @var string $filename
	 */
	protected string $filename;
	/**
	 * Array of export data.
	 *
	 * @var array $data
	 */
	protected array $data = array();
	/**
	 * Static name of folder in uploads.
	 *
	 * @var string $dir
	 */
	protected static string $dir = '/runner-exports';

	/**
	 * Constructor taking ID and optional FileName as argument.
	 *
	 * @param string $id Base name of the export file.
	 * @param string $filename Optional filename to use.
	 */
	public function __construct( string $id, string $filename = '' ) {
		$this->id = $id;
		if ( empty( $filename ) ) {
			$filename = $this->id . '-' . gmdate( 'YmdHis' );
		}
		$this->filename = $filename;
		$this->read_file_data();
	}

	/**
	 * Check if export has data to export.
	 *
	 * @return bool
	 */
	public function has_data(): bool {
		return count( $this->data ) > 0;
	}

	/**
	 * Add a row to the dataset.
	 *
	 * @param array $row Array of values to insert into data.
	 * @return void
	 */
	public function add_row( array $row ) {
		array_push( $this->data, $row );
	}

	/**
	 * Get the filename of the export file.
	 *
	 * @param string $type Type of file extension to return.
	 * @return string
	 */
	public function get_filename( string $type = '' ): string {
		return $this->filename . ( empty( $type ) ? '' : '.' . $type );
	}

	/**
	 * Get full path of export file.
	 *
	 * @param string $type Type of file extension to return.
	 * @return string The absolute path of the file.
	 */
	public function get_filepath( string $type = 'json' ) {
		$upload = $this->get_upload_dir();
		return $upload['path'] . '/' . $this->get_filename( $type );
	}

	/**
	 * Returns the work directory for the export files.
	 *
	 * @return string[] Array containing path and url.
	 */
	public function get_upload_dir() {
		$upload   = wp_upload_dir( null, false );
		$base_dir = $upload['basedir'] . static::$dir;
		if ( ! is_dir( $base_dir ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
			mkdir( $base_dir );
		}
		return array(
			'path' => $base_dir,
			'url'  => $upload['baseurl'] . static::$dir,
		);
	}

	/**
	 * Read file content from temporary file.
	 *
	 * @return void
	 */
	protected function read_file_data() {
		$json_filename = $this->get_filepath();
		if ( file_exists( $json_filename ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$json       = file_get_contents( $json_filename );
			$this->data = json_decode( $json );
		}
	}

	/**
	 * Write data to file.
	 *
	 * @return void
	 */
	public function write_file_data() {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $this->get_filepath(), wp_json_encode( $this->data ) );
	}
}
