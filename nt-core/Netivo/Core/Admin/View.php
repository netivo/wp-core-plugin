<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: Michal
 * Date: 10.10.2018
 * Time: 13:56
 *
 * @package \Netivo\Admin
 */

namespace Netivo\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

if ( ! class_exists( '\Netivo\Core\Admin\View' ) ) {
	/**
	 * Class for admin View
	 */
	class View {
		
		/**
		 * Variables to put on view
		 *
		 * @var array
		 */
		protected $_variables = [];
		
		/**
		 * Page to which view is assigned
		 *
		 * @var null|\Netivo\Core\Admin\Page
		 */
		protected $_page = null;
		
		/**
		 * View constructor.
		 *
		 * @param \Netivo\Core\Admin\Page $page Page to which view is initialized.
		 */
		public function __construct( Page $page ) {
			$this->_page = $page;
		}
		
		
		/**
		 * Displays content of page
		 *
		 * @throws \Exception When error.
		 */
		public function display() {
			$this->render();
		}
		
		/**
		 * Renders the view
		 *
		 * @throws \Exception When error.
		 */
		protected function render() {
			require NT_CORE_PLUGIN_PATH.'/Netivo/Core/Admin/views/layout.phtml';
		}
		
		/**
		 * Renders the page content
		 *
		 * @throws \Exception When error.
		 */
		public function content() {
			extract( $this->_variables );
			if ( file_exists( $this->_page->get_view_file() ) ) {
				require $this->_page->get_view_file();
			} else {
				throw new \Exception( "There is no view file for this admin action" );
			}
		}
		
		/**
		 * Magical method to get the parameter from variables array.
		 *
		 * @param string $name Name of parameter to get.
		 *
		 * @return mixed|null
		 */
		public function __get( $name ) {
			return ( array_key_exists( $name, $this->_variables ) ) ? $this->_variables[ $name ] : null;
		}
		
		/**
		 * Magical method to set parameter in variables to value.
		 *
		 * @param string $name Name of the parameter to set.
		 * @param mixed  $value Value of new parameter.
		 */
		public function __set( $name, $value ) {
			$this->_variables[ $name ] = $value;
		}
	}
}