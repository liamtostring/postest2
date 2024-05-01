<?php
/**
 * WooCommerce POS Outlet CSV importer
 *
 * @package WKWC_POS\Helper\Outlet\Importer
 * @version 3.1.0
 */

namespace WKWC_POS\Helper\Outlet\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WKWCPOS_Outlet_CSV_Importer Class.
 */
class WKWCPOS_Outlet_CSV_Importer {

	/**
	 * Tracks current row being parsed.
	 *
	 * @var integer
	 */
	protected $parsing_raw_data_index = 0;

	/**
	 * Database object.
	 *
	 * @var object $db Database object.
	 */
	protected $db = '';

	/**
	 * Pos outlet table name.
	 *
	 * @var string $table_name Table name.
	 */
	public $table_name = '';

	/**
	 * Pos outlet map table name.
	 *
	 * @var string $table_name2 Outlet map table name.
	 */
	public $table_name2 = '';

	/**
	 * Wp parse args object.
	 *
	 * @var object $params Wp parse args object.
	 */
	public $params;

	/**
	 * File object.
	 *
	 * @var object|array|string $file File object.
	 */
	public $file;

	/**
	 * File position.
	 *
	 * @var array $file_positions File position.
	 */
	public $file_positions;

	/**
	 * Singe file position.
	 *
	 * @var null|bool|int|float|string|array $file_position Single file position.
	 */
	public $file_position;

	/**
	 * Mapped keys.
	 *
	 * @var array $mapped_keys Mapped keys.
	 */
	public $mapped_keys;

	/**
	 * Raw keys.
	 *
	 * @var object|array $raw_keys Raw keys.
	 */
	public $raw_keys;

	/**
	 * Raw data.
	 *
	 * @var object|array $raw_data Raw data.
	 */
	public $raw_data;

	/**
	 * Initialize importer.
	 *
	 * @param string $file   File to read.
	 * @param array  $params Arguments for the parser.
	 */
	public function __construct( $file, $params = array() ) {

		global $wpdb;

		$this->db = $wpdb;

		$this->table_name = $this->db->prefix . 'woocommerce_pos_outlets';

		$this->table_name2 = $this->db->prefix . 'woocommerce_pos_outlet_map';

		$default_args = array(
			'start_pos'        => 0, // File pointer start.
			'end_pos'          => -1, // File pointer end.
			'lines'            => -1, // Max lines to read.
			'mapping'          => array(), // Column mapping. csv_heading => schema_heading.
			'parse'            => false, // Whether to sanitize and format data.
			'update_existing'  => false, // Whether to update existing items.
			'delimiter'        => ',', // CSV delimiter.
			'prevent_timeouts' => true, // Check memory and time usage and abort if reaching limit.
			'enclosure'        => '"', // The character used to wrap text in the CSV.
			'escape'           => "\0", // PHP uses '\' as the default escape character. This is not RFC-4180 compliant. This disables the escape character.
		);

		$this->params = wp_parse_args( $params, $default_args );
		$this->file   = $file;

		if ( isset( $this->params['mapping']['from'], $this->params['mapping']['to'] ) ) {
			$this->params['mapping'] = array_combine( $this->params['mapping']['from'], $this->params['mapping']['to'] );
		}

		// Import mappings for CSV data.
		include_once dirname( __FILE__ ) . '/mappings/mappings.php';

		$this->read_file();
	}

	/**
	 * Get file raw headers.
	 *
	 * @return array
	 */
	public function get_raw_keys() {
		return $this->raw_keys;
	}

	/**
	 * Get raw data.
	 *
	 * @return array
	 */
	public function get_raw_data() {
		return $this->raw_data;
	}

