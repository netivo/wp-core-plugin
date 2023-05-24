<?php
/**
 * Created by Netivo for Netivo Core Plugin
 * Creator: michal
 * Creation date: 04.07.2019 16:38
 */

namespace Netivo\Core\Plugin;


abstract class Main
{
    /**
     * Main theme instance, theme run once, allow for only one instance per run
     *
     * @var \Netivo\Core\Plugin\Main
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

    protected $plugin_dir = '';
    protected $plugin_uri = '';


    /**
     * Get class instance, allowed only one instance per run
     *
     * @param $plugin_dir
     * @param $plugin_uri
     * @return Netivo\Core\Plugin\Main
     */
    public static function get_instance($plugin_dir, $plugin_uri)
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class($plugin_dir, $plugin_uri);
        }
        return self::$instances[$class];
    }

    /**
     * Theme constructor inits all necessary data.
     * @param $plugin_dir
     * @param $plugin_uri
     */
    protected function __construct($plugin_dir, $plugin_uri)
    {
        $this->plugin_dir = $plugin_dir;
        $this->plugin_uri = $plugin_uri;
        $this->init_configuration();

        add_action( 'init', [ $this, 'init_custom_posts_and_taxonomies' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'init_styles_and_scripts' ] );

        $this->init_plugin();

        if (function_exists('WC')) {
            $this->init_woocommerce();
        }

        if (is_admin()) {
            $this->init_admin_site();
        }
    }

    /**
     * Disable clone from public.
     */
    protected function __clone()
    {
    }

    /**
     * Init configuration array from files
     */
    protected function init_configuration() {
        if ( file_exists( $this->plugin_dir . "/config/" ) ) {
            $postsConfig    = array();
            $taxConfig      = array();
            $assetsConfig   = array();
            $mainConfig     = array();

            $config_dir = $this->plugin_dir . "/config/";
            if ( file_exists( $config_dir . 'posts.config.php' ) ) {
                $postsConfig = include $config_dir . 'posts.config.php';
            }
            if ( file_exists( $config_dir . 'tax.config.php' ) ) {
                $taxConfig = include $config_dir . 'tax.config.php';
            }
            if ( file_exists( $config_dir . 'assets.config.php' ) ) {
                $assetsConfig = include $config_dir . 'assets.config.php';
            }
            if ( file_exists( $config_dir . 'main.config.php' ) ) {
                $mainConfig = include $config_dir . 'main.config.php';
            }

            $this->configuration = array_merge( $this->configuration, $mainConfig, $postsConfig, $taxConfig, $assetsConfig );
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
            foreach ( $css as $st ) {
                if ( file_exists( $this->plugin_dir . $st['file'] ) ) {
                    wp_enqueue_style( $st['name'], $this->plugin_uri . $st['file'] );
                }
            }
            foreach ( $js as $sc ) {
                if ( file_exists( $this->plugin_dir . $sc['file'] ) ) {
                    wp_enqueue_script( $sc['name'], $this->plugin_uri . $sc['file'], array(), null, true );
                }
            }
        }
    }
	
	public function get_configuration() {
		return $this->configuration;
	}

    /**
     * Initializes woocommerce functions.
     */
    protected function init_woocommerce()
    {
        if (!empty(self::$woocommerce_panel) && class_exists(self::$woocommerce_panel)) {
            $name = self::$woocommerce_panel;
            new $name();
        }
    }

    /**
     * Initialize administrator site filters and actions
     */
    protected function init_admin_site()
    {
        if (!empty(self::$admin_panel) && class_exists(self::$admin_panel)) {
            $name = self::$admin_panel;
            new $name();
        }

    }

    protected abstract function init_plugin();
}