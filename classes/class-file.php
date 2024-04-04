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
	public function __construct( string $filename, string $section = '', string $extension = 'json' ) {
		$this->filename  = $filename;
		$this->extension = $extension;
		$this->section   = $section;
	}

	/**
	 * Returns the work directory for the export files.
	 *
	 * @param string $section The file section (folder).
	 * @param string $filename Optional filename.
	 * @return string[] Array containing path and url.
	 */
	public static function get_upload_dir( $section, $filename = '' ) {
		$upload = wp_upload_dir( null, false );
		$dir    = static::$dir;
		if ( ! is_dir( $upload['basedir'] . $dir ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
			mkdir( $upload['basedir'] . $dir );
		}
		if ( ! empty( $section ) ) {
			$dir .= '/' . $section;
			if ( ! is_dir( $upload['basedir'] . $dir ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
				mkdir( $upload['basedir'] . $dir );
			}
		}
		if ( ! empty( $filename ) ) {
			$dir .= '/' . $filename;
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
		$upload = static::get_upload_dir( $this->section, $this->filename . '.' . $this->extension );
		return $upload['path'];
	}

	/**
	 * Get a list of files from a section.
	 *
	 * @param string $section The section to list.
	 * @param string $name partial file name to match.
	 * @param int    $number Max number of files.
	 * @return array
	 */
	public static function get_files( string $section, string $name = '', int $number = 5 ) {
		$upload = static::get_upload_dir( $section );
		$files  = scandir( $upload['path'] );
		rsort( $files );
		$result = array();
		foreach ( $files as $file ) {
			if ( '.' !== substr( $file, 0, 1 ) && false !== strpos( $file, $name ) ) {
				$result[] = array(
					'name' => $file,
					'path' => $upload['path'] . '/' . $file,
					'url'  => $upload['url'] . '/' . $file,
				);
				if ( --$number <= 0 ) {
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * Get the filename of the export file.
	 *
	 * @param bool $extension Whether to include extension or not.
	 * @return string
	 */
	public function get_filename( $extension = true ): string {
		if ( $extension ) {
			return $this->filename . '.' . $this->extension;
		}
		return $this->filename;
	}

	/**
	 * Get the file extension.
	 *
	 * @return string
	 */
	public function get_extension(): string {
		return $this->extension;
	}

	/**
	 * Read file content from temporary file.
	 *
	 * @param mixed $default_value Default value to return.
	 * @return array
	 */
	public function read_file_data( mixed $default_value = array() ) {
		// phpcs:disable WordPress.WP.AlternativeFunctions
		$json_filename = $this->get_filepath();
		if ( file_exists( $json_filename ) ) {
			switch ( $this->extension ) {
				case 'json':
					$json = file_get_contents( $json_filename );
					return json_decode( $json, true );
				case 'csv':
					$data = array();
					$file = fopen( $json_filename, 'rb' );
					// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
					while ( ( $row = fgetcsv( $file, null, '|' ) ) !== false ) {
						$data[] = $row;
					}
					fclose( $file );
					return $data;
			}
			$json = file_get_contents( $json_filename );
			return json_decode( $json, true );
		}
		return $default_value;
		// phpcs:enable WordPress.WP.AlternativeFunctions
	}

	/**
	 * Write data to file.
	 *
	 * @param array $data Data to write to file.
	 *
	 * @return void
	 */
	public function write_file_data( mixed $data ) {
		// phpcs:disable WordPress.WP.AlternativeFunctions
		switch ( $this->extension ) {
			default:
			case 'json':
				file_put_contents( $this->get_filepath(), wp_json_encode( $data ) );
				break;
			case 'csv':
				$file = fopen( $this->get_filepath(), 'wb' );
				foreach ( $data as $row ) {
					if ( is_array( $row ) ) {
							$row = array_values(
								array_map(
									static function ( $value ) {
										if ( is_array( $value ) ) {
											if ( is_array( $value[0] ) ) {
												$value = array_merge( ...$value );
											}
											return implode( ',', $value );
										}
										return $value;
									},
									$row
								)
							);
					}
					fputcsv( $file, $row, '|' );
				}
				fclose( $file );
				break;
		}
		// phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
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

	/**
	 * Sets the file extension.
	 *
	 * @param string $extension File extension (json, csv).
	 *
	 * @return void
	 */
	public function set_extension( string $extension ): void {
		$this->extension = $extension;
	}
}
