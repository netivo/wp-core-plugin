<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: michal
 * Date: 09.11.18
 * Time: 08:42
 *
 * @package Netivo\Core\Admin
 */

namespace Netivo\Core\Admin;

use ReflectionClass;

if ( ! defined( 'ABSPATH' ) ) {
    header( 'HTTP/1.0 403 Forbidden' );
    exit;
}

if ( ! class_exists( 'Netivo\Core\Admin\Gutenberg' ) ) {
    /**
     * Class Gutenberg
     */
    abstract class Gutenberg {

        /**
         * Id of the block.
         *
         * @var string
         */
        protected $id;

        /**
         * Handle/namespace of the block.
         *
         * @var string
         */
        protected $handle;

        protected $dependencies = [ 'wp-element', 'wp-blocks' ];

        /**
         * Callback name.
         *
         * @var string
         */
        protected $callback = null;

        /**
         * Path to Admin folder on server.
         *
         * @var string
         */
        protected $path = '';

        /**
         * Uri to Admin folder.
         *
         * @var string
         */
        protected $uri = '';


	    protected $style = null;
	    protected $script = null;

        /**
         * Gutenberg constructor.
         *
         * @param string $path Path to Admin folder.
         * @param string $uri Uri to Admin folder.
         */
        public function __construct( $path, $uri ) {
            $this->path = $path;
            $this->uri  = $uri;
	        $this->init_style();
            add_action( 'init', [ $this, 'register_block' ] );
        }

        /**
         * Registers scripts, styles and block.
         *
         * @throws \Exception When error.
         */
        public function register_block() {
            $obj      = new ReflectionClass( $this );
            $filename = $obj->getFileName();
            $filename = str_replace( '.php', '', $filename );
	        $obj = new ReflectionClass($this);
	        $data = $obj->getAttributes();
	        foreach($data as $attribute) {
		        if($attribute->getName() == 'Netivo\Attributes\Block') {
					$name = $attribute->getArguments()[0];
		        }
	        }
	        if(empty($name)){
		        $filename = $obj->getFileName();
		        $filename = str_replace( '.php', '', $filename );
		        $name = basename($filename);
		        $name = 'src/views/gutenberg/'.strtolower($name);
	        }
            $css  = get_template_directory() . '/' . strtolower( $name ) . '/block.css';
            $css_uri  = get_template_directory_uri() . '/' . strtolower( $name ) . '/block.css';
            $js   = get_template_directory() . '/' . strtolower( $name ) . '/block.js';
            $js_uri   = get_template_directory_uri() . '/' . strtolower( $name ) . '/block.js';

            if ( file_exists( $css ) ) {
                wp_register_style( $this->handle, $css_uri, array( 'wp-edit-blocks' ) );
            }
            if ( file_exists( $js ) ) {
                wp_register_script( $this->handle, $js_uri, $this->dependencies );
            } else {
                throw new \Exception( 'Block js not found.' );
            }

	        $args = [
		        'editor_script' => $this->handle,
	        ];
	        if ( file_exists( $css ) ) {
		        $args['editor_style'] = $this->handle;
	        }
	        if(!empty($this->style)) {
		        if (file_exists($this->style['file'])) {
			        wp_register_style($this->handle . '-style', $this->style['uri']);
			        $args['style'] = $this->handle . '-style';
		        }
	        }
	        if(!empty($this->script)) {
		        if (file_exists($this->script['file'])) {
			        wp_register_script($this->handle . '-script', $this->script['uri']);
			        $args['script'] = $this->handle . '-script';
		        }
	        }
	        if ( ! empty( $this->callback ) ) {
		        $args['render_callback'] = [ $this, $this->callback ];
	        }

            register_block_type( $this->id, $args );

        }
	    abstract function init_style();

    }
}