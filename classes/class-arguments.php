<?php
/**
 * Runner Data class.
 *
 * @package Jcore\Runner
 */

namespace Jcore\Runner;

require_once 'class-export.php';


/**
 * Arguments that are passed to the custom scripts, and passed back to the handler.
 *
 * @package Jcore\Runner
 */
class Arguments {

	/**
	 * Status string, 'ok' means success. Any string can be used for debugging errors.
	 *
	 * @var string
	 */
	public string $status = 'ok';

	/**
	 * Name of script. This is an id that is used internally.
	 *
	 * @var string
	 */
	public string $script = '';

	/**
	 * Page being run. Usually goes from 1 upwards.
	 *
	 * @var int
	 */
	public int $page = 0;

	/**
	 * The next page to be called, if set to 0, execution ends.
	 *
	 * @var int
	 */
	public int $next_page = 0;

	/**
	 * Variable data that can be set and read by the script. Is passed to the next iteration.
	 *
	 * @var array
	 */
	public array $data = array();

	/**
	 * Possible input values entered by the user when running the script.
	 *
	 * @var array
	 */
	public array $input = array();

	/**
	 * Return values shown to the user can be given in a key / value pair format.
	 *
	 * @var array
	 */
	public array $return = array();

	/**
	 * Instance of the Export class. If written to it, a file will be exported to disk.
	 *
	 * @var Export
	 */
	public Export $export;

	/**
	 * Constructor initializes the object with the passed JSON data.
	 *
	 * @param array $json JSON Data.
	 * @return void
	 */
	public function __construct( array $json ) {
		$this->script = $json['script'];
		$this->page   = $json['page'];
		if ( ! empty( $json['data'] ) && is_array( $json['data'] ) ) {
			$this->data = $json['data'];
		}
		if ( ! empty( $json['input'] ) && is_array( $json['input'] ) ) {
			$this->input = $json['input'];
		}

		$export_file  = $json['exportFile'] ?? '';
		$this->export = new Export( $this->script, $export_file );
	}

	/**
	 * Sets the next page variable.
	 *
	 * @param int $nr The number of the next page to run, defaults to page+1.
	 * @return void
	 */
	public function set_next_page( $nr = 0 ) {
		if ( 0 === $nr ) {
			$nr = $this->page + 1;
		}
		$this->next_page = $nr;
	}

	/**
	 * Check if status is 'ok'.
	 *
	 * @return bool
	 */
	public function check_status() {
		return ( 'ok' === $this->status );
	}

	/**
	 * Create the return data for the call.
	 *
	 * @param string $output The captured buffer output to include.
	 * @return array
	 */
	public function return_data( string $output ) {
		$return = array(
			'status' => $this->status,
			'output' => wp_strip_all_tags( $output ),
			'return' => $this->return,
		);
		if ( ! empty( $this->next_page ) ) {
			$return['nextPage'] = $this->next_page;
		}
		if ( ! empty( $this->data ) ) {
			$return['data'] = $this->data;
		}
		if ( $this->export->has_data() ) {
			// Function exports data.
			$this->export->write_file_data();
			$return['exportFile'] = $this->export->get_filename();
		}

		return $return;
	}
}
