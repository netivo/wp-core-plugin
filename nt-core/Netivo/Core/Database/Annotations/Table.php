<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: Michal
 * Date: 26.10.2018
 * Time: 08:44
 *
 * @package Netivo\Core\Database\Annotations
 */

namespace Netivo\Core\Database\Annotations;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

if ( ! class_exists( '\Netivo\Core\Database\Annotation\Table' ) ) {
	/**
	 * Class Table
	 */
	class Table {
		/**
		 * Name of table in database without prefix.
		 *
		 * @var string
		 */
		public $name;
		/**
		 * Version of table.
		 *
		 * @var float
		 */
		public $version;
		
		/**
		 * Column list in table.
		 *
		 * @var Column[]
		 */
		public $columns;
		
		/**
		 * Gets the table name.
		 *
		 * @return mixed
		 */
		public function get_name() {
			return $this->name;
		}
		
		/**
		 * Sets the table name.
		 *
		 * @param string $name Name of table.
		 *
		 * @return Table
		 */
		public function set_name( $name ) {
			$this->name = $name;
			
			return $this;
		}
		
		/**
		 * Gets version of table.
		 *
		 * @return float
		 */
		public function get_version() {
			return $this->version;
		}
		
		/**
		 * Sets version of table.
		 *
		 * @param float $version Version number.
		 *
		 * @return Table
		 */
		public function set_version( $version ) {
			$this->version = $version;
			
			return $this;
		}
		
		/**
		 * Gets columns in table.
		 *
		 * @return \Netivo\Core\Database\Annotations\Column[]
		 */
		public function get_columns() {
			return $this->columns;
		}
		
		/**
		 * Sets columns in table.
		 *
		 * @param \Netivo\Core\Database\Annotations\Column[] $columns Columns in table.
		 *
		 * @return Table
		 */
		public function set_columns( $columns ) {
			$this->columns = $columns;
			
			return $this;
		}
		
		/**
		 * Adds column to the set.
		 *
		 * @param string                                   $name Name of column.
		 * @param \Netivo\Core\Database\Annotations\Column $column Column class.
		 *
		 * @return \Netivo\Core\Database\Annotations\Table
		 */
		public function add_column( $name, $column ) {
			$this->columns[ $name ] = $column;
			
			return $this;
		}
		
	}
}