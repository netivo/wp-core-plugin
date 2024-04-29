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
         * @var
         */
        protected $main_class = null;

        protected $modules = [];

		/**
		 * Woocommerce constructor.
		 *
		 * @param string $include_path Include path for plugin.
		 *
		 * @throws \ReflectionException When error.
		 */
		public function __construct( $main_class ) {
            $this->main_class = $main_class;

            if(!empty($this->main_class->get_configuration()['modules']['woocommerce'])) {
                $this->modules = $this->main_class->get_configuration()['modules']['woocommerce'];
            }

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
            if(!empty($this->modules['product_tabs'])) {
                foreach($this->modules['product_tabs'] as $tab) {
                    if(class_exists($tab)) {
                        new $tab( $this->main_class->get_view_path() );
                    }
                }
            }
		}
		/**
		 * Initialize new product type
		 *
		 * @throws \ReflectionException When error.
		 */
		protected function init_product_types(){
            if(!empty($this->modules['product_types'])) {
                foreach($this->modules['product_types'] as $type) {
                    if(class_exists($type)) {
                        $type::register();
                    }
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