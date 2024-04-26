<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: michal
 * Date: 16.11.18
 * Time: 14:45
 *
 * @package Netivo\Core\Admin
 */

namespace Netivo\Core;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use RegexIterator;

if(!class_exists('Netivo\Core\Woocommerce')) {
	/**
	 * Class Woocommerce
	 */
	abstract class Woocommerce {
		/**
		 * Include path for plugin, defined in child classes
		 *
		 * @var string
		 */
		protected $include_path = '';

		/**
		 * Woocommerce constructor.
		 *
		 * @param string $include_path Include path for plugin.
		 *
		 * @throws \ReflectionException When error.
		 */
		public function __construct( ) {
		    $this->init_vars();
			$this->init_product_types();
			$this->init_child();
			if(is_admin()) {
				$this->init_product_tabs();
				$this->init_child_admin();
			}

		}

		/**
		 * Initialize new tab in woocommerce product data metabox.
		 *
		 * @throws \ReflectionException When error.
		 */
		protected function init_product_tabs(){
			if ( file_exists( $this->include_path . '/Admin/Woocommerce/Product/Tabs' ) ) {
				$Directory     = new RecursiveDirectoryIterator( $this->include_path . '/Admin/Woocommerce/Product/Tabs' );
				$Iterator      = new RecursiveIteratorIterator( $Directory );
				$Regex         = new RegexIterator( $Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH );
				$include_array = array();
				$rc            = new ReflectionClass( $this );
				foreach ( $Regex as $name => $obj ) {
					$name      = basename( $name );
					$name      = str_replace( '.php', '', $name );
					$namespace = $rc->getNamespaceName();
					array_push( $include_array, $namespace . '\\Admin\\Woocommerce\\Product\\Tabs\\' . $name );
				}

				foreach ( $include_array as $tab ) {
					new $tab( $this->include_path . '/Admin' );
				}
			}
		}
		/**
		 * Initialize new product type
		 *
		 * @throws \ReflectionException When error.
		 */
		protected function init_product_types(){
			if ( file_exists( $this->include_path . '/Woocommerce/Product/Type' ) ) {
				$Directory     = new RecursiveDirectoryIterator( $this->include_path . '/Woocommerce/Product/Type' );
				$Iterator      = new RecursiveIteratorIterator( $Directory );
				$Regex         = new RegexIterator( $Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH );
				$include_array = array();
				$rc            = new ReflectionClass( $this );
				foreach ( $Regex as $name => $obj ) {
					$name      = basename( $name );
					$name      = str_replace( '.php', '', $name );
					$namespace = $rc->getNamespaceName();
					array_push( $include_array, $namespace . '\\Woocommerce\\Product\\Type\\' . $name );
				}

				foreach ( $include_array as $type ) {
					$type::register();
				}
			}
		}

		/**
		 * Init custom data in woocommerce panel for child use.
		 */
		abstract protected function init_child();
		/**
		 * Init custom data in woocommerce admin panel for child use.
		 */
		abstract protected function init_child_admin();

        abstract protected function init_vars();

	}
}