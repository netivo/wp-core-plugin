<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: michal
 * Date: 16.11.18
 * Time: 14:40
 *
 * @package Netivo\Core\Admin\Woocommerce\Product
 */

namespace Netivo\Core\Admin\Woocommerce\Product;

use ReflectionClass;

if(!class_exists('Netivo\Core\Admin\Woocommerce\Product\Tab')) {
	/**
	 * Class Tab
	 */
	abstract class Tab {
		/**
		 * Tab id.
		 *
		 * @var string
		 */
		protected $id;
		/**
		 * Tab title.
		 *
		 * @var string
		 */
		protected $title;

		/**
		 * Path to Admin folder on server.
		 *
		 * @var string
		 */
		protected $path = '';

		/**
		 * Tab constructor.
		 *
		 * @param string $path Path to Admin folder.
		 */
		public function __construct( $path ) {
			$this->path = $path;

			add_filter('woocommerce_product_data_tabs', [$this, 'add_tab']);
			add_action('woocommerce_product_data_panels', [$this, 'display']);
			add_action( 'save_post', [ $this, 'do_save' ] );
		}

		/**
		 * Adds tab to product data metabox.
		 *
		 * @param array $tabs Current tabs in product data metabox.
		 *
		 * @return array
		 */
		public function add_tab($tabs){
			$tabs[$this->id] = array(
				'label'    => $this->title,
				'target'   => 'nt_'.$this->id.'_product_data',
				'class'    => array( '' ),
				'priority' => 12,
			);
			return $tabs;
		}

		/**
		 * Displays the tab content.
		 *
		 * @throws \Exception When error.
		 */
		public function display() {
			global $post, $thepostid, $product_object;

			$obj      = new ReflectionClass( $this );
			$filename = $obj->getFileName();
			$filename = str_replace( '.php', '', $filename );

			$name = str_replace( $this->path . '/Woocommerce/Product/Tabs', '', $filename );
			$name .= '.phtml';

			$filename = $this->path . '/views/woocommerce/product/tabs' . strtolower( $name );

			if ( file_exists( $filename ) ) {
				echo '<div id="nt_'.$this->id.'_product_data" class="panel woocommerce_options_panel">';
				include $filename;
				echo '</div>';
			} else {
				throw new \Exception( "There is no view file for this admin action" );
			}

		}

		/**
		 * Start saving process of the metabox.
		 *
		 * @param int $post_id Id of the saved post.
		 *
		 * @return mixed
		 */
		public function do_save( $post_id ) {

			return $this->save( $post_id );
		}

		/**
		 * Method where the saving process is done. Use it in metabox to save the data.
		 *
		 * @param int $post_id Id of the saved post.
		 *
		 * @return mixed
		 */
		abstract public function save( $post_id );
	}
}