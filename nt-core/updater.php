<?php
/**
 * Created by Netivo for Netivo Core Plugin
 * Creator: michal
 * Creation date: 02.07.2019 15:42
 */

function nt_core_updates() {
    $core_version = get_option('_nt_core_version', null);
    if(empty($core_version)) {
        update_option('_nt_core_version', NT_CORE_VERSION);
        $core_version = NT_CORE_VERSION;
    }

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
    $zipball = $release['zipball_url'];

    if($latest_version != $core_version){
        $download = curl_init();
        curl_setopt_array($download, array(
            CURLOPT_URL => $zipball,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => ['User-Agent: Wp-Core-Plugin']
        ));
        $data = curl_exec ($download);

        curl_close ($download);

        $destination = WP_CONTENT_DIR.'/upgrade/wp-core-plugin-'.$latest_version.'.zip';
        $file = fopen($destination, "w+");
        fputs($file, $data);
        fclose($file);
        $zip = new ZipArchive;
        if ($zip->open($destination) === TRUE) {
            $zip->extractTo(WP_CONTENT_DIR.'/upgrade/wp-core');
            $zip->close();
            $dirs = scandir(WP_CONTENT_DIR.'/upgrade/wp-core');
            foreach($dirs as $dir){
                if($dir != '.' && $dir != '..') $main_dir = $dir;
            }
            if(!empty($main_dir))
                recurse_copy(WP_CONTENT_DIR.'/upgrade/wp-core/'.$main_dir, WPMU_PLUGIN_DIR);
            rrmdir(WP_CONTENT_DIR.'/upgrade/wp-core');
            rrmdir($destination);

            update_option('_nt_core_version', $latest_version);

        }
    }
}

function rrmdir($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file)
            if ($file != "." && $file != "..") rrmdir("$dir/$file");
        rmdir($dir);
    }
    else if (file_exists($dir)) unlink($dir);
}
function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}