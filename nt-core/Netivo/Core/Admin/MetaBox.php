<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: michal
 * Date: 07.11.18
 * Time: 15:17
 *
 * @package Netivo\Core\Admin
 */

namespace Netivo\Core\Admin;

use ReflectionClass;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

if ( ! class_exists( 'Netivo\Core\Admin\MetaBox' ) ) {
	/**
	 * Class MetaBox
	 */
	abstract class MetaBox {
		/**
		 * Metabox id.
		 *
		 * @var string
		 */
		protected $id;
		/**
		 * Metabox title.
		 *
		 * @var string
		 */
		protected $title;
		/**
		 * Post type/s where the metabox is visible.
		 *
		 * @var string
		 */
		protected $screen;
		/**
		 * Page template on which metabox must appear. Works only when screen includes page.
		 * It gets value with relative path to template file
		 * or value 'home-page' if the metabox must display on front-page only.
		 *
		 * @var string
		 */
		protected $template = '';
		/**
		 * Context of the metabox. One of:
		 * advanced - main column of edition
		 * side - right column of edition
		 *
		 * @var string
		 */
		protected $context = 'advanced';
		/**
		 * Priority of the metabox in list. One of:
		 * default - no priority, show when initalized.
		 * high - high priority, show on top but with the init sequence stayed.
		 *
		 * @var string
		 */
		protected $priority = 'default';

		/**
		 * Path to Admin folder on server.
		 *
		 * @var string
		 */
		protected $path = '';

		/**
		 * MetaBox constructor.
		 *
		 * @param string $path Path to Admin folder.
		 */
		public function __construct( $path ) {
			$this->path = $path;
			if ( ! is_array( $this->screen ) ) {
				$tmp            = $this->screen;
				$this->screen   = [];
				$this->screen[] = $tmp;
			}

			add_action( 'add_meta_boxes', [ $this, 'register_box' ] );
			add_action( 'save_post', [ $this, 'do_save' ] );

		}

		/**
		 * Register metabox in admin panel.
		 */
		public function register_box() {
			if(empty($this->template) || !in_array('page', $this->screen)) {
				add_meta_box( $this->id, $this->title, [
					$this,
					'display'
				], $this->screen, $this->context, $this->priority );
			} else {
				global $post;
				if($this->template == 'home-page'){
					if ((int) get_option( 'page_on_front' ) === $post->ID) {
						add_meta_box( $this->id, $this->title, [
							$this,
							'display'
						], $this->screen, $this->context, $this->priority );
					}
				} else {
					if(get_post_meta($post->ID, '_wp_page_template', true) === $this->template){
						add_meta_box( $this->id, $this->title, [
							$this,
							'display'
						], $this->screen, $this->context, $this->priority );
					}
				}
			}
		}

		/**
		 * Displays the metabox content.
		 *
		 * @param \WP_Post $post Id of the edited post.
		 *
		 * @throws \Exception When error.
		 */
		public function display( $post ) {
			wp_nonce_field( 'save_' . $this->id, $this->id . '_nonce' );

			$obj      = new ReflectionClass( $this );
			$filename = $obj->getFileName();
			$filename = str_replace( '.php', '', $filename );

			$name = str_replace( $this->path . '/MetaBox/', '', $filename );
			$name .= '.phtml';

			$filename = $this->path . '/views/metabox/' . strtolower( $name );

			if ( file_exists( $filename ) ) {
				include $filename;
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
			if ( ! isset( $_POST[ $this->id . '_nonce' ] ) ) {
				return $post_id;
			}
			if ( ! wp_verify_nonce( $_POST[ $this->id . '_nonce' ], 'save_' . $this->id ) ) {
				return $post_id;
			}
			if ( ! in_array( $_POST['post_type'], $this->screen ) ) {
				return $post_id;
			}
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}

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