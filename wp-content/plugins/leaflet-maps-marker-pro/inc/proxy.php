<?php
//info: construct path to wp-load.php
while(!is_file('wp-load.php')) {
	if(is_dir('..' . DIRECTORY_SEPARATOR)) chdir('..' . DIRECTORY_SEPARATOR);
	else die('Error: Could not construct path to wp-load.php - please check <a href="https://www.mapsmarker.com/path-error">https://www.mapsmarker.com/path-error</a> for more details');
}
include( 'wp-load.php' );
function hide_email($email) { $character_set = '+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz'; $key = str_shuffle($character_set); $cipher_text = ''; $id = 'e'.rand(1,999999999); for ($i=0;$i<strlen($email);$i+=1) $cipher_text.= $key[strpos($character_set,$email[$i])]; $script = 'var a="'.$key.'";var b=a.split("").sort().join("");var c="'.$cipher_text.'";var d="";'; $script.= 'for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));'; $script.= 'document.getElementById("'.$id.'").innerHTML="<a href=\\"mailto:"+d+"\\">"+d+"</a>"'; $script = "eval(\"".str_replace(array("\\",'"'),array("\\\\",'\"'), $script)."\")"; $script = '<script type="text/javascript">/*<![CDATA[*/'.$script.'/*]]>*/</script>'; return '<span id="'.$id.'">[javascript protected email address]</span>'.$script; }
//info: check if plugin is active (didnt use is_plugin_active() due to problems reported by users)
function lmm_is_plugin_active( $plugin ) {
	$active_plugins = get_option('active_plugins');
	$active_plugins = array_flip($active_plugins);
	if ( isset($active_plugins[$plugin]) || lmm_is_plugin_active_for_network( $plugin ) ) { return true; }
}
function lmm_is_plugin_active_for_network( $plugin ) {
	if ( !is_multisite() )
		return false;
	$plugins = get_site_option( 'active_sitewide_plugins');
	if ( isset($plugins[$plugin]) )
				return true;
	return false;
}
if (!lmm_is_plugin_active('leaflet-maps-marker-pro/leaflet-maps-marker.php') ) {
	echo sprintf(__('The plugin "Maps Marker Pro" is inactive on this site and therefore this API link is not working.<br/><br/>Please contact the site owner (%1s) who can activate this plugin again.','lmm'), hide_email(get_bloginfo('admin_email')) );
} else {

	//info: proxy sec check
	$referer_marker = LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_marker';
	$referer_layer = LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer';
	$transient_proxy_get = (isset($_GET['transient']) ? $_GET['transient'] : '');
	$transient_proxy = get_transient( 'leafletmapsmarkerpro_proxy_access' );
		 
	if ( ((strpos($_SERVER['HTTP_REFERER'], $referer_marker) === 0) || (strpos($_SERVER['HTTP_REFERER'], $referer_layer) === 0)) && ($transient_proxy !== FALSE) && ($transient_proxy_get == $transient_proxy) ) { 
		if (isset($_GET['url'])) {
			$gpx_content_raw = wp_remote_get( $_GET['url'], array( 'sslverify' => false, 'timeout' => 30 ) );	
			$gpx_content = str_replace("\xEF\xBB\xBF",'',$gpx_content_raw['body']);  //info: replace UTF8-BOM for Chrome - not sure if needed here
			header( 'Content-type: text/xml' );
			echo $gpx_content;
		}
	} else {
		die("".__('Security check failed - please call this function from the according admin page!','lmm').""); 
	}
}