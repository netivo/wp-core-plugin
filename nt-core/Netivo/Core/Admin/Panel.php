<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: Michal
 * Date: 10.10.2018
 * Time: 13:33
 *
 * @package Netivo\Admin
 */

namespace Netivo\Core\Admin;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use RegexIterator;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

if ( ! class_exists( '\Netivo\Core\Admin\Panel' ) ) {
	/**
	 * Class for admin Panel
	 */
	abstract class Panel {

		/**
		 * Include path for plugin, defined in child classes
		 *
		 * @var string
		 */
		public $include_path = '';

		/**
		 * Uri for plugin.
		 *
		 * @var string
		 */
		public $uri = '';

		/**
		 * Panel constructor.
		 */
		public function __construct() {
		    $this->set_vars();

			add_action( 'admin_enqueue_scripts', [ $this, 'init_header' ] );
			try {
				$this->init_pages();
				$this->init_metaboxes();
				$this->init_gutenberg();
				$this->init_bulkactions();

				$this->init();

			} catch ( \Exception $e ) {
				$e->getCode();
			}
		}

		/**
		 * Initializes scripts and styles loaded in admin page.
		 */
		public function init_header() {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'media-upload' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-progressbar' );
			wp_enqueue_media();

			if ( ! wp_style_is( 'netivo-jq-ui', 'enqueued' ) ) {
				wp_enqueue_style( 'netivo-jq-ui', NT_CORE_PLUGIN_URL . "/assets/admin/css/jquery-ui.min.css" );
			}

			if ( ! wp_style_is( 'netivo-admin', 'enqueued' ) ) {
				wp_enqueue_style( 'netivo-admin', NT_CORE_PLUGIN_URL . "/assets/admin/css/admin.min.css" );
			}
			if ( ! wp_script_is( 'netivo-admin', 'enqueued' ) ) {
				wp_enqueue_script( 'netivo-admin', NT_CORE_PLUGIN_URL . "/assets/admin/js/admin.min.js" );
			}
			$this->custom_header();
		}

		/**
		 * Init pages to Admin view.
		 *
		 * @throws \ReflectionException When error.
		 */
		protected function init_pages() {
			$include_array = [];

			$files = glob( $this->include_path . '/Page/*.php' );
			$obj   = new ReflectionClass( $this );

			foreach ( $files as $file ) {
				$name         = basename( $file );
				$not_included = array();
				if ( ! in_array( $name, $not_included ) ) {
					$name      = str_replace( '.php', '', $name );
					$namespace = $obj->getNamespaceName();
					array_push( $include_array, $namespace . '\\Page\\' . $name );
				}
			}
			foreach ( $include_array as $page ) {
				new $page( $this->include_path );
			}

		}

		/**
		 * Init metaboxes in Admin view.
		 *
		 * @throws \ReflectionException When error.
		 */
		protected function init_metaboxes() {
			if ( file_exists( $this->include_path . '/MetaBox' ) ) {
				$Directory     = new RecursiveDirectoryIterator( $this->include_path . '/MetaBox' );
				$Iterator      = new RecursiveIteratorIterator( $Directory );
				$Regex         = new RegexIterator( $Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH );
				$include_array = array();
				$rc            = new ReflectionClass( $this );
				foreach ( $Regex as $name => $obj ) {
					$name      = str_replace($this->include_path . '/MetaBox/', '', $name);
					$name      = str_replace( ['.php', '/'], ['', '\\'], $name );
					$namespace = $rc->getNamespaceName();
					array_push( $include_array, $namespace . '\\MetaBox\\' . $name );
				}
				
				foreach ( $include_array as $metabox ) {
					new $metabox( $this->include_path );
				}
			}
		}

		/**
		 * Init gutenberg blocks in Admin editor.
		 *
		 * @throws \ReflectionException When error.
		 */
		protected function init_gutenberg() {
			if ( file_exists( $this->include_path . '/Gutenberg' ) ) {
				$Directory     = new RecursiveDirectoryIterator( $this->include_path . '/Gutenberg' );
				$Iterator      = new RecursiveIteratorIterator( $Directory );
				$Regex         = new RegexIterator( $Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH );
				$include_array = array();
				$rc            = new ReflectionClass( $this );
				foreach ( $Regex as $name => $obj ) {
					$name      = str_replace($this->include_path . '/Gutenberg/', '', $name);
					$name      = str_replace( ['.php', '/'], ['', '\\'], $name );
					$namespace = $rc->getNamespaceName();
					array_push( $include_array, $namespace . '\\Gutenberg\\' . $name );
				}

				foreach ( $include_array as $gutenberg ) {
					new $gutenberg( $this->include_path, $this->uri );
				}
			}
		}

		/**
		 * Init Bulk Action to admin views.
		 *
		 * @throws \ReflectionException When error.
		 */
		protected function init_bulkactions(){
			if ( file_exists( $this->include_path . '/Bulk' ) ) {
				$Directory     = new RecursiveDirectoryIterator( $this->include_path . '/Bulk' );
				$Iterator      = new RecursiveIteratorIterator( $Directory );
				$Regex         = new RegexIterator( $Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH );
				$include_array = array();
				$rc            = new ReflectionClass( $this );
				foreach ( $Regex as $name => $obj ) {
					$name      = str_replace($this->include_path . '/Bulk/', '', $name);
					$name      = str_replace( ['.php', '/'], ['', '\\'], $name );
					$namespace = $rc->getNamespaceName();
					array_push( $include_array, $namespace . '\\Bulk\\' . $name );
				}

				foreach ( $include_array as $bulk ) {
					new $bulk();
				}
			}
		}

        protected abstract function set_vars();

		protected abstract function init();
	}


}