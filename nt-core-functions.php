<?php
/**
 * Plugin Name: Netivo Core Elements
 * Plugin URI: http://netivo.pl
 * Description: Netivo Core elements contains all needed classes and function to work with Netivo plugins or themes.
 * Version: 1.0
 * Author: Netivo <biuro@netivo.pl>
 * Author URI: http://netivo.pl
*/

if(!defined('ABSPATH')){
    header('HTTP/1.0 403 Forbidden');
    exit;
}

define( 'NT_CORE_PLUGIN_FILE', __FILE__ );
define( 'NT_CORE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'NT_CORE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once 'Netivo/Autoloader.php';

do_action('nt-core-init');
