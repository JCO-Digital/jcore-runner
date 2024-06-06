<?php
/**
 * Class for exporting data to file.
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

require_once 'class-file.php';

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
	 * @var File $file
	 */
	protected File $file;
	/**
	 * Array of export data.
	 *
	 * @var array $data
	 */
	protected array $data = array();

	/**
	 * Constructor taking ID and optional FileName as argument.
	 *
	 * @param string $id Base name of the export file.
	 * @param string $filename Optional filename to use.
	 * @param string $extension Optional file extension.
	 */
	public function __construct( string $id, string $filename = '', string $extension = 'json' ) {
		$this->id = $id;
		if ( empty( $filename ) ) {
			$filename = $this->id . '-' . gmdate( 'YmdHis' );
		}
		$this->file = new File( $filename, 'export', $extension );
		$this->data = $this->file->read_file_data();
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
	 * @param int   $index Insert at a specific point.
	 * @return void
	 */
	public function add_row( array $row, int $index = -1 ) {
		if ( $index >= 0 ) {
			array_splice( $this->data, $index, 0, $row );
		} else {
			$this->data[] = $row;
		}
	}

	/**
	 * Get the filename of the export file.
	 *
	 * @return string
	 */
	public function get_filename(): string {
		return $this->file->get_filename( false );
	}

	/**
	 * Returns the file extension.
	 *
	 * @return string
	 */
	public function get_extension(): string {
		return $this->file->get_extension();
	}

	/**
	 * Sets the file extension. (and the format)
	 *
	 * @param string $extension File extension (json, csv).
	 *
	 * @return void
	 */
	public function set_extension( string $extension ): void {
		$this->file->set_extension( $extension );
		$this->write_file_data( $extension );
	}

	/**
	 * Write data to file.
	 *
	 * @return void
	 */
	public function write_file_data() {
		$this->file->write_file_data( $this->data );
	}
}