	/**
	 * Check whether a file is a valid CSV file.
	 *
	 * @todo Replace this method with wc_is_file_valid_csv() function.
	 * @param string $file File path.
	 * @param bool   $check_path Whether to also check the file is located in a valid location (Default: true).
	 * @return bool
	 */
	public static function is_file_valid_csv( $file, $check_path = true ) {
		if ( $check_path && apply_filters( 'wkwcpos_outlet_csv_importer_check_import_file_path', true ) && false !== stripos( $file, '://' ) ) {
			return false;
		}

		$valid_filetypes = self::get_valid_csv_filetypes();
		$filetype        = wp_check_filetype( $file, $valid_filetypes );
		if ( in_array( $filetype['type'], $valid_filetypes, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get all the valid filetypes for a CSV file.
	 *
	 * @return array
	 */
	protected static function get_valid_csv_filetypes() {
		return apply_filters(
			'wkwcpos_csv_outlet_import_valid_filetypes',
			array(
				'csv' => 'text/csv',
				'txt' => 'text/plain',
			)
		);
	}

	/**
	 * Read file.
	 */
	protected function read_file() {
		if ( ! self::is_file_valid_csv( $this->file ) ) {
			wp_die( esc_html__( 'Invalid file type. The importer supports CSV and TXT file formats.', 'wc_pos' ) );
		}

		$handle = fopen( $this->file, 'r' ); // @codingStandardsIgnoreLine.

		if ( false !== $handle ) {
			$this->raw_keys = version_compare( PHP_VERSION, '5.3', '>=' ) ? array_map( 'trim', fgetcsv( $handle, 0, $this->params['delimiter'], $this->params['enclosure'], $this->params['escape'] ) ) : array_map( 'trim', fgetcsv( $handle, 0, $this->params['delimiter'], $this->params['enclosure'] ) ); // @codingStandardsIgnoreLine

			// Remove BOM signature from the first item.
			if ( isset( $this->raw_keys[0] ) ) {
				$this->raw_keys[0] = $this->remove_utf8_bom( $this->raw_keys[0] );
			}

			if ( 0 !== $this->params['start_pos'] ) {
				fseek( $handle, (int) $this->params['start_pos'] );
			}

			while ( 1 ) {
				$row = version_compare( PHP_VERSION, '5.3', '>=' ) ? fgetcsv( $handle, 0, $this->params['delimiter'], $this->params['enclosure'], $this->params['escape'] ) : fgetcsv( $handle, 0, $this->params['delimiter'], $this->params['enclosure'] ); // @codingStandardsIgnoreLine

				if ( false !== $row ) {
					$this->raw_data[]                                 = $row;
					$this->file_positions[ count( $this->raw_data ) ] = ftell( $handle );

					if ( ( $this->params['end_pos'] > 0 && ftell( $handle ) >= $this->params['end_pos'] ) || 0 === --$this->params['lines'] ) {
						break;
					}
				} else {
					break;
				}
			}

			$this->file_position = ftell( $handle );
		}

		if ( ! empty( $this->params['mapping'] ) ) {
			$this->set_mapped_keys();
		}

		if ( $this->params['parse'] ) {
			$this->set_parsed_data();
		}
	}

	/**
	 * Remove UTF-8 BOM signature.
	 *
	 * @param string $string String to handle.
	 *
	 * @return string
	 */
	protected function remove_utf8_bom( $string ) {
		if ( 'efbbbf' === substr( bin2hex( $string ), 0, 6 ) ) {
			$string = substr( $string, 3 );
		}

		return $string;
	}

	/**
	 * Set file mapped keys.
	 */
	protected function set_mapped_keys() {
		$mapping = $this->params['mapping'];

		foreach ( $this->raw_keys as $key ) {
			$this->mapped_keys[] = isset( $mapping[ $key ] ) ? $mapping[ $key ] : $key;
		}
	}

	/**
	 * Parse relative field and return outlet ID.
	 *
	 * Handles `id:xx`.
	 *
	 * If mapping to an id: and the outlet ID does not exist, this link is not
	 * valid.
	 *
	 * @param string $value Field value.
	 *
	 * @return int|string
	 */
	public function parse_relative_field( $value ) {

		if ( empty( $value ) ) {
			return '';
		}

		// IDs are prefixed with id:.
		if ( preg_match( '/^id:(\d+)$/', $value, $matches ) ) {
			$id = intval( $matches[1] );

			// See if the given ID maps to a valid product allready.
			$existing_id = $this->db->get_var( $this->db->prepare( "SELECT id FROM {$this->table_name} WHERE id = %d", $id ) ); // WPCS: db call ok, cache ok.

			if ( $existing_id ) {
				return absint( $existing_id );
			}
		}

		return '';

	}

	/**
	 * Parse the ID field.
	 *
	 * If we're not doing an update, create a placeholder outlet so mapping works
	 * for rows following this one.
	 *
	 * @param string $value Field value.
	 *
	 * @return int
	 */
	public function parse_id_field( $value ) {

		$id = absint( $value );

		if ( ! $id ) {
			return 0;
		}

		$original_id = $this->db->get_var( $this->db->prepare( "SELECT id FROM {$this->table_name} WHERE id = %d", $id ) ); // WPCS: db call ok, cache ok.

		if ( $original_id ) {
			return absint( $original_id );
		}

		return $id && ! is_wp_error( $id ) ? $id : 0;
	}

	/**
	 * Parse relative comma-delineated field and return product ID.
	 *
	 * @param string $value Field value.
	 *
	 * @return array
	 */
	public function parse_relative_comma_field( $value ) {
		if ( empty( $value ) ) {
			return array();
		}

		return array_filter( array_map( array( $this, 'parse_relative_field' ), $this->explode_values( $value ) ) );
	}


	/**
	 * Just skip current field.
	 *
	 * By default is applied wc_clean() to all not listed fields
	 * in self::get_formating_callback(), use this method to skip any formating.
	 *
	 * @param string $value Field value.
	 *
	 * @return string
	 */
	public function parse_skip_field( $value ) {
		return $value;
	}

	/**
	 * Parse a description value field
	 *
	 * @param string $description field value.
	 *
	 * @return string
	 */
	public function parse_description_field( $description ) {
		$parts = explode( "\\\\n", $description );
		foreach ( $parts as $key => $part ) {
			$parts[ $key ] = str_replace( '\n', "\n", $part );
		}

		return implode( '\\\n', $parts );
	}


	/**
	 * Get formatting callback.
	 *
	 * @return array
	 */
	protected function get_formating_callback() {

		/**
		 * Columns not mentioned here will get parsed with 'wc_clean'.
		 * column_name => callback.
		 */
		$data_formatting = array(
			'id'              => array( $this, 'parse_id_field' ),
			'outlet_name'     => array( $this, 'parse_description_field' ),
			'outlet_address'  => array( $this, 'parse_description_field' ),
			'outlet_status'   => array( $this, 'parse_description_field' ),
			'outlet_city'     => array( $this, 'parse_description_field' ),
			'outlet_state'    => array( $this, 'parse_description_field' ),
			'outlet_postcode' => array( $this, 'parse_description_field' ),
			'outlet_country'  => array( $this, 'parse_description_field' ),
			'description'     => array( $this, 'parse_description_field' ),
		);

		$callbacks = array();

		// Figure out the parse function for each column.
		foreach ( $this->get_mapped_keys() as $index => $heading ) {
			$callback = 'wc_clean';

			if ( isset( $data_formatting[ $heading ] ) ) {
				$callback = $data_formatting[ $heading ];
			}

			$callbacks[] = $callback;
		}

		return apply_filters( 'wkwcpos_outlet_importer_formatting_callbacks', $callbacks, $this );
	}

	/**
	 * Check if strings starts with determined word.
	 *
	 * @param string $haystack Complete sentence.
	 * @param string $needle   Excerpt.
	 *
	 * @return bool
	 */
	protected function starts_with( $haystack, $needle ) {
		return substr( $haystack, 0, strlen( $needle ) ) === $needle;
	}

	/**
	 * Expand special and internal data into the correct formats for the product CRUD.
	 *
	 * @param array $data Data to import.
	 *
	 * @return array
	 */
	protected function expand_data( $data ) {

		$data = apply_filters( 'wkwcpos_outlet_importer_pre_expand_data', $data );

		if ( isset( $data['outlet_status'] ) ) {

			$outlet_status = array(
				'enable'  => 0,
				'disable' => 1,
			);

			$data['outlet_status'] = isset( $outlet_status[ $data['outlet_status'] ] ) ? $outlet_status[ $data['outlet_status'] ] : 1;

		}

		return $data;
	}

	/**
	 * Get file mapped headers.
	 *
	 * @return array
	 */
	public function get_mapped_keys() {
		return ! empty( $this->mapped_keys ) ? $this->mapped_keys : $this->raw_keys;
	}

	/**
	 * Map and format raw data to known fields.
	 */
	protected function set_parsed_data() {
		$parse_functions = $this->get_formating_callback();
		$mapped_keys     = $this->get_mapped_keys();
		$use_mb          = function_exists( 'mb_convert_encoding' );

		// Parse the data.
		foreach ( $this->raw_data as $row_index => $row ) {
			// Skip empty rows.
			if ( ! count( array_filter( $row ) ) ) {
				continue;
			}

			$this->parsing_raw_data_index = $row_index;

			$data = array();

			do_action( 'wkwcpos_outlet_importer_before_set_parsed_data', $row, $mapped_keys );

			foreach ( $row as $id => $value ) {
				// Skip ignored columns.
				if ( empty( $mapped_keys[ $id ] ) ) {
					continue;
				}

				// Convert UTF8.
				if ( $use_mb ) {
					$encoding = mb_detect_encoding( $value, mb_detect_order(), true );
					if ( $encoding ) {
						$value = mb_convert_encoding( $value, 'UTF-8', $encoding );
					} else {
						$value = mb_convert_encoding( $value, 'UTF-8', 'UTF-8' );
					}
				} else {
					$value = wp_check_invalid_utf8( $value, true );
				}

				$data[ $mapped_keys[ $id ] ] = call_user_func( $parse_functions[ $id ], $value );
			}

			$this->parsed_data[] = apply_filters( 'wkwcpos_outlet_importer_parsed_data', $this->expand_data( $data ), $this );
		}
	}

	/**
	 * Get a string to identify the row from parsed data.
	 *
	 * @param array $parsed_data Parsed data.
	 *
	 * @return string
	 */
	protected function get_row_id( $parsed_data ) {
		$id = isset( $parsed_data['id'] ) ? absint( $parsed_data['id'] ) : 0;

		$row_data = array();

		if ( $id ) {
			/* translators: %d: product ID */
			$row_data[] = sprintf( __( 'ID %d', 'wc_pos' ), $id );
		}

		return implode( ', ', $row_data );
	}

	/**
	 * Get file pointer position as a percentage of file size.
	 *
	 * @return int
	 */
	public function get_percent_complete() {
		$size = filesize( $this->file );
		if ( ! $size ) {
			return 0;
		}

		return absint( min( round( ( $this->file_position / $size ) * 100 ), 100 ) );
	}

	/**
	 * Process importer.
	 *
	 * Do not import products with IDs or SKUs that already exist if option
	 * update existing is false, and likewise, if updating products, do not
	 * process rows which do not exist if an ID/SKU is provided.
	 *
	 * @return array
	 */
	public function import() {
		$this->start_time = time();
		$index            = 0;
		$update_existing  = $this->params['update_existing'];
		$data             = array(
			'imported' => array(),
			'failed'   => array(),
			'updated'  => array(),
			'skipped'  => array(),
		);

		foreach ( $this->parsed_data as $parsed_data_key => $parsed_data ) {
			do_action( 'wkwcpos_outlet_import_before_import', $parsed_data );

			$id        = isset( $parsed_data['id'] ) ? absint( $parsed_data['id'] ) : 0;
			$id_exists = false;

			if ( $id ) {
				// See if the given ID maps to a valid product allready.
				$id_exists = $this->db->get_var( $this->db->prepare( "SELECT id FROM {$this->table_name} WHERE id = %d", $id ) ); // WPCS: db call ok, cache ok.
			}

			if ( $id_exists && ! $update_existing ) {
				$data['skipped'][] = new \WP_Error(
					'wkwcpos_outlet_importer_error',
					esc_html__( 'A outlet with this ID already exists.', 'wc_pos' ),
					array(
						'id'  => $id,
						'row' => $this->get_row_id( $parsed_data ),
					)
				);
				continue;
			}

			if ( $update_existing && ( isset( $parsed_data['id'] ) ) && ! $id_exists ) {
				$data['skipped'][] = new \WP_Error(
					'wkwcpos_outlet_importer_error',
					esc_html__( 'No matching outlet exists to update.', 'wc_pos' ),
					array(
						'id'  => $id,
						'row' => $this->get_row_id( $parsed_data ),
					)
				);
				continue;
			}

			$result = $this->process_item( $parsed_data );

			if ( is_wp_error( $result ) ) {
				$result->add_data( array( 'row' => $this->get_row_id( $parsed_data ) ) );
				$data['failed'][] = $result;
			} elseif ( $result['updated'] ) {
				$data['updated'][] = $result['id'];
			} else {
				$data['imported'][] = $result['id'];
			}

			$index ++;

			if ( $this->params['prevent_timeouts'] && ( $this->time_exceeded() || $this->memory_exceeded() ) ) {
				$this->file_position = $this->file_positions[ $index ];
				break;
			}
		}

		return $data;
	}

	/**
	 * Memory exceeded
	 *
	 * Ensures the batch process never exceeds 90%
	 * of the maximum WordPress memory.
	 *
	 * @return bool
	 */
	protected function memory_exceeded() {
		$memory_limit   = $this->get_memory_limit() * 0.9; // 90% of max memory
		$current_memory = memory_get_usage( true );
		$return         = false;
		if ( $current_memory >= $memory_limit ) {
			$return = true;
		}
		return apply_filters( 'wkwcpos_outlet_importer_memory_exceeded', $return );
	}

	/**
	 * Get memory limit
	 *
	 * @return int
	 */
	protected function get_memory_limit() {
		if ( function_exists( 'ini_get' ) ) {
			$memory_limit = ini_get( 'memory_limit' );
		} else {
			// Sensible default.
			$memory_limit = '128M';
		}

		if ( ! $memory_limit || -1 === intval( $memory_limit ) ) {
			// Unlimited, set to 32GB.
			$memory_limit = '32000M';
		}
		return intval( $memory_limit ) * 1024 * 1024;
	}

	/**
	 * Time exceeded.
	 *
	 * Ensures the batch never exceeds a sensible time limit.
	 * A timeout limit of 30s is common on shared hosting.
	 *
	 * @return bool
	 */
	protected function time_exceeded() {
		$finish = $this->start_time + apply_filters( 'wkwcpos_outlet_importer_default_time_limit', 20 ); // 20 seconds
		$return = false;
		if ( time() >= $finish ) {
			$return = true;
		}
		return apply_filters( 'wkwcpos_outlet_importer_time_exceeded', $return );
	}

	/**
	 * Process a single item and save.
	 *
	 * @throws Exception If item cannot be processed.
	 * @param  array $data Raw CSV data.
	 * @return array|WP_Error
	 */
	public function process_item( $data ) {

		try {

			do_action( 'wkwcpos_outlet_import_before_process_item', $data );

			$outlet_id = 0;

			if ( ! empty( $data['id'] ) && $this->params['update_existing'] ) {

				$outlet_id = $data['id'];

				$updating = true;

				$this->db->update(
					$this->table_name,
					array(
						'outlet_name'     => $data['outlet_name'],
						'outlet_status'   => $data['outlet_status'],
						'outlet_address'  => $data['outlet_address'],
						'outlet_city'     => $data['outlet_city'],
						'outlet_state'    => $data['outlet_state'],
						'outlet_postcode' => $data['outlet_postcode'],
						'outlet_country'  => $data['outlet_country'],
					),
					array(
						'id' => $outlet_id,
					),
					array(
						'%s',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					),
					array(
						'%d',
					)
				);

			} else {

				$updating = false;

				$insert = $this->db->insert(
					$this->table_name,
					array(
						'outlet_name'     => $data['outlet_name'],
						'outlet_status'   => $data['outlet_status'],
						'outlet_address'  => $data['outlet_address'],
						'outlet_city'     => $data['outlet_city'],
						'outlet_state'    => $data['outlet_state'],
						'outlet_postcode' => $data['outlet_postcode'],
						'outlet_country'  => $data['outlet_country'],
					),
					array(
						'%s',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);

				if ( $insert ) {

					$outlet_id = $this->db->insert_id;

					$this->db->insert(
						$this->table_name2,
						array(
							'outlet_id' => $outlet_id,
							'user_id'   => 0,
						),
						array(
							'%d',
							'%d',
						)
					);
				}
			}

			return array(
				'id'      => $outlet_id,
				'updated' => $updating,
			);

		} catch ( \Exception $e ) {
			return new \WP_Error( 'wkwcpos_outlet_importer_error', $e->getMessage(), array( 'status' => $e->getCode() ) );
		}
	}

}
