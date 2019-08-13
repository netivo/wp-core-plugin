<?php
/**
 * Plugin Name: Netivo Core Elements
 * Plugin URI: http://netivo.pl
 * Description: Netivo Core elements contains all needed classes and function to work with Netivo plugins or themes.
 * Version: 1.2.6
 * Author: Netivo <biuro@netivo.pl>
 * Author URI: http://netivo.pl
*/

if(!defined('ABSPATH')){
    header('HTTP/1.0 403 Forbidden');
    exit;
}

define( 'NT_CORE_PLUGIN_FILE', __FILE__ );
define( 'NT_CORE_PLUGIN_PATH', WPMU_PLUGIN_DIR.'/nt-core/' );
define( 'NT_CORE_PLUGIN_URL', WPMU_PLUGIN_URL.'/nt-core/' );
define( 'NT_CORE_VERSION', '1.2.6' );

require_once WPMU_PLUGIN_DIR.'/nt-core/Netivo/Autoloader.php';
require_once WPMU_PLUGIN_DIR.'/nt-core/updater.php';

add_action('nt_core_updates', 'nt_core_updates');

if ( !wp_next_scheduled('nt_core_updates') ) {
    wp_schedule_event( current_time( 'timestamp' ), 'daily', 'nt_core_updates');
}
