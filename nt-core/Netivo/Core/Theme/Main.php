<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: Michal
 * Date: 08.10.2018
 * Time: 17:09
 *
 * @package Netivo Core Theme
 */

namespace Netivo\Core\Theme;

require_once ABSPATH . 'wp-admin/includes/plugin.php';

use Netivo\Theme\Admin\Panel;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

/**
 * Main Netivo Theme Class
 *
 * Class \Netivo\Core\Theme\Main
 */
abstract class Main {
	/**
	 * Main theme instance, theme run once, allow for only one instance per run
	 *
	 * @var \Netivo\Core\Theme\Main
	 */
	protected static $instances = array();

	/**
	 * Class name of Admin panel.
	 *
	 * @var string
	 */
	public static $admin_panel = '';

	/**
	 * Class name of Woocommerce panel class.
	 *
	 * @var string
	 */
	public static $woocommerce_panel = '';

	/**
	 * Configuration for theme
	 *
	 * @var array
	 */
	protected $configuration = array();

    protected $view_path = '';

	/**
	 * Get class instance, allowed only one instance per run
	 *
	 * @return Netivo\Core\Theme\Main
	 */
	public static function get_instance(){
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
        }
        return self::$instances[$class];
    }

	/**
	 * Theme constructor inits all necessary data.
	 */
	protected function __construct() {
		$this->init_configuration();

		if ( array_key_exists( 'admin_bar', $this->configuration ) ) {
			if ( ! $this->configuration['admin_bar'] ) {
				add_action( 'after_setup_theme', [ $this, 'remove_admin_bar' ] );
			}
		}

		if(array_key_exists('supports', $this->configuration)){
		    foreach($this->configuration['supports'] as $key => $support){
		        if(is_string($support)){
		            $args = (array_key_exists($support, $this->configuration['supports'])) ? $this->configuration['supports'][$support] : [];
		            if(empty($args)) add_theme_support($support);
		            else add_theme_support($support, $args);
                }
            }
        }

		$this->init_security();
		$this->init_content_filters();
		$this->init_front_site();
        $this->init_customizer();

		if(function_exists('WC')) {
			$this->init_woocommerce();
		}

		$this->init();

		if ( is_admin() ) {
			$this->init_admin_site();
		}

	}

	/**
	 * Disable clone from public.
	 */
	protected function __clone() {
	}

	/**
	 * Init configuration array from files
	 */
	protected function init_configuration() {
		if ( file_exists( get_stylesheet_directory() . "/config/" ) ) {
			$imagesConfig   = array();
			$postsConfig    = array();
			$menuConfig     = array();
			$assetsConfig   = array();
			$sidebarsConfig = array();
			$mainConfig     = array();
            $modulesConfig   = array();

			$config_dir = get_stylesheet_directory() . "/config/";
			if ( file_exists( $config_dir . 'images.config.php' ) ) {
				$imagesConfig = include $config_dir . 'images.config.php';
			}
			if ( file_exists( $config_dir . 'sidebars.config.php' ) ) {
				$sidebarsConfig = include $config_dir . 'sidebars.config.php';
			}
			if ( file_exists( $config_dir . 'posts.config.php' ) ) {
				$postsConfig = include $config_dir . 'posts.config.php';
			}
			if ( file_exists( $config_dir . 'menu.config.php' ) ) {
				$menuConfig = include $config_dir . 'menu.config.php';
			}
			if ( file_exists( $config_dir . 'assets.config.php' ) ) {
				$assetsConfig = include $config_dir . 'assets.config.php';
			}
			if ( file_exists( $config_dir . 'main.config.php' ) ) {
				$mainConfig = include $config_dir . 'main.config.php';
			}
			if ( file_exists( $config_dir . 'modules.config.php' ) ) {
                $modulesConfig = include $config_dir . 'modules.config.php';
			}

			$this->configuration = array_merge( $this->configuration, $mainConfig, $imagesConfig, $postsConfig, $menuConfig, $assetsConfig, $sidebarsConfig, $modulesConfig );

            if(!empty($this->configuration['modules']['views_path'])) {
                $this->view_path = $this->configuration['modules']['views_path'];
            } else {
                $this->view_path = get_stylesheet_directory() . '/src/views';
            }
		}
	}
    public function get_configuration() {
        return $this->configuration;
    }

    public function get_view_path() {
        return $this->view_path;
    }

	/**
	 * Removes admin bar from front view
	 */
	public function remove_admin_bar() {
		show_admin_bar( false );
	}

	/**
	 * Calls security rules for wordpress.
	 */
	protected function init_security() {
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'style_loader_src', [ $this, 'remove_version_scripts' ], 9999 );
		add_filter( 'script_loader_src', [ $this, 'remove_version_scripts' ], 9999 );
	}

	/**
	 * Removes version from query string loading styles/scripts
	 *
	 * @param string $src Query string from style/script.
	 *
	 * @return string
	 */
	public function remove_version_scripts( $src ) {
        if(!empty($this->configuration['assets']['versions'])) {
            if (in_array($src, $this->configuration['assets']['versions'])) return $src;
        }
		$src = remove_query_arg( 'ver', $src );

		return $src;
	}

	/**
	 * Inits filters for content and titles display
	 */
	protected function init_content_filters() {

		add_filter( 'wp_title', [ $this, 'custom_title' ] );
		add_filter( 'widget_title', [ $this, 'custom_widget_title' ] );
		add_filter( "the_content", [ $this, 'the_content_filter' ] );
		remove_filter( 'widget_title', 'esc_html' );
	}

	/**
	 * Changes the meta title of page.
	 *
	 * @param string $title Title to display.
	 *
	 * @return string
	 */
	public function custom_title( $title ) {
		$ret = get_bloginfo( 'name', 'display' );
		if ( empty( $title ) && ( is_home() || is_front_page() ) ) {
			return $ret;
		}

		return $ret . " :: " . $title;
	}

	/**
	 * Removes html from widget title, keeping few tags.
	 *
	 * @param string $title Widget title.
	 *
	 * @return mixed|string
	 */
	public function custom_widget_title( $title ) {
		$title = str_replace( '[', '<', $title );
		$title = str_replace( ']', '>', $title );
		$title = strip_tags( $title, '<a><blink><br><span><large>' );

		return $title;
	}

	/**
	 * Filters the content to prevent adding "p" tag to special shortcodes
	 *
	 * @param string $content Content to display.
	 *
	 * @return null|string|string[]
	 */
	public function the_content_filter( $content ) {
		$shortcodes = array();
		if ( array_key_exists( 'filters', $this->configuration ) && array_key_exists( 'content', $this->configuration['filters'] ) ) {
			$shortcodes = $this->configuration['filters']['content'];
		}
		$block = join( "|", $shortcodes );
		$rep   = preg_replace( "/(<p>)?\[($block)(\s[^\]]+)?\](<\/p>|<br \/>)?/", "[$2$3]", $content );
		$rep   = preg_replace( "/(<p>)?\[\/($block)](<\/p>|<br \/>)?/", "[/$2]", $rep );

		return $rep;
	}

	/**
	 * Initialize front end site filters and actions
	 */
	protected function init_front_site() {
		if ( array_key_exists( 'menu', $this->configuration ) ) {
			foreach ( $this->configuration['menu'] as $key => $menu ) {
				register_nav_menu( $key, $menu['name'] );
			}
		}

		add_action( 'widgets_init', [ $this, 'init_widgets' ] );

		add_action( 'init', [ $this, 'init_sidebars' ] );
		add_action( 'init', [ $this, 'init_custom_posts_and_taxonomies' ] );
		add_action( 'init', [ $this, 'init_custom_image_sizes' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'init_styles_and_scripts' ] );

	}

	/**
	 * Initializes widgets. Namespace got from configuration file: sidebars.config.php
	 */
	public function init_widgets() {
        if(!empty($this->configuration['modules']['widget'])) {
            foreach($this->configuration['modules']['widget'] as $widget) {
                if(class_exists($widget)){
                    register_widget($widget);
                }
            }
        }
	}

	/**
	 * Initialize sidebars based on configuration file: sidebars.config.php
	 */
	public function init_sidebars() {
		$sidebars = array();
		if ( array_key_exists( 'sidebars', $this->configuration ) ) {
			$sidebars = $this->configuration['sidebars'];
		}
		$args = array(
			'before_widget' => '<div class="sidebar-content widget widget-%2$s" id="%1$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3>',
			'after_title'   => '</h3>',
		);
		foreach ( $sidebars as $sidebar ) {
			$real_args         = $args;
			$real_args['id']   = $sidebar['id'];
			$real_args['name'] = __( $sidebar['name'], 'netivo' );
			if ( isset( $sidebar['before_widget'] ) ) {
				$real_args['before_widget'] = $sidebar['before_widget'];
			}
			if ( isset( $sidebar['after_widget'] ) ) {
				$real_args['after_widget'] = $sidebar['after_widget'];
			}
			if ( isset( $sidebar['before_title'] ) ) {
				$real_args['before_title'] = $sidebar['before_title'];
			}
			if ( isset( $sidebar['after_title'] ) ) {
				$real_args['after_title'] = $sidebar['after_title'];
			}
			register_sidebar( $real_args );
		}
	}

	/**
	 * Initializes custom posts and taxonomies based on configuration file: posts.config.php
	 */
	public function init_custom_posts_and_taxonomies() {
		$customPosts = array();
		if ( array_key_exists( 'posts', $this->configuration ) ) {
			$customPosts = $this->configuration['posts'];
		}


		$customTaxonomies = array();
		if ( array_key_exists( 'taxonomies', $this->configuration ) ) {
			$customTaxonomies = $this->configuration['taxonomies'];
		}


		foreach ( $customPosts as $id => $customPost ) {
			if ( ! in_array( $id, [ 'post', 'page' ] ) ) {
				register_post_type( $id, $customPost );
				if ( ! empty( $customPost['capabilities'] ) ) {
					$role = get_role( 'administrator' );
					foreach ( $customPost['capabilities'] as $capability ) {
						$role->add_cap( $capability );
					}
				}
			} else {
				global $wp_post_types;
				$labels = &$wp_post_types[ $id ]->labels;
				if ( isset( $customPost['labels']['name'] ) ) {
					$labels->name = $customPost['labels']['name'];
				}
				if ( isset( $customPost['labels']['singular_name'] ) ) {
					$labels->singular_name = $customPost['labels']['singular_name'];
				}
				if ( isset( $customPost['labels']['add_new'] ) ) {
					$labels->add_new = $customPost['labels']['add_new'];
				}
				if ( isset( $customPost['labels']['add_new_item'] ) ) {
					$labels->add_new_item = $customPost['labels']['add_new_item'];
				}
				if ( isset( $customPost['labels']['edit_item'] ) ) {
					$labels->edit_item = $customPost['labels']['edit_item'];
				}
				if ( isset( $customPost['labels']['new_item'] ) ) {
					$labels->new_item = $customPost['labels']['new_item'];
				}
				if ( isset( $customPost['labels']['view_item'] ) ) {
					$labels->view_item = $customPost['labels']['view_item'];
				}
				if ( isset( $customPost['labels']['search_items'] ) ) {
					$labels->search_items = $customPost['labels']['search_items'];
				}
				if ( isset( $customPost['labels']['not_found'] ) ) {
					$labels->not_found = $customPost['labels']['not_found'];
				}
				if ( isset( $customPost['labels']['not_found_in_trash'] ) ) {
					$labels->not_found_in_trash = $customPost['labels']['not_found_in_trash'];
				}
				if ( isset( $customPost['labels']['all_items'] ) ) {
					$labels->all_items = $customPost['labels']['all_items'];
				}
				if ( isset( $customPost['labels']['menu_name'] ) ) {
					$labels->menu_name = $customPost['labels']['menu_name'];
				}
				if ( isset( $customPost['labels']['name_admin_bar'] ) ) {
					$labels->name_admin_bar = $customPost['labels']['name_admin_bar'];
				}
			}
		}

		foreach ( $customTaxonomies as $id => $customTaxonomy ) {
			register_taxonomy( $id, $customTaxonomy['post'], $customTaxonomy['options'] );
		}
	}

	/**
	 * Initialize styles and scripts based on configuration file: assets.config.php
	 */
	public function init_styles_and_scripts() {
		if ( array_key_exists( 'assets', $this->configuration ) ) {
			$js  = $this->configuration['assets']['js'];
			$css = $this->configuration['assets']['css'];
            $this->configuration['assets']['versions'] = [];
			foreach ( $css as $st ) {
                $loading_dir = '';
				if ( file_exists( get_stylesheet_directory() . $st['file'] ) ) {
                    $loading_dir = get_stylesheet_directory_uri();
				} else if ( file_exists( get_template_directory() . $st['file'] ) ) {
					$loading_dir = get_template_directory_uri();
				}
                if(!empty($loading_dir)) {
                    if (!empty($st['condition']) && is_callable($st['condition'])) {
                        if ($st['condition']()) {
                            wp_enqueue_style($st['name'], $loading_dir . $st['file'], array(), ((!empty($sc['version'])) ? $sc['version'] : null), ((!empty($sc['media'])) ? $sc['media'] : 'all'));
                            if(!empty($sc['version'])) $this->configuration['assets']['versions'][] = $loading_dir . $st['file'];
                        }
                    } else {
                        wp_enqueue_style($st['name'], $loading_dir . $st['file'], array(), ((!empty($sc['version'])) ? $sc['version'] : null), ((!empty($sc['media'])) ? $sc['media'] : 'all'));
                        if(!empty($sc['version'])) $this->configuration['assets']['versions'][] = $loading_dir . $st['file'];
                    }
                }
			}
			foreach ( $js as $sc ) {
                $loading_dir = '';
                if ( file_exists( get_stylesheet_directory() . $sc['file'] ) ) {
                    $loading_dir = get_stylesheet_directory_uri();
                } else if ( file_exists( get_template_directory() . $sc['file'] ) ) {
                    $loading_dir = get_template_directory_uri();
                }
                if(!empty($loading_dir)) {
                    if (!empty($st['condition']) && is_callable($st['condition'])) {
                        if ($st['condition']()) {
                            wp_enqueue_script( $sc['name'], $loading_dir . $sc['file'], array(), ((!empty($sc['version'])) ? $sc['version'] : null), true );
                            if(!empty($sc['version'])) $this->configuration['assets']['versions'][] = $loading_dir . $sc['file'];
                        }
                    } else {
                        wp_enqueue_script( $sc['name'], $loading_dir . $sc['file'], array(), ((!empty($sc['version'])) ? $sc['version'] : null), true );
                        if(!empty($sc['version'])) $this->configuration['assets']['versions'][] = $loading_dir . $sc['file'];
                    }
                }
			}
		}
	}

	/**
	 * Initialize custom image sizes defined in configuration file: images.config.php
	 */
	public function init_custom_image_sizes() {
		$imageSizes = array();
		if ( array_key_exists( 'image', $this->configuration ) ) {
			$imageSizes = $this->configuration['image'];
		}
		foreach ( $imageSizes as $size ) {
			add_image_size( $size['name'], $size['width'], $size['height'], $size['crop'] );
		}
	}

    /**
     * Initialize WP Customizer settings defined in modules.config.php
     *
     * @return void
     */
    public function init_customizer() {
        if(!empty($this->configuration['modules']['customizer'])) {
            foreach($this->configuration['modules']['customizer'] as $customizer) {
                if(class_exists($customizer)){
                    new $customizer();
                }
            }
        }
    }

	/**
	 * Initializes woocommerce functions.
	 */
	protected function init_woocommerce(){
		add_action( 'after_setup_theme', [$this, 'enable_woocommerce_support'] );
		if ( ! empty( self::$woocommerce_panel ) && class_exists( self::$woocommerce_panel ) ) {
			$name = self::$woocommerce_panel;
			new $name();
		}
	}

	/**
	 * Add theme support for woocommerce.
	 */
	public function enable_woocommerce_support(){
		add_theme_support( 'woocommerce' );
	}

	/**
	 * Initialize administrator site filters and actions
	 */
	protected function init_admin_site() {
		if ( ! empty( self::$admin_panel ) && class_exists( self::$admin_panel ) ) {
			$name = self::$admin_panel;
			new $name($this);
		}
	}
    protected abstract function init();
}