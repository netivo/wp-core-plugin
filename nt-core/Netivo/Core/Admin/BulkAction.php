<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: michal
 * Date: 26.11.18
 * Time: 12:53
 *
 * @package Netivo\Core\Admin
 */

namespace Netivo\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

if ( ! class_exists( 'Netivo\Core\Admin\BulkActions' ) ) {
	/**
	 * Class BulkAction
	 */
	abstract class BulkAction {

		/**
		 * Id of action, also a slug.
		 *
		 * @var string
		 */
		protected $id = '';

		/**
		 * Name of action, to display in select.
		 *
		 * @var string
		 */
		protected $name = '';

		/**
		 * Screen on which action can be run.
		 *
		 * @var string
		 */
		protected $screen = 'edit-post';

		/**
		 * BulkAction constructor.
		 */
		public function __construct() {
			add_filter( 'bulk_actions-' . $this->screen, [ $this, 'add_action' ] );
			add_action( 'admin_init', [ $this, 'action' ] );
		}

		/**
		 * Adds action to bulk action select.
		 *
		 * @param array $actions Current actions array.
		 *
		 * @return mixed
		 */
		public function add_action($actions){
			$actions[$this->id] = $this->name;
			return $actions;
		}

		/**
		 * Prepare action to run.
		 */
		public function action(){
			if ( !isset($_GET['post_type']) || $_GET['post_type'] != 'shop_order' ) {
				return;
			}
			if ( isset($_GET['action']) && $_GET['action'] === $this->id) {

				if ( !check_admin_referer("bulk-posts") ) {
					return;
				}

				$data = $_REQUEST['post'];
				$this->do_action($data);
			}
		}

		/**
		 * Do bulk action.
		 *
		 * @param array $data Posts to edit.
		 *
		 * @return mixed
		 */
		abstract public function do_action($data);

	}
}