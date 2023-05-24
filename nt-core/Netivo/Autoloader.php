<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: Michal
 * Date: 08.10.2018
 * Time: 16:38
 *
 * @package Netivo
 */

namespace Netivo;

global $netivo_loader;
if ( ! class_exists( '\Netivo\Autoloader' ) ) {
	/**
	 * Class Netivo_Autoloader
	 * Autoloader for Netivo Themes and Plugins
	 */
	class Autoloader {
		
		/**
		 * Check if autoloader is valid
		 *
		 * @var bool Valid class.
		 */
		protected $_valid = true;
		
		/**
		 * Prefix for autoloaded classes
		 *
		 * @var string Class prefix.
		 */
		protected $_prefix = 'Netivo';
		
		/**
		 * Include paths for autoloader
		 *
		 * @var array Include Paths.
		 */
		protected $_includePaths = [];
		
		/**
		 * Registers the autoloader
		 */
		public function register() {
			if ( $this->_valid ) {
				spl_autoload_register( array( $this, 'autoload' ) );
				$this->_valid = false;
			}
		}
		
		/**
		 * Autoload function
		 *
		 * @param string $className Class name to load.
		 *
		 * @return bool
		 */
		public function autoload( $className ) {
			if ( strpos( $className, $this->_prefix . '\\' ) !== 0 ) {
				return false;
			}
			if ( empty( $this->_includePaths ) ) {
				return false;
			}
			
			$includePath = '';
			
			foreach ( $this->_includePaths as $prefix => $path ) {
				if ( strpos( $className, $prefix . '\\' ) === 0 ) {
					$includePath = $path;
					break;
				}
			}
			
			if ( empty( $includePath ) ) {
				return false;
			}
			
			$req_file = str_replace('\\', '/', $className) . '.php';

			foreach($includePath as $path) {
				if ( file_exists( $path . '/' . $req_file ) ) {
					require_once $path . '/' . $req_file;
					return true;
				}
			}
			return false;
			
		}
		
		/**
		 * Adds include paths to autoloader
		 *
		 * @param string $prefix Prefix of classes in path.
		 * @param string $path Path to load.
		 *
		 * @return \Netivo\Autoloader
		 */
		public function addIncludePath( $prefix, $path ) {
			if ( ! array_key_exists( $prefix, $this->_includePaths ) ) {
				$this->_includePaths[ $prefix ] = [ $path ];
			} else {
				$this->_includePaths[ $prefix ][] = $path;
			}
			
			return $this;
		}
		
	}
    $netivo_loader = new Autoloader();
    $netivo_loader->register();
	
	do_action('loader_created');
}
$netivo_loader->addIncludePath('Netivo\\Core', NT_CORE_PLUGIN_PATH);
