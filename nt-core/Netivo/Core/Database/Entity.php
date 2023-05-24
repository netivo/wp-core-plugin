<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: Michal
 * Date: 23.10.2018
 * Time: 15:28
 *
 * @package Netivo\Core\Database
 */

namespace Netivo\Core\Database;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

if ( ! class_exists( '\Netivo\Core\Database\Entity' ) ) {
	/**
	 * Class Model
	 */
	class Entity {
		
		/**
		 * State of entity. One of: new, existing, changed
		 * new - entity newly created, not existing in DB
		 * existing - entity existing in DB, not changed
		 * changed - entity existing in DB and changed
		 *
		 * @var string
		 */
		public $state = 'new';
		
		/**
		 * Return called class.
		 *
		 * @return string
		 */
		public function get_self(){
			return get_called_class();
		}
		
		/**
		 * Get table data information.
		 *
		 * @return \Netivo\Core\Database\Annotations\Table|null
		 *
		 * @throws \ReflectionException When error.
		 */
		public function get_table_data() {
			return Annotations::get_table_annotations($this->get_self());
		}
		
		/**
		 * Gets entity state.
		 *
		 * @return string
		 */
		public function get_state(){
			return $this->state;
		}
		
		/**
		 * Set the entity state.
		 *
		 * @param string $new_state New state.
		 *
		 * @return $this
		 */
		public function set_state($new_state){
			$this->state = $new_state;
			return $this;
		}
		
		/**
		 * Set entity data from array.
		 *
		 * @param array $data Data to set.
		 *
		 * @return $this
		 *
		 * @throws \ReflectionException When error.
		 */
		public function from_array($data){
			if(is_array($data)){
				foreach($data as $key => $value){
					$class = $this->get_self();
					$table = Annotations::get_table_annotations($class);
					if(!empty($table)) {
						if ( array_key_exists( $key, $table->get_columns() ) ) {
							$this->$key = $value;
						}
					}
				}
			}
			return $this;
		}
		
	}
}