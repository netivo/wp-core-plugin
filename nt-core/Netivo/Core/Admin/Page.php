<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: Michal
 * Date: 10.10.2018
 * Time: 13:56
 *
 * @package Netivo\Admin
 */

namespace Netivo\Core\Admin;

use ReflectionClass;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

if ( ! class_exists( '\Netivo\Core\Admin\Page' ) ) {
	/**
	 * Class for admin Page
	 */
	abstract class Page {
		
		/**
		 * Paths to include child pages
		 *
		 * @var array
		 */
		public static $pages_path = [];
		
		/**
		 * Name of the page used as view name
		 *
		 * @var string
		 */
		protected $_name = '';
		
		/**
		 * View class for the page
		 *
		 * @var \Netivo\Core\Admin\View
		 */
		protected $view;
		
		/**
		 * Page type. One of: main, subpage, tab
		 * main - Main page will display in first level menu
		 * subpage - Sub page will display in second level menu, MUST have parent attribute
		 * tab - Tab for page, will not display in menu, MUST have parent attribute
		 *
		 * @var string
		 */
		protected $_type = 'main';
		
		/**
		 * The text to be displayed in the title tags of the page when the menu is selected.
		 *
		 * @var string
		 */
		protected $_page_title = '';
		
		/**
		 * The text to be used for the menu.
		 *
		 * @var string
		 */
		protected $_menu_text = '';
		/**
		 * The capability required for this menu to be displayed to the user.
		 *
		 * @var string
		 */
		protected $_capability = 'manage_options';
		/**
		 * The slug name to refer to this menu by. Should be unique for this menu page and only include lowercase alphanumeric, dashes, and underscores characters to be compatible with sanitize_key()
		 *
		 * @var string
		 */
		protected $_menu_slug = '';
		/**
		 * The URL to the icon to be used for this menu.
		 * Pass a base64-encoded SVG using a data URI, which will be colored to match the color scheme. This should begin with 'data:image/svg+xml;base64,'.
		 * Pass the name of a Dashicons helper class to use a font icon, e.g. 'dashicons-chart-pie'.
		 * Pass 'none' to leave div.wp-menu-image empty so an icon can be added via CSS.
		 *
		 * Ignored when subpage or tab
		 *
		 * @var string
		 */
		protected $_icon = '';
		/**
		 * The position in the menu order this one should appear.
		 *
		 * Ignored when subpage or tab
		 *
		 * @var null
		 */
		protected $_position = null;
		
		/**
		 * The slug name for the parent element (or the file name of a standard WordPress admin page).
		 * Needed when submenu or tab
		 *
		 * @var string
		 */
		protected $_parent = '';

        /**
         * List of Child classes
         *
         * @var array
         */
        protected $_children = array();

		/**
		 * Children of page
		 *
		 * @var array
		 */
		protected $_childrenObjects = array();
		
		/**
		 * Redirect url after saving
		 *
		 * @var string
		 */
		protected $_redirect_url = '';
		
		/**
		 * Path to admin
		 *
		 * @var string
		 */
		protected $_views_path = '';
		
		/**
		 * Page constructor.
		 *
		 * @param string $path Path to admin.
		 *
		 * @throws \ReflectionException When error searching children.
		 */
		public function __construct( $path, $children ) {
			$this->_views_path = $path;
			$this->generate_redirect();
			add_action( 'init', [ $this, 'do_save' ] );
			if ( $this->_type != 'tab' ) {
				add_action( 'admin_menu', [ $this, 'register_menu' ] );
				$this->register_children();
			}
			$this->view = new View( $this );
		}
		
		/**
		 * Register menu element in Admin
		 */
		public function register_menu() {
			if ( $this->_type == 'subpage' ) {
				add_submenu_page( $this->_parent, $this->_page_title, $this->_menu_text, $this->_capability, $this->_menu_slug, function () { $this->display(); } );
			} elseif ( $this->_type == 'main' ) {
				add_menu_page( $this->_page_title, $this->_menu_text, $this->_capability, $this->_menu_slug, function () { $this->display(); }, $this->_icon, $this->_position );
			}
		}
		
		/**
		 * Generate redirect link if empty
		 */
		protected function generate_redirect() {
			if ( empty( $this->_redirect_url ) ) {
				$rdu = 'admin.php?page=';
				if ( $this->_type == 'tab' ) {
					$rdu .= $this->_parent;
					$rdu .= '&tab=' . $this->_menu_slug;
				} else {
					$rdu .= $this->_menu_slug;
				}
				$this->_redirect_url = $rdu;
			}
		}
		
		/**
		 * Displays the view
		 */
		public function display() {
			$this->view->title = $this->_page_title;
			wp_enqueue_media();
			if ( ! $this->is_tab() && isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ) {
				$tab = $this->find_tab( $_GET['tab'] );
				if ( ! empty( $tab ) ) {
					$tab->display();
				} else {
					$this->do_action();
					$this->view->display();
				}
			} else {
				if ( $this->is_tab() ) {
					$this->view->tab = $this->_menu_slug;
				}
				$this->do_action();
				$this->view->display();
			}
		}
		
		/**
		 * Action done before displaying content
		 */
		abstract public function do_action();
		
		/**
		 * Save function, to be used in child class.
		 * Main data saving is done here.
		 */
		abstract public function save();
		
		/**
		 * Save main function, called on save
		 */
		public function do_save() {
			if ( ! $this->is_tab() && isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ) {
				$tab = $this->find_tab( $_GET['tab'] );
				if ( ! empty( $tab ) ) {
					$tab->do_save();
				} else {
					if ( $this->is_post() ) {
						try {
							$this->save();
							wp_redirect( admin_url( $this->_redirect_url . '&success' ) );
						} catch ( \Exception $e ) {
							wp_redirect( admin_url( $this->_redirect_url . '&error=' . $e->getMessage() ) );
						}
					}
				}
			} else {
				if ( $this->is_post() ) {
					try {
						$this->save();
						wp_redirect( admin_url( $this->_redirect_url . '&success' ) );
					} catch ( \Exception $e ) {
						wp_redirect( admin_url( $this->_redirect_url . '&error=' . $e->getMessage() ) );
					}
				}
			}
		}
		
		/**
		 * Checks if current page is saved.
		 *
		 * @return bool
		 */
		public function is_post() {
			if ( isset( $_POST[ 'save_' . $this->_menu_slug ] ) ) {
				return true;
			}
			
			return false;
		}
		
		/**
		 * Check if page is tab
		 *
		 * @return bool
		 */
		public function is_tab() {
			return $this->_type == 'tab';
		}
		
		/**
		 * Get page slug
		 *
		 * @return string
		 */
		public function get_slug() {
			return $this->_menu_slug;
		}
		
		/**
		 * Finds called tab
		 *
		 * @param string $tab Tab slug.
		 *
		 * @return mixed|null
		 */
		public function find_tab( $tab ) {
			foreach ( $this->_childrenObjects as $child ) {
				if ( $child->is_tab() ) {
					if ( $child->get_slug() == $tab ) {
						return $child;
					}
				}
			}
			
			return null;
		}
		
		/**
		 * Register all children of page
		 *
		 * @throws \ReflectionException When error.
		 */
		protected function register_children() {
            if(!empty($this->_children)) {
                foreach($this->_children as $page){
                    if(class_exists($page['class'])) {
                        $className = $page['class'];
                        $children = (!empty($page['children'])) ? $page['children'] : [];
                        new $className($this->_views_path, $children);
                    }
                }
            }
		}
		
		/**
		 * Get view path for current page.
		 *
		 * @throws \ReflectionException When error.
		 */
		public function get_view_file() {
            $obj = new ReflectionClass($this);
            $data = $obj->getAttributes();
            foreach($data as $attribute) {
                if($attribute->getName() == 'Netivo\Attributes\PageView') {
                    $name = $attribute->getArguments()[0];
                }
            }
            if(empty($name)){
                $filename = $obj->getFileName();
                $filename = str_replace( '.php', '', $filename );

                $name = basename($filename);
                $name = strtolower($name);
            }
			
			return $this->_views_path . '/admin/pages/' . $name . '.phtml';
		}

        /**
         * Gets the path to admin.
         *
         * @return string
         */
		public function get_views_path() {
		    return $this->_views_path;
        }
	}
}