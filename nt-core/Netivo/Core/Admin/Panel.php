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

use Netivo\Core\Theme\Main as ThemeMain;
use Netivo\Core\Plugin\Main as PluginMain;
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
         * @var ThemeMain|PluginMain|null
         */
        public ThemeMain|PluginMain|null $parent_class = null;

		/**
		 * Include path for plugin, defined in child classes
		 *
		 * @var string
		 */
		public string $include_path = '';

		/**
		 * Uri for plugin.
		 *
		 * @var string
		 */
		public string $uri = '';

        /**
         * Modules to load automatically, loaded from configuration files
         * @var array
         */
        public array $modules = [];

		/**
		 * Panel constructor.
		 */
		public function __construct($parent) {
            $this->parent_class = $parent;
		    $this->set_vars();

            if(!empty($this->parent_class->get_configuration()['modules']['admin'])) {
                $this->modules = $this->parent_class->get_configuration()['modules']['admin'];
            }

			add_action( 'admin_enqueue_scripts', [ $this, 'init_header' ] );
			try {
				$this->init_pages();
				$this->init_metaboxes();
				$this->init_gutenberg();
				$this->init_bulkactions();

				$this->init();

			} catch ( \Exception $e ) {
				var_dump($e->getCode());
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
            if(!empty($this->modules['pages'])) {
                foreach($this->modules['pages'] as $page){
                    if(class_exists($page['class'])) {
                        $className = $page['class'];
                        $children = (!empty($page['children'])) ? $page['children'] : [];
                        new $className($this->parent_class->get_view_path(), $children);
                    }
                }
            }

		}

		/**
		 * Init metaboxes in Admin view.
         */
		protected function init_metaboxes(): void
        {
            if(!empty($this->modules['metabox'])) {
                foreach($this->modules['metabox'] as $meta){
                    if(class_exists($meta)) {
                        new $meta($this->parent_class->get_view_path());
                    }
                }
            }
		}

		/**
		 * Init gutenberg blocks in Admin editor.
		 *
		 * @throws \ReflectionException When error.
		 */
		protected function init_gutenberg() {
            if(!empty($this->modules['gutenberg'])) {
                foreach($this->modules['gutenberg'] as $gutenberg) {
                    if(class_exists($gutenberg)) {
                        new $gutenberg($this->include_path, $this->uri);
                    }
                }
            }
		}

		/**
		 * Init Bulk Action to admin views.
		 *
		 * @throws \ReflectionException When error.
		 */
		protected function init_bulkactions(){
            if(!empty($this->modules['bulk'])) {
                foreach($this->modules['bulk'] as $bulk){
                    if(class_exists($bulk)) {
                        new $bulk();
                    }
                }
            }
		}

        protected abstract function set_vars();

		protected abstract function init();

        protected abstract function custom_header();
	}


}