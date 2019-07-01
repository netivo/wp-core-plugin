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
define( 'NT_CORE_PLUGIN_PATH', WPMU_PLUGIN_DIR.'/nt-core/' );
define( 'NT_CORE_PLUGIN_URL', WPMU_PLUGIN_URL.'/nt-core/' );
define( 'NT_CORE_VERSION', '1.0' );

require_once WPMU_PLUGIN_DIR.'/nt-core/Netivo/Autoloader.php';


$core_version = get_option('_nt_core_version', null);
if(empty($core_version)) update_option('_nt_core_version', NT_CORE_VERSION);

function nt_core_updates() {	
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://api.github.com/repos/netivo/wp-core-plugin/releases/latest',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => ['User-Agent: Wp-Core-Plugin']
	));
	
	$release = curl_exec($curl);
	$release = json_decode($release, true);
    $latest_version = $release['name'];
	
	if($latest_version != $core_version){
		
	}
}

//if ( !wp_next_scheduled('nt_core_updates') ) {
//    wp_schedule_event( current_time( 'timestamp' ), 'daily', 'nt_core_updates');
//}

nt_core_updates();
