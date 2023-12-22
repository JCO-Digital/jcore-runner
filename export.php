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
	protected string $id;
	protected string $filename;
	protected array $data = array();

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
		$upload = wp_upload_dir();
		$this->read_file_data();
	}

	/**
	 * Add a row to the dataset.
	 *
	 * @param array $row
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
	public function get_filename( string $type = 'json' ) {
		return $this->filename . '.' . $type;
	}

	/**
	 * Read file content from temporary file.
	 *
	 * @return void
	 */
	protected function read_file_data() {
		$json_filename = $this->filename . '.json';
		if ( file_exists( $json_filename ) ) {
			file_get_contents( $json_filename );

		}
	}
}
