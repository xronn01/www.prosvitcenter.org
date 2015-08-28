<?php
/*
    Checks for incompatible plugins and settings - Maps Marker Pro
*/
//info prevent file from being accessed directly
if (basename($_SERVER['SCRIPT_FILENAME']) == 'compatibility-checks.php') { die ("Please do not access this file directly. Thanks!<br/><a href='https://www.mapsmarker.com/go'>www.mapsmarker.com</a>"); }
require_once(ABSPATH . WPINC . DIRECTORY_SEPARATOR . "pluggable.php");
$lmm_options = get_option( 'leafletmapsmarker_options' ); //info: required for bing maps api key check

$check_license_key = get_option('leafletmapsmarkerpro_license_key');
//info: show admin notice if no license key is entered
if ($check_license_key == NULL) {
	$current_page = isset($_GET['page']) ? $_GET['page'] : '';
	if ($current_page != 'leafletmapsmarker_license') {
		echo '<p><div class="updated" style="padding:5px;"><table><tr><td><a href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_license"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/logo-mapsmarker-pro.png" width="50" height="50" /></a></td><td><a href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_license"">' . __('Please click here to finish the installation of Maps Marker Pro','lmm') . '</a></td></tr></table></div></p>';
	}
}
//info: check if bing maps api key is defined
if (( (($lmm_options['standard_basemap'] == 'bingaerial') || ($lmm_options['standard_basemap'] == 'bingaerialwithlabels') || ($lmm_options['standard_basemap'] == 'bingroad'))
|| ((isset($lmm_options[ 'controlbox_bingaerial' ]) == TRUE ) && ($lmm_options[ 'controlbox_bingaerial' ] == 1 ))
|| ((isset($lmm_options[ 'controlbox_bingaerialwithlabels' ]) == TRUE ) && ($lmm_options[ 'controlbox_bingaerialwithlabels' ] == 1 ))
|| ((isset($lmm_options[ 'controlbox_bingroad' ]) == TRUE ) && ($lmm_options[ 'controlbox_bingroad' ] == 1 ))
) && ( isset($lmm_options['bingmaps_api_key']) && ($lmm_options['bingmaps_api_key'] == NULL )
)) {
	echo '<p><div class="error" style="padding:10px;"><strong>' . __('Warning: you enabled support for bing maps but did not provide an API key. Please visit <a href="http://www.mapsmarker.com/bing-maps" target="_blank">http://www.mapsmarker.com/bing-maps</a> for info on how to get a free bing maps API key!','lmm') . '</strong></div></p>';
}
//info: plugin JavaScript to Footer
if (is_plugin_active('footer-javascript/footer-javascript.php') ) {
	echo '<p><div class="error" style="padding:10px;"><strong>' . __('Warning: you are using the plugin Javascript to Footer which is incompatible with Leaflet Maps Marker and causing maps to break. Please deactivate this plugin in order to be able to use Leaflet Maps Marker.','lmm') . '</strong></div></p>';
}
//info: plugin jQuery Colorbox
if (is_plugin_active('jquery-colorbox/jquery-colorbox.php') ) {
	$lmm_jquery_colorbox_options = get_option( 'jquery-colorbox_settings' );
	if ($lmm_jquery_colorbox_options['autoColorbox'] == TRUE) {
		echo '<p><div class="error" style="padding:10px;">' . __('<strong>Warning: you are using the plugin jQuery Colorbox which is causing maps to break!</strong><br/><br/>Here is how to fix this:<br/>1. click on to "Settings" / "jQuery Colorbox" in your WordPress admin menu<br/>2. Uncheck the setting "Automate jQuery Colorbox for all images in pages, posts and galleries:"<br/>3. check the setting "Automate jQuery Colorbox for images in WordPress galleries only:" instead<br/>4. save changes<br/><br/>This message will disappear automatically when the jQuery Colorbox option was updated.','lmm') . '</div></p>';
	}
}
//info: plugin cformsII
if (is_plugin_active('cforms/cforms.php') ) {
	$lmm_cforms_options = get_option( 'cforms_settings' );
	if ($lmm_cforms_options['global'][ 'cforms_show_quicktag_js' ] == FALSE) {
		echo '<p><div class="error" style="padding:10px;">' . __('<strong>Warning: you are using the plugin cformsII which is causing the TinyMCE editor to break when creating new maps!</strong><br/><br/>Here is how to fix this:<br/>1. click on to "cformsII" / "Global Settings" in your WordPress admin menu<br/>2. open the tab "WP Editor Button support"<br/>3. check the option "Fix TinyMCE error"<br/>4. save changes<br/><br/>If you do not see this option in your settings, please upgrade to the latest version first (this has to be done manually - see plugin website http://www.deliciousdays.com/cforms-plugin/ for details)<br/><br/>This message will disappear automatically when the cformsII option "Fix TinyMCE error" is checked.','lmm') . '</div></p>';
	}
}
//info: plugin WP Google Analytics
if (is_plugin_active('wp-google-analytics/wp-google-analytics.php') ) {
	echo '<p><div class="error" style="padding:10px;"><strong>' . __('Warning: you are using the outdated plugin WP Google Analytics which is incompatible with Leaflet Maps Marker. Please update to a more current Google analytics plugin like http://wordpress.org/extend/plugins/google-analytics-for-wordpress/','lmm') . '</strong></div></p>';
}
//info: plugin Better WordPress Minify
if (is_plugin_active('bwp-minify/bwp-minify.php') ) {
	$lmm_bwpminify_options = get_option( 'bwp_minify_general' );
	if ($lmm_bwpminify_options['enable_min_js'] == 'yes') {
		if ((strpos($lmm_bwpminify_options['input_ignore'], 'leafletmapsmarker-core') === false) || (strpos($lmm_bwpminify_options['input_ignore'], 'leafletmapsmarker-addons') === false))  {
			echo '<p><div class="error" style="padding:10px;">' . sprintf(__('Warning: you are using the plugin "Better WordPress Minify" which can cause Leaflet Maps Marker to break if the option "Minify JS files automatically?" is active. Please disable this option (BWP Minify / General Settings) or navigate to BWP Minify / "Manage Enqueued Files", click on "Scripts to be ignored (not minified)" and add %1$s (one line for each)','lmm'), '<strong>leafletmapsmarker-core</strong> & <strong>leafletmapsmarker-addons</strong>') . '</div></p>';
		}
	}
}
//info: plugin WP Minify
if (is_plugin_active('wp-minify/wp-minify.php') ) {
	$lmm_bwpminify_options = get_option( 'wp_minify' );
	if ($lmm_bwpminify_options['enable_html'] == '1') {
			echo '<p><div class="error" style="padding:10px;"><strong>' . __('Warning: you are using the plugin "WP Minify" which is causing Leaflet Maps Marker layer maps to break as the option "Enable HTML Minification" is active. Please disable this option under Settings / WP Minify.','lmm') . '</strong></div></p>';
	}
}
//info: plugin W3 Total Cache check for Minify & CDN
if (is_plugin_active('w3-total-cache/w3-total-cache.php') ) {
	$w3tc_config = w3_instance('W3_Config');
	$w3tc_minify = $w3tc_config->get_boolean('minify.enabled');
	if ($w3tc_minify == true) {
		$w3tc_js = $w3tc_config->get_boolean('minify.js.enable');
		if ($w3tc_js == true) {
			$w3tc_js_exclude = $w3tc_config->get_array('minify.reject.files.js');
			if ((in_array('wp-content/plugins/leaflet-maps-marker-pro/leaflet-dist/leaflet-core.js', $w3tc_js_exclude) == false) || (in_array('wp-content/plugins/leaflet-maps-marker-pro/leaflet-dist/leaflet-addons.js', $w3tc_js_exclude) == false)) {
				echo '<p><div class="error" style="padding:10px;"><strong>' . sprintf(__('Warning: you are using the plugin "W3 Total Cache" with the feature "JS Minify" enabled which is causing maps to break.<br/>To fix this, please navigate to <a href="%1s">Performance / Minify / Advanced</a> and add <strong>%2s</strong> and <strong>%3s</strong> to "Never minify the following JS files:"','lmm'), LEAFLET_WP_ADMIN_URL . 'admin.php?page=w3tc_minify', 'wp-content/plugins/leaflet-maps-marker-pro/leaflet-dist/leaflet-core.js', 'wp-content/plugins/leaflet-maps-marker-pro/leaflet-dist/leaflet-addons.js') . '</strong></div></p>';
			}
		}
	}
	$w3tc_cdn = $w3tc_config->get_boolean('cdn.enabled');
	$w3tc_version = $w3tc_config->get_string('version');
	if ($w3tc_cdn == true) {
		if (version_compare($w3tc_version,"0.9.3","<")){
			$w3tc_cdn_exclude = $w3tc_config->get_array('cdn.reject.files');
			//info: custom marker dir?
			if ( $lmm_options['defaults_marker_custom_icon_url_dir'] == 'no' ) {
				$marker_icon_url = 'wp-content/uploads/leaflet-maps-marker-icons/*';
			} else {
				$marker_icon_url = substr(parse_url(htmlspecialchars($lmm_options['defaults_marker_icon_url']), PHP_URL_PATH),1);
			}
			if (in_array($marker_icon_url, $w3tc_cdn_exclude) == false) {
				echo '<p><div class="error" style="padding:10px;"><strong>' . sprintf(__('Warning: you are using the plugin "W3 Total Cache" with the feature "CDN" enabled which is causing layer maps to break.<br/>To fix this, please navigate to <a href="%1s">Performance / CDN / Advanced</a> and add <strong>%2s</strong> to "Rejected files:"','lmm'), LEAFLET_WP_ADMIN_URL . 'admin.php?page=w3tc_cdn', $marker_icon_url) . '</strong></div></p>';
			}
		}
	}
}
//info: plugin Root Relative URLs
if (is_plugin_active('root-relative-urls/sb_root_relative_urls.php') ) {
	echo '<p><div class="error" style="padding:10px;"><strong>' . sprintf(__('Warning: the plugin %1$s is active and causing maps to break - please deactivate that plugin!','lmm'), '"Root Relative URLs"	') . '</strong></div></p>';
}
//info: active shortcodes for already deleted maps?
if (get_option( 'leafletmapsmarkerpro_deleted_maps_errors' ) !== FALSE) {
	echo '<p><div id="deleted_maps_errors" class="error" style="padding:10px;"><strong>' . sprintf(__('Warning: you are using Maps Marker Pro shortcodes with invalid map IDs! As a result instead of a map an <a href="%1$s" target="_blank">error image</a> will be shown!','lmm'), LEAFLET_PLUGIN_URL . 'inc/img/map-deleted-image.png') . '</strong><br/>' . sprintf(__('<a href="%1$s">Click here to show all affected URLs</a>','lmm'), LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_tools#deleted-maps-errors') . '</div></p>';
}
//info: Page Builder by SiteOrigin plugin incompatibility
if (is_plugin_active('siteorigin-panels/siteorigin-panels.php') ) {
	$pagebuilder_metadata = get_plugin_data(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'siteorigin-panels' . DIRECTORY_SEPARATOR . 'siteorigin-panels.php');
	if (version_compare($pagebuilder_metadata['Version'],"2.1","<")){
		if ($lmm_options['misc_javascript_header_footer_pro'] == 'footer') {
			echo '<p><div class="error" style="padding:10px;">' . sprintf(__('Warning: you are using the Plugin %1$s which is causing maps to break! To fix this, please navigate to <a href="%2$s">Settings / Misc / General Settings</a> and set the Option "Where to insert Javascript files on frontend?" to "header (+ inline javascript)".','lmm'), '"Page Builder by SiteOrigin"', LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-misc' ) . '</div></p>';
		}
	}
}
//info: plugin WP External Links
if (is_plugin_active('wp-external-links/wp-external-links.php') ) {
	$plugin_options = get_option('wp_external_links-main');
	if (!strpos($plugin_options['ignore'], 'mapsmarker.com')) { $ignore_list .= 'mapsmarker.com<br/>'; }
	if (!strpos($plugin_options['ignore'], 'leafletjs.com')) { $ignore_list .= 'leafletjs.com<br/>'; }
	if (!strpos($plugin_options['ignore'], 'mapicons.mapsmarker.com')) { $ignore_list .= 'mapicons.mapsmarker.com<br/>'; }
	if (!strpos($plugin_options['ignore'], 'visualead.com')) { $ignore_list .= 'visualead.com<br/>'; }
	if (!strpos($plugin_options['ignore'], 'openstreetmap.org')) { $ignore_list .= 'openstreetmap.org<br/>'; }
	if (!strpos($plugin_options['ignore'], 'mapquest.com')) { $ignore_list .= 'mapquest.com<br/>'; }
	if (!strpos($plugin_options['ignore'], 'data.wien.gv.at')) { $ignore_list .= 'data.wien.gv.at<br/>'; }
	if (!strpos($plugin_options['ignore'], 'stamen.com')) { $ignore_list .= 'stamen.com<br/>'; }
	if (!strpos($plugin_options['ignore'], 'creativecommons.org')) { $ignore_list .= 'creativecommons.org<br/>'; }
	if (!strpos($plugin_options['ignore'], 'mapbox.com')) { $ignore_list .= 'mapbox.com<br/>'; }
	if (!strpos($plugin_options['ignore'], 'thunderforest.com')) { $ignore_list .= 'thunderforest.com'; }
	if ($ignore_list != NULL) {
		echo '<p><div class="error" style="padding:10px;"><strong>' . sprintf(__('Warning: you are using the plugin "WP External Links" which is currently causing maps to break! Please navigate to "External Links" and add the following links to the option "Ignore links (URL) containing...": %1$s','lmm'), '</strong><br/>' . $ignore_list) . '</div></p>';
	}
}
//info: Sucuri Security (active "restrict wp-content access" breaks maps)
function lmm_file_lines( $filepath='' ){
        return @file( $filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
}
if (is_plugin_active('sucuri-scanner/sucuri.php') ) {
	$htaccess_upload = WP_CONTENT_DIR . '/.htaccess';
	if( !is_readable($htaccess_upload) ){
        $cp = 0;
    } else {
        $cp = 0;
        $fcontent = lmm_file_lines($htaccess_upload);
        foreach( $fcontent as $fline ){
            if( stripos($fline, 'deny from all') !== FALSE ){
                $cp = 1;
                break;
            }
        }
    }
	if ($cp == 1) {
		echo '<p><div class="error" style="padding:10px;">' . sprintf(__('Warning: you are using the plugin %1$s which is causing maps to break! To fix this, please navigate to <a href="%2$s">Sucuri Security / Hardening</a> and click on the button "Revert hardening" in the section "Restrict wp-content access".','lmm'), '"Sucuri Security"', LEAFLET_WP_ADMIN_URL . 'admin.php?page=sucuriscan_hardening' ) . '</div></p>';
	}
}
//info: check if custom mapbox basemaps without API access tokens are used
if ( ($lmm_options[ 'mapbox_user' ] != 'mapbox') && ($lmm_options[ 'mapbox_access_token' ] == NULL) ) {
	echo '<p><div class="error" style="padding:10px;">' . sprintf(__('Warning: Mapbox now requires to use a custom API access token for your custom MapBox %1$s basemap - <a href="%2$s">please update your settings!</a><br/>A tutorial on how to get a free Mapbox API access token can be found at %3$s','lmm'), '1', LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-basemaps-section1', '<a href="https://www.mapsmarker.com/mapbox" target="_blank">https://www.mapsmarker.com/mapbox</a>') . '</div></p>';
}
if ( ($lmm_options[ 'mapbox2_user' ] != 'mapbox') && ($lmm_options[ 'mapbox2_access_token' ] == NULL) ) {
	echo '<p><div class="error" style="padding:10px;">' . sprintf(__('Warning: Mapbox now requires to use a custom API access token for your custom MapBox %1$s basemap - <a href="%2$s">please update your settings!</a><br/>A tutorial on how to get a free Mapbox API access token can be found at %3$s','lmm'), '2', LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-basemaps-section2', '<a href="https://www.mapsmarker.com/mapbox" target="_blank">https://www.mapsmarker.com/mapbox</a>') . '</div></p>';
}
if ( ($lmm_options[ 'mapbox3_user' ] != 'mapbox') && ($lmm_options[ 'mapbox3_access_token' ] == NULL) ) {
	echo '<p><div class="error" style="padding:10px;">' . sprintf(__('Warning: Mapbox now requires to use a custom API access token for your custom MapBox %1$s basemap - <a href="%2$s">please update your settings!</a><br/>A tutorial on how to get a free Mapbox API access token can be found at %3$s','lmm'), '3', LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-basemaps-section3', '<a href="https://www.mapsmarker.com/mapbox" target="_blank">https://www.mapsmarker.com/mapbox</a>') . '</div></p>';
}
//info: plugin Autoptimize
if (is_plugin_active('autoptimize/autoptimize.php') ) {
	if ( (get_option( 'autoptimize_js_forcehead' ) != 'on') || (strpos(get_option( 'autoptimize_js_exclude'), 'mapsmarkerjs') === false) || (strpos(get_option( 'autoptimize_js_exclude'), 'leaflet') === false)) {
		echo '<p><div class="error" style="padding:10px;">' . sprintf(__('Warning: you are using the plugin "Autoptimize" which is currently causing maps to break!<br/>To fix this, please navigate to <a href="%1$s">Autoptimize settings</a>, tick the checkbox "Force JavaScript in <head>?" and add the following to the end the option "Exclude scripts from Autoptimize:": %2$s','lmm'), LEAFLET_WP_ADMIN_URL . 'options-general.php?page=autoptimize', '<strong>,leaflet,mapsmarkerjs</strong>') . '</div></p>';
	}
}
?>