<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: Michal
 * Date: 23.10.2018
 * Time: 13:39
 *
 * @package Netivo\Core
 */

namespace Netivo\Core;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

if ( ! class_exists( '\Netivo\Core\Endpoint' ) ) {
	/**
	 * Class Endpoint
	 */
	abstract class Endpoint {
		/**
		 * Name of the endpoint. It is name of query var.
		 *
		 * @var string
		 */
		protected $name = '';
		/**
		 * Type of endpoint. One of: template, action
		 * template - endpoint will load custom template
		 * action - endpoint will do action and exit
		 *
		 * @var string
		 */
		protected $type = 'template';
		/**
		 * Endpoint mask describing the places the endpoint should be added.
		 *
		 * @var int
		 */
		protected $place = EP_NONE;
		/**
		 * If type is template template name to be loaded.
		 *
		 * @var string
		 */
		protected $template = '';
		
		/**
		 * Endpoint constructor.
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'register_endpoint' ] );
			add_action( 'template_redirect', [ $this, 'redirect_template' ] );
		}
		
		/**
		 * Registers endpoint with specified name
		 */
		public function register_endpoint() {
			add_rewrite_endpoint( $this->name, $this->place );
		}
		
		/**
		 * Redirect template or do action, concerning the endpoint type.
		 *
		 * @return string
		 */
		public function redirect_template() {
			if ( get_query_var( $this->name ) ) {
				if ( $this->type == 'template' ) {
					
					return locate_template( $this->template );
					
				} elseif ( $this->type == 'action' ) {
					
					$this->doAction( get_query_var( $this->name ) );
					
					exit();
				}
			}
		}
		
		/**
		 * Action to be done.
		 *
		 * @param mixed $var Query variable data.
		 */
		abstract public function doAction( $var );
		
	}
}