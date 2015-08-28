<?php
/*
    Edit layer - Maps Marker Pro
*/
//info prevent file from being accessed directly
if (basename($_SERVER['SCRIPT_FILENAME']) == 'leaflet-layer.php') { die ("Please do not access this file directly. Thanks!<br/><a href='https://www.mapsmarker.com/go'>www.mapsmarker.com</a>"); }
?>
<div class="wrap">
<?php include('inc' . DIRECTORY_SEPARATOR . 'admin-header.php'); ?>
<?php
global $wpdb, $current_user, $wp_version, $allowedtags, $locale;
$lmm_options = get_option( 'leafletmapsmarker_options' );
//info: set custom marker icon dir/url
if ( $lmm_options['defaults_marker_custom_icon_url_dir'] == 'no' ) {
	$defaults_marker_icon_url = LEAFLET_PLUGIN_ICONS_URL;
} else {
	$defaults_marker_icon_url = htmlspecialchars($lmm_options['defaults_marker_icon_url']);
}
//info: set marker shadow url
if ( $lmm_options['defaults_marker_icon_shadow_url_status'] == 'default' ) {
	if ( $lmm_options['defaults_marker_icon_shadow_url'] == NULL ) {
		$marker_shadow_url = '';
	} else {
		$marker_shadow_url = LEAFLET_PLUGIN_URL . 'leaflet-dist/images/marker-shadow.png';
	}
} else {
	$marker_shadow_url = htmlspecialchars($lmm_options['defaults_marker_icon_shadow_url']);
}

$current_editor = get_option( 'leafletmapsmarker_editor' );
$current_editor_css = ($current_editor == 'simplified') ? 'display:none;' : 'display:block';
$current_editor_css_inline = ($current_editor == 'simplified') ? 'display:none;' : 'display:inline';
$current_editor_css_audit = ($current_editor == 'simplified') ? 'display:none;' : '';

$markercount_toggle = isset($_GET['markercount_toggle']) ? $_GET['markercount_toggle'] : '';
//info: workaround - select shortcode on input focus doesnt work on iOS
if ( version_compare( $wp_version, '3.4', '>=' ) ) {
	 $is_ios = wp_is_mobile() && preg_match( '/iPad|iPod|iPhone/', $_SERVER['HTTP_USER_AGENT'] );
	 $shortcode_select = ( $is_ios ) ? '' : 'onfocus="this.select();" readonly="readonly"';
} else {
	 $shortcode_select = '';
}
//info: check gpx url for validity
function lmm_isValidURL( $url ) {
	if (preg_match("|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i", $url)) {
		return true;
	} else {
		return false;
	}
}
$table_name_markers = $wpdb->prefix.'leafletmapsmarker_markers';
$table_name_layers = $wpdb->prefix.'leafletmapsmarker_layers';
$layerlist = $wpdb->get_results('SELECT l.id as lid,l.name as lname FROM `'.$table_name_layers.'` as l WHERE l.multi_layer_map = 0 and l.id != 0', ARRAY_A);
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
$oid = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : '');
$lat_check = isset($_POST['layerviewlat']) ? $_POST['layerviewlat'] : (isset($_GET['layerviewlat']) ? $_GET['layerviewlat'] : '');
$lon_check = isset($_POST['layerviewlon']) ? $_POST['layerviewlon'] : (isset($_GET['layerviewlon']) ? $_GET['layerviewlon'] : '');
$layerid = isset($_GET['layerid']) ? $_GET['layerid'] : ''; //info: for switcheditor-js-forward
//info: functions for capability checks
function lmm_check_capability_edit($createdby) {
	global $current_user;
	$lmm_options = get_option( 'leafletmapsmarker_options' );
	if ( current_user_can( $lmm_options[ 'capabilities_edit_others' ]) ) {
		return true;
	}
	if ( current_user_can( $lmm_options[ 'capabilities_edit' ]) && ( $current_user->user_login == $createdby) ) {
		return true;
	}
	return false;
}
function lmm_check_capability_delete($createdby) {
	global $current_user;
	$lmm_options = get_option( 'leafletmapsmarker_options' );
	if ( current_user_can( $lmm_options[ 'capabilities_delete_others' ]) ) {
		return true;
	}
	if ( current_user_can( $lmm_options[ 'capabilities_delete' ]) && ( $current_user->user_login == $createdby) ) {
		return true;
	}
	return false;
}

if (!empty($action)) {
	$layernonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : (isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '');
	if (! wp_verify_nonce($layernonce, 'layer-nonce') ) { die('<br/>'.__('Security check failed - please call this function from the according admin page!','lmm').''); };
	
	//info: set active editor
	if (isset($_POST['active_editor'])) {
		if ($current_editor != $_POST['active_editor']) {
			if ( ($_POST['active_editor'] == 'simplified') || ($_POST['active_editor'] == 'advanced') ) { //info: only allow simplified & advanced
				update_option( 'leafletmapsmarker_editor', $_POST['active_editor'] );
			}
		}
	}
	
	if ($action == 'add') {
		if ( ($lat_check != NULL) && ($lon_check != NULL) ) {
			global $current_user;
			//info: set values for wms checkboxes status
			$wms_checkbox = isset($_POST['wms']) ? '1' : '0';
			$wms2_checkbox = isset($_POST['wms2']) ? '1' : '0';
			$wms3_checkbox = isset($_POST['wms3']) ? '1' : '0';
			$wms4_checkbox = isset($_POST['wms4']) ? '1' : '0';
			$wms5_checkbox = isset($_POST['wms5']) ? '1' : '0';
			$wms6_checkbox = isset($_POST['wms6']) ? '1' : '0';
			$wms7_checkbox = isset($_POST['wms7']) ? '1' : '0';
			$wms8_checkbox = isset($_POST['wms8']) ? '1' : '0';
			$wms9_checkbox = isset($_POST['wms9']) ? '1' : '0';
			$wms10_checkbox = isset($_POST['wms10']) ? '1' : '0';
			$clustering_checkbox = isset($_POST['clustering']) ? '1' : '0';
			$listmarkers_checkbox = isset($_POST['listmarkers']) ? '1' : '0';
			$panel_checkbox = isset($_POST['panel']) ? '1' : '0';
			$layername_quotes = str_replace("\\\\","/", str_replace("\"","'", $_POST['name'])); //info: backslash and double quotes break geojson
			$address = preg_replace("/(\\\\)(?!')/","/", preg_replace("/\t/", " ", $_POST['address'])); //info: tabs break geojson
			$multi_layer_map_checkbox = isset($_POST['multi_layer_map']) ? '1' : '0';
			$mlm_checked_imploded = isset($_POST['mlm-all']) ? 'all' : '';
			$gpx_panel_checkbox = isset($_POST['gpx_panel']) ? '1' : '0';
			if ($mlm_checked_imploded != 'all') {
				$mlm_checked_temp = '';
				foreach ($layerlist as $mlmrow){
					$mlm_checked{$mlmrow['lid']} = isset($_POST['mlm-'.$mlmrow['lid'].'']) ? $mlmrow['lid'].',' : '';
					$mlm_checked_temp .= $mlm_checked{$mlmrow['lid']};
				}
				$mlm_checked_imploded = substr($mlm_checked_temp, 0, -1);
			}
			$result = $wpdb->prepare( "INSERT INTO `$table_name_layers` (`name`, `basemap`, `layerzoom`, `mapwidth`, `mapwidthunit`, `mapheight`, `panel`, `layerviewlat`, `layerviewlon`, `createdby`, `createdon`, `updatedby`, `updatedon`, `controlbox`, `overlays_custom`, `overlays_custom2`, `overlays_custom3`, `overlays_custom4`, `wms`, `wms2`, `wms3`, `wms4`, `wms5`, `wms6`, `wms7`, `wms8`, `wms9`, `wms10`, `listmarkers`, `multi_layer_map`, `multi_layer_map_list`, `address`, `clustering`, `gpx_url`, `gpx_panel` ) VALUES (%s, %s, %d, %d, %s, %d, %d, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %d, %s, %d)", $layername_quotes, $_POST['basemap'], $_POST['layerzoom'], $_POST['mapwidth'], $_POST['mapwidthunit'], $_POST['mapheight'], $panel_checkbox, str_replace(',', '.', $_POST['layerviewlat']), str_replace(',', '.', $_POST['layerviewlon']), $current_user->user_login, current_time('mysql',0), $current_user->user_login, current_time('mysql',0), $_POST['controlbox'], $_POST['overlays_custom'], $_POST['overlays_custom2'], $_POST['overlays_custom3'], $_POST['overlays_custom4'], $wms_checkbox, $wms2_checkbox, $wms3_checkbox, $wms4_checkbox, $wms5_checkbox, $wms6_checkbox, $wms7_checkbox, $wms8_checkbox, $wms9_checkbox, $wms10_checkbox, $listmarkers_checkbox, $multi_layer_map_checkbox, $mlm_checked_imploded, $address, $clustering_checkbox, $_POST['gpx_url'], $gpx_panel_checkbox );
			$wpdb->query( $result );
			$wpdb->query( "OPTIMIZE TABLE `$table_name_layers`" );
			echo '<script> window.location="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id=' . $wpdb->insert_id . '&status=published&Layername=' . $layername_quotes . '"; </script> ';
		} else {
			echo '<p><div class="error" style="padding:10px;">' . __('Error: coordinates cannot be empty!','lmm') . '</div><br/><a href="javascript:history.back();" class=\'button-secondary lmm-nav-secondary\' >' . __('Go back to form','lmm') . '</a></p>';
		}
	} elseif ($action == 'edit') {
		$createdby_check = $wpdb->get_var( 'SELECT `createdby` FROM `'.$table_name_layers.'` WHERE id='.$oid );
		if (lmm_check_capability_edit($createdby_check) == TRUE) {
			if ( ($lat_check != NULL) && ($lon_check != NULL) ) {
				global $current_user;
				//info: set values for wms checkboxes status
				$wms_checkbox = isset($_POST['wms']) ? '1' : '0';
				$wms2_checkbox = isset($_POST['wms2']) ? '1' : '0';
				$wms3_checkbox = isset($_POST['wms3']) ? '1' : '0';
				$wms4_checkbox = isset($_POST['wms4']) ? '1' : '0';
				$wms5_checkbox = isset($_POST['wms5']) ? '1' : '0';
				$wms6_checkbox = isset($_POST['wms6']) ? '1' : '0';
				$wms7_checkbox = isset($_POST['wms7']) ? '1' : '0';
				$wms8_checkbox = isset($_POST['wms8']) ? '1' : '0';
				$wms9_checkbox = isset($_POST['wms9']) ? '1' : '0';
				$wms10_checkbox = isset($_POST['wms10']) ? '1' : '0';
				$clustering_checkbox = isset($_POST['clustering']) ? '1' : '0';
				$listmarkers_checkbox = isset($_POST['listmarkers']) ? '1' : '0';
				$panel_checkbox = isset($_POST['panel']) ? '1' : '0';
				$layername_quotes = str_replace("\\\\","/", str_replace("\"","'", $_POST['name'])); //info: backslash and double quotes break geojson
				$address = preg_replace("/(\\\\)(?!')/","/", preg_replace("/\t/", " ", $_POST['address'])); //info: tabs break geojson
				$multi_layer_map_checkbox = isset($_POST['multi_layer_map']) ? '1' : '0';
				$mlm_checked_imploded = isset($_POST['mlm-all']) ? 'all' : '';
				$gpx_panel_checkbox = isset($_POST['gpx_panel']) ? '1' : '0';
				if ($mlm_checked_imploded != 'all') {
					$mlm_checked_temp = '';
					foreach ($layerlist as $mlmrow){
						$mlm_checked{$mlmrow['lid']} = isset($_POST['mlm-'.$mlmrow['lid'].'']) ? $mlmrow['lid'].',' : '';
						$mlm_checked_temp .= $mlm_checked{$mlmrow['lid']};
					}
					$mlm_checked_imploded = substr($mlm_checked_temp, 0, -1);
				}
				$result = $wpdb->prepare( "UPDATE `$table_name_layers` SET `name` = %s, `basemap` = %s, `layerzoom` = %d, `mapwidth` = %d, `mapwidthunit` = %s, `mapheight` = %d, `panel` = %d, `layerviewlat` = %s, `layerviewlon` = %s, `createdby` = %s, `createdon` = %s, `updatedby` = %s, `updatedon` = %s, `controlbox` = %d, `overlays_custom` = %d, `overlays_custom2` = %d, `overlays_custom3` = %d, `overlays_custom4` = %d, `wms` = %d, `wms2` = %d, `wms3` = %d, `wms4` = %d, `wms5` = %d, `wms6` = %d, `wms7` = %d, `wms8` = %d, `wms9` = %d, `wms10` = %d, `listmarkers` = %d, `multi_layer_map` = %d, `multi_layer_map_list` = %s, `address` = %s, `clustering` = %d, `gpx_url` = %s, `gpx_panel` = %d WHERE `id` = %d", $layername_quotes, $_POST['basemap'], $_POST['layerzoom'], $_POST['mapwidth'], $_POST['mapwidthunit'], $_POST['mapheight'], $panel_checkbox, str_replace(',', '.', $_POST['layerviewlat']), str_replace(',', '.', $_POST['layerviewlon']), $_POST['createdby'], $_POST['createdon'], $current_user->user_login, current_time('mysql',0), $_POST['controlbox'], $_POST['overlays_custom'], $_POST['overlays_custom2'], $_POST['overlays_custom3'], $_POST['overlays_custom4'], $wms_checkbox, $wms2_checkbox, $wms3_checkbox, $wms4_checkbox, $wms5_checkbox, $wms6_checkbox, $wms7_checkbox, $wms8_checkbox, $wms9_checkbox, $wms10_checkbox, $listmarkers_checkbox, $multi_layer_map_checkbox, $mlm_checked_imploded, $address, $clustering_checkbox, $_POST['gpx_url'], $gpx_panel_checkbox, $oid );
				$wpdb->query( $result );
				$wpdb->query( "OPTIMIZE TABLE `$table_name_layers`" );
				echo '<script> window.location="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id=' . $oid . '&status=updated&Layername=' . urlencode($_POST['name']) . '"; </script> ';
			} else {
				echo '<p><div class="error" style="padding:10px;">' . __('Error: coordinates cannot be empty!','lmm') . '</div><br/><a href="javascript:history.back();" class=\'button-secondary lmm-nav-secondary\' >' . __('Go back to form','lmm') . '</a></p>';
			}
		} else {
			echo '<p><div class="error" style="padding:10px;">' . __('Error: your user does not have the permission to edit layers from other users!','lmm') . '</div><br/><a href="javascript:history.back();" class=\'button-secondary lmm-nav-secondary\' >' . __('Go back to form','lmm') . '</a></p>';
		}
	} elseif ($action == 'deleteboth') {
		$createdby_check = $wpdb->get_var( 'SELECT `createdby` FROM `'.$table_name_layers.'` WHERE `id`='.$oid );
		if (lmm_check_capability_delete($createdby_check) == TRUE) {
			//info: delete qr code cache images for assigned markers
			$layer_marker_list_qr = $wpdb->get_results('SELECT m.id as markerid,m.layer as mlayer,l.id as lid FROM `'.$table_name_layers.'` as l INNER JOIN '.$table_name_markers.' AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id=' . $oid, ARRAY_A);
			foreach ($layer_marker_list_qr as $row){
				if ( file_exists(LEAFLET_PLUGIN_QR_DIR . DIRECTORY_SEPARATOR . 'marker-' . $row['markerid'] . '.png') ) {
					unlink(LEAFLET_PLUGIN_QR_DIR . DIRECTORY_SEPARATOR . 'marker-' . $row['markerid'] . '.png');
				}
			}
			//info: delete qr code cache image for layer
			if ( file_exists(LEAFLET_PLUGIN_QR_DIR . DIRECTORY_SEPARATOR . 'layer-' . $oid . '.png') ) {
				unlink(LEAFLET_PLUGIN_QR_DIR . DIRECTORY_SEPARATOR . 'layer-' . $oid . '.png');
			}
			
			$markers_of_layer = $wpdb->get_results(" SELECT id,layer FROM  `$table_name_markers` WHERE layer LIKE '%\"".$oid."\"%' ");
			if(!empty($markers_of_layer)){
				foreach( $markers_of_layer as $marker ){
					$marker_layers = json_decode($marker->layer,true);
					if(count($marker_layers) == 1){
						$result = $wpdb->prepare( "DELETE FROM `$table_name_markers` WHERE `id` = %d", $marker->id );
						$wpdb->query( $result );
					}else{
						$layer_key = array_search($oid, $marker_layers);
						unset($marker_layers[$layer_key]);
						$new_layer = json_encode($marker_layers);
						$result = $wpdb->prepare( "UPDATE `$table_name_markers` SET `layer` = '".$new_layer."' WHERE `id` = %d", $marker->id );
						$wpdb->query( $result );
					}		
				}
			}
			$result2 = $wpdb->prepare( "DELETE FROM `$table_name_layers` WHERE `id` = %d", $oid );
			$wpdb->query( $result2 );

			$wpdb->query( "OPTIMIZE TABLE `$table_name_layers`" );
			echo '<p><div class="updated" style="padding:10px;">' . __('Layer and assigned markers have been successfully deleted (or the reference to the layer has been removed if marker was assigned to multiple layers)','lmm') . '</div><a class=\'button-secondary lmm-nav-secondary\' href=\'' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layers\'>' . __('list all layers','lmm') . '</a>&nbsp;&nbsp;&nbsp;<a class=\'button-secondary lmm-nav-secondary\' href=\'' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer\'>' . __('add new layer','lmm') . '</a></p>';
		} else {
			echo '<p><div class="error" style="padding:10px;">' . __('Error: your user does not have the permission to delete layers from other users!','lmm') . '</div><br/><a href="javascript:history.back();" class=\'button-secondary lmm-nav-secondary\' >' . __('Go back to form','lmm') . '</a></p>';
		}	
	} elseif ($action == 'delete') {
		$createdby_check = $wpdb->get_var( 'SELECT `createdby` FROM `'.$table_name_layers.'` WHERE `id`='.$oid );
		if (lmm_check_capability_delete($createdby_check) == TRUE) {
			//info: delete qr code cache image for layer
			if ( file_exists(LEAFLET_PLUGIN_QR_DIR . DIRECTORY_SEPARATOR . 'layer-' . $oid . '.png') ) {
				unlink(LEAFLET_PLUGIN_QR_DIR . DIRECTORY_SEPARATOR . 'layer-' . $oid . '.png');
			}
			$markers_of_layer = $wpdb->get_results(" SELECT id,layer FROM  `$table_name_markers` WHERE layer LIKE '%\"".$oid."\"%' ");
			if(!empty($markers_of_layer)){
				foreach( $markers_of_layer as $marker ){
					$marker_layers = json_decode($marker->layer,true);
					if(count($marker_layers) == 1){
						$new_layer = json_encode(array("0"));
					}else{
						$layer_key = array_search($oid, $marker_layers);
						unset($marker_layers[$layer_key]);
						$new_layer = json_encode($marker_layers);
					}

					$result = $wpdb->prepare( "UPDATE `$table_name_markers` SET `layer` = '".$new_layer."' WHERE `id` = %d", $marker->id );
					$wpdb->query( $result );
				}
			}
			$result2 = $wpdb->prepare( "DELETE FROM `$table_name_layers` WHERE `id` = %d", $oid );
			$wpdb->query( $result2 );
			$wpdb->query( "OPTIMIZE TABLE `$table_name_layers`" );
			echo '<div class="updated" style="padding:10px;">' . __('Layer has been successfully deleted (assigned markers have not been deleted)','lmm') . '</div><p><a class=\'button-secondary lmm-nav-secondary\' href=\'' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layers\'>' . __('list all layers','lmm') . '</a>&nbsp;&nbsp;&nbsp;<a class=\'button-secondary lmm-nav-secondary\' href=\'' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer\'>' . __('add new layer','lmm') . '</a></p>';
		} else {
			echo '<p><div class="error" style="padding:10px;">' . __('Error: your user does not have the permission to delete layers from other users!','lmm') . '</div><br/><a href="javascript:history.back();" class=\'button-secondary lmm-nav-secondary\' >' . __('Go back to form','lmm') . '</a></p>';
		}	
	} elseif ($action == 'duplicate') {	
		global $current_user;	
		$result = $wpdb->get_row( $wpdb->prepare('SELECT * FROM `'.$table_name_layers.'` WHERE `id` = %d',$oid), ARRAY_A);
		$sql_duplicate = $wpdb->prepare( "INSERT INTO `$table_name_layers` (`name`, `basemap`, `layerzoom`, `mapwidth`, `mapwidthunit`, `mapheight`, `panel`, `layerviewlat`, `layerviewlon`, `createdby`, `createdon`, `updatedby`, `updatedon`, `controlbox`, `overlays_custom`, `overlays_custom2`, `overlays_custom3`, `overlays_custom4`, `wms`, `wms2`, `wms3`, `wms4`, `wms5`, `wms6`, `wms7`, `wms8`, `wms9`, `wms10`, `listmarkers`, `multi_layer_map`, `multi_layer_map_list`, `address`, `clustering`, `gpx_url`, `gpx_panel`) VALUES (%s, %s, %d, %d, %s, %d, %d, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %d, %s, %d)", $result['name'], $result['basemap'], $result['layerzoom'], $result['mapwidth'], $result['mapwidthunit'], $result['mapheight'], $result['panel'], $result['layerviewlat'], $result['layerviewlon'], $current_user->user_login, current_time('mysql',0), $current_user->user_login, current_time('mysql',0), $result['conrolbox'], $result['overlays_custom'], $result['overlays_custom2'], $result['overlays_custom3'], $result['overlays_custom4'], $result['wms'], $result['wms2'], $result['wms3'], $result['wms4'], $result['wms5'], $result['wms6'], $result['wms7'], $result['wms8'], $result['wms9'], $result['wms10'], $result['listmarkers'], $result['multi_layer_map'], $result['multi_layer_map_list'], $result['address'], $result['clustering'], $result['gpx_url'], $result['gpx_panel'] );
		$wpdb->query( $sql_duplicate );
		$wpdb->query( "OPTIMIZE TABLE `$table_name_layers`" );
		echo '<script> window.location="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id=' . $wpdb->insert_id . '&status=duplicated"; </script> ';
	} 
} else { //info: !empty($action) 2/3
	$isedit = isset($_GET['id']);
	if (!$isedit) {
		$id = '';
		$name = '';
		$basemap = $lmm_options[ 'standard_basemap' ];
		$layerviewlat = floatval($lmm_options[ 'defaults_layer_lat' ]);
		$layerviewlon = floatval($lmm_options[ 'defaults_layer_lon' ]);
		$layerzoom = intval($lmm_options[ 'defaults_layer_zoom' ]);
		$mapwidth = intval($lmm_options[ 'defaults_layer_mapwidth' ]);
		$mapwidthunit = $lmm_options[ 'defaults_layer_mapwidthunit' ];
		$mapheight = intval($lmm_options[ 'defaults_layer_mapheight' ]);
		$panel = $lmm_options[ 'defaults_layer_panel' ];
		$lcreatedby = '';
		$lcreatedon = '';
		$lupdatedby = '';
		$lupdatedon = '';
		$lcontrolbox = $lmm_options[ 'defaults_layer_controlbox' ];
		$loverlays_custom = ( (isset($lmm_options[ 'defaults_layer_overlays_custom_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_overlays_custom_active' ] == 1 ) ) ? '1' : '0';
		$loverlays_custom2 = ( (isset($lmm_options[ 'defaults_layer_overlays_custom2_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_overlays_custom2_active' ] == 1 ) ) ? '1' : '0';
		$loverlays_custom3 = ( (isset($lmm_options[ 'defaults_layer_overlays_custom3_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_overlays_custom3_active' ] == 1 ) ) ? '1' : '0';
		$loverlays_custom4 = ( (isset($lmm_options[ 'defaults_layer_overlays_custom4_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_overlays_custom4_active' ] == 1 ) ) ? '1' : '0';
		$wms = ( (isset($lmm_options[ 'defaults_layer_wms_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_wms_active' ] == 1 ) ) ? '1' : '0';
		$wms2 = ( (isset($lmm_options[ 'defaults_layer_wms2_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_wms2_active' ] == 1 ) ) ? '1' : '0';
		$wms3 = ( (isset($lmm_options[ 'defaults_layer_wms3_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_wms3_active' ] == 1 ) ) ? '1' : '0';
		$wms4 = ( (isset($lmm_options[ 'defaults_layer_wms4_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_wms4_active' ] == 1 ) ) ? '1' : '0';
		$wms5 = ( (isset($lmm_options[ 'defaults_layer_wms5_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_wms5_active' ] == 1 ) ) ? '1' : '0';
		$wms6 = ( (isset($lmm_options[ 'defaults_layer_wms6_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_wms6_active' ] == 1 ) ) ? '1' : '0';
		$wms7 = ( (isset($lmm_options[ 'defaults_layer_wms7_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_wms7_active' ] == 1 ) ) ? '1' : '0';
		$wms8 = ( (isset($lmm_options[ 'defaults_layer_wms8_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_wms8_active' ] == 1 ) ) ? '1' : '0';
		$wms9 = ( (isset($lmm_options[ 'defaults_layer_wms9_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_wms9_active' ] == 1 ) ) ? '1' : '0';
		$wms10 = ( (isset($lmm_options[ 'defaults_layer_wms10_active' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_wms10_active' ] == 1 ) ) ? '1' : '0';
		$llistmarkers = $lmm_options[ 'defaults_layer_listmarkers' ];
		$multi_layer_map = 0;
		$multi_layer_map_list = array();
		$multi_layer_map_list_exploded = array();
		$laddress = '';
		$lclustering = ($lmm_options[ 'defaults_layer_clustering' ] == 'enabled' ) ? '1' : '0';
		$markercount = 0;
		$gpx_url = '';
		$gpx_panel = 0;
	} else {
		$id = intval($_GET['id']);
		$row = $wpdb->get_row('SELECT l.id as lid, l.name as lname, l.basemap as lbasemap, l.layerzoom as llayerzoom, l.mapwidth as lmapwidth, l.mapwidthunit as lmapwidthunit, l.mapheight as lmapheight, l.panel as lpanel, l.layerviewlat as llayerviewlat, l.layerviewlon as llayerviewlon, l.createdby as lcreatedby, l.createdon as lcreatedon, l.updatedby as lupdatedby, l.updatedon as lupdatedon, l.controlbox as lcontrolbox, l.overlays_custom as loverlays_custom, l.overlays_custom2 as loverlays_custom2, l.overlays_custom3 as loverlays_custom3, l.overlays_custom4 as loverlays_custom4,l.wms as lwms, l.wms2 as lwms2, l.wms3 as lwms3, l.wms4 as lwms4, l.wms5 as lwms5, l.wms6 as lwms6, l.wms7 as lwms7, l.wms8 as lwms8, l.wms9 as lwms9, l.wms10 as lwms10, l.listmarkers as llistmarkers, l.multi_layer_map as lmulti_layer_map, l.address as laddress, l.clustering as lclustering, l.gpx_url as lgpx_url, l.gpx_panel as lgpx_panel, m.id as markerid, m.markername as markername, m.lat as mlat, m.lon as mlon, m.icon as micon, m.popuptext as mpopuptext, m.zoom as mzoom, m.mapwidth as mmapwidth, m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight, m.address as maddress FROM `'.$table_name_layers.'` as l LEFT OUTER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id='.$id, ARRAY_A);
		$name = htmlspecialchars($row['lname']);
		$basemap = $row['lbasemap'];
		//info: fallback for existing maps if Google API is disabled
		if (($lmm_options['google_maps_api_status'] == 'disabled') && (($basemap == 'googleLayer_roadmap') || ($basemap == 'googleLayer_satellite') || ($basemap == 'googleLayer_hybrid') || ($basemap == 'googleLayer_terrain')) ) {
			$basemap = 'osm_mapnik';
		}
		$layerzoom = $row['llayerzoom'];
		$mapwidth = $row['lmapwidth'];
		$mapwidthunit = $row['lmapwidthunit'];
		$mapheight = $row['lmapheight'];
		$layerviewlat = $row['llayerviewlat'];
		$layerviewlon = $row['llayerviewlon'];
		$markerid = $row['markerid'];
		$markername = htmlspecialchars($row['markername']);
		$mlat = $row['mlat'];
		$mlon = $row['mlon'];
		$coords = $mlat.', '.$mlon;
		$micon = $row['micon'];
		$popuptext = $row['mpopuptext'];
		$markerzoom = $row['mzoom'];
		$markermapwidth = $row['mmapwidth'];
		$markermapwidthunit = $row['mmapwidthunit'];
		$markermapheight = $row['mmapheight'];
		$panel = $row['lpanel'];
		$lcreatedby = $row['lcreatedby'];
		$lcreatedon = $row['lcreatedon'];
		$lupdatedby = $row['lupdatedby'];
		$lupdatedon = $row['lupdatedon'];
		$lcontrolbox = $row['lcontrolbox'];
		$loverlays_custom = $row['loverlays_custom'];
		$loverlays_custom2 = $row['loverlays_custom2'];
		$loverlays_custom3 = $row['loverlays_custom3'];
		$loverlays_custom4 = $row['loverlays_custom4'];
		$wms = $row['lwms'];
		$wms2 = $row['lwms2'];
		$wms3 = $row['lwms3'];
		$wms4 = $row['lwms4'];
		$wms5 = $row['lwms5'];
		$wms6 = $row['lwms6'];
		$wms7 = $row['lwms7'];
		$wms8 = $row['lwms8'];
		$wms9 = $row['lwms9'];
		$wms10 = $row['lwms10'];
		$llistmarkers = $row['llistmarkers'];
		$multi_layer_map = $row['lmulti_layer_map'];
		$multi_layer_map_list = $wpdb->get_var('SELECT l.multi_layer_map_list FROM `'.$table_name_layers.'` as l WHERE l.id='.$id);
		$multi_layer_map_list_exploded = explode(",", $wpdb->get_var('SELECT l.multi_layer_map_list FROM `'.$table_name_layers.'` as l WHERE l.id='.$id));
		$laddress = htmlspecialchars($row['laddress']);
		$lclustering = $row['lclustering'];
		$gpx_url = $row['lgpx_url'];
		$gpx_panel = $row['lgpx_panel'];

		//info: markercount
		if ($multi_layer_map == 0) {
			$markercount = $wpdb->get_var('SELECT count(*) FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id='.$id);
		} else 	if ( ($multi_layer_map == 1) && ( $multi_layer_map_list == 'all' ) ) {
			$markercount = intval($wpdb->get_var('SELECT COUNT(*) FROM '.$table_name_markers));
		} else 	if ( ($multi_layer_map == 1) && ( $multi_layer_map_list != NULL ) && ($multi_layer_map_list != 'all') ) {
			foreach ($multi_layer_map_list_exploded as $mlmrowcount){
				$mlm_count_temp{$mlmrowcount} = $wpdb->get_var('SELECT count(*) FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id='.$mlmrowcount);
			}
			$markercount = array_sum($mlm_count_temp);
		} else 	if ( ($multi_layer_map == 1) && ( $multi_layer_map_list == NULL ) ) {
			$markercount = 0;
		}
	}

	//info: sqls for singe and multi-layer-maps
	if ($id == NULL) { //info: no mysql-query on new layer creation
		$layer_marker_list = NULL;
		$layer_marker_list_table = NULL;
	} else if ($multi_layer_map == 0) {
		//info: overwrite where statement for new layer maps (otherwise debug error sql statements $layer_marker_list and $layer_marker_list_table
		if ($id == '') { $sql_where = ''; } else { $sql_where = 'WHERE l.id=' . $id; }
		$layer_marker_list = $wpdb->get_results('SELECT l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') ' . $sql_where . ' ORDER BY ' . $lmm_options[ 'defaults_layer_listmarkers_order_by' ] . ' ' . $lmm_options[ 'defaults_layer_listmarkers_sort_order' ] . ' LIMIT ' . intval($lmm_options[ 'defaults_layer_listmarkers_limit' ]), ARRAY_A);
		
		$layer_marker_list_table = $wpdb->get_results('SELECT l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') ' . $sql_where . ' ORDER BY ' . $lmm_options[ 'defaults_layer_listmarkers_order_by' ] . ' ' . $lmm_options[ 'defaults_layer_listmarkers_sort_order' ] . ' LIMIT ' . intval($lmm_options[ 'defaults_layer_listmarkers_limit' ]), ARRAY_A);
		
	} else if ($multi_layer_map == 1) {

			//info: set sort order for multi-layer-maps based on list-marker-setting
			if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.id') {
				$sort_order_mlm = 'markerid';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.markername') {
				$sort_order_mlm = 'markername';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.popuptext') {
				$sort_order_mlm = 'mpopuptext';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.icon') {
				$sort_order_mlm = 'micon';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.createdby') {
				$sort_order_mlm = 'mcreatedby';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.createdon') {
				$sort_order_mlm = 'mcreatedon';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.updatedby') {
				$sort_order_mlm = 'mupdatedby';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.updatedon') {
				$sort_order_mlm = 'mupdatedon';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.layer') {
				$sort_order_mlm = 'mlayer';
			} else if ( $lmm_options['defaults_layer_listmarkers_order_by'] == 'm.kml_timestamp') {
				$sort_order_mlm = 'mkml_timestamp';
			}

			if ( (count($multi_layer_map_list_exploded) == 1) && ($multi_layer_map_list != 'all') && ($multi_layer_map_list != NULL) ) { //info: only 1 layer selected
				$mlm_query = "SELECT l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%')  WHERE l.id='" . $multi_layer_map_list . "' ORDER BY " . $sort_order_mlm . " " . $lmm_options[ 'defaults_layer_listmarkers_sort_order' ] . " LIMIT " . intval($lmm_options[ 'defaults_layer_listmarkers_limit' ]);
				$layer_marker_list = $wpdb->get_results($mlm_query, ARRAY_A);
				
				$mlm_query_table = "(SELECT l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "`  as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $multi_layer_map_list . "')";
				$mlm_query_table .= " ORDER BY " . $sort_order_mlm . " " . $lmm_options['defaults_layer_listmarkers_sort_order'] . " LIMIT " . intval($lmm_options['defaults_layer_listmarkers_limit']) . "";
				$layer_marker_list_table = $wpdb->get_results($mlm_query_table, ARRAY_A);
			} //info: end (count($multi_layer_map_list_exploded) == 1) && ($multi_layer_map_list != 'all') && ($multi_layer_map_list != NULL)
			else if ( (count($multi_layer_map_list_exploded) > 1 ) && ($multi_layer_map_list != 'all') ) {
				$first_mlm_id = $multi_layer_map_list_exploded[0];
				$other_mlm_ids = array_slice($multi_layer_map_list_exploded,1);
				$mlm_query = "(SELECT l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $first_mlm_id . "')";
				foreach ($other_mlm_ids as $row) {
					$mlm_query .= " UNION (SELECT l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON  m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $row . "')";
				}
				$mlm_query .= " ORDER BY " . $sort_order_mlm . " " . $lmm_options['defaults_layer_listmarkers_sort_order'] . " LIMIT " . intval($lmm_options['defaults_layer_listmarkers_limit']) . "";
				$layer_marker_list = $wpdb->get_results($mlm_query, ARRAY_A);
				
				$mlm_query_table = "(SELECT l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $first_mlm_id . "')";
				foreach ($other_mlm_ids as $row) {
					$mlm_query_table .= " UNION (SELECT l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $row . "')";
				}
				$mlm_query_table .= " ORDER BY " . $sort_order_mlm . " " . $lmm_options['defaults_layer_listmarkers_sort_order'] . " LIMIT " . $lmm_options['defaults_layer_listmarkers_limit'] . "";
				$layer_marker_list_table = $wpdb->get_results($mlm_query_table, ARRAY_A);
			} //info: end else if ( (count($multi_layer_map_list_exploded) > 1 ) && ($multi_layer_map_list != 'all')
			else if ($multi_layer_map_list == 'all') {
				$first_mlm_id = '0';
				$mlm_all_layers = $wpdb->get_results( "SELECT id FROM $table_name_layers", ARRAY_A );
				$other_mlm_ids = array_slice($mlm_all_layers,1);
				$mlm_query = "(SELECT l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $first_mlm_id . "')";
				foreach ($other_mlm_ids as $row) {
					$mlm_query .= " UNION (SELECT l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $row['id'] . "')";
				}
				$mlm_query .= " ORDER BY " . $sort_order_mlm . " " . $lmm_options['defaults_layer_listmarkers_sort_order'] . " LIMIT " . intval($lmm_options['defaults_layer_listmarkers_limit']) . "";
				$layer_marker_list = $wpdb->get_results($mlm_query, ARRAY_A);
				
				$mlm_query_table = "(SELECT l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $first_mlm_id . "')";
				foreach ($other_mlm_ids as $row) {
					$mlm_query_table .= " UNION (SELECT l.id as lid,l.name as lname,l.mapwidth as lmapwidth,l.mapheight as lmapheight,l.mapwidthunit as lmapwidthunit,l.layerzoom as llayerzoom,l.layerviewlat as llayerviewlat,l.layerviewlon as llayerviewlon, l.address as laddress, m.lon as mlon, m.lat as mlat, m.icon as micon, m.popuptext as mpopuptext,m.markername as markername,m.id as markerid,m.mapwidth as mmapwidth,m.mapwidthunit as mmapwidthunit,m.mapheight as mmapheight,m.zoom as mzoom,m.openpopup as mopenpopup, m.basemap as mbasemap, m.controlbox as mcontrolbox, m.createdby as mcreatedby, m.createdon as mcreatedon, m.updatedby as mupdatedby, m.updatedon as mupdatedon, m.address as maddress, m.layer as mlayer, m.kml_timestamp as mkml_timestamp FROM `" . $table_name_layers . "` as l INNER JOIN `" . $table_name_markers . "` AS m ON m.layer LIKE concat('%\"',l.id,'\"%') WHERE l.id='" . $row['id'] . "')";

				}
				$mlm_query_table .= " ORDER BY " . $sort_order_mlm . " " . $lmm_options['defaults_layer_listmarkers_sort_order'] . " LIMIT " . intval($lmm_options['defaults_layer_listmarkers_limit']) . "";
				$layer_marker_list_table = $wpdb->get_results($mlm_query_table, ARRAY_A);
			} //info: end else if ($multi_layer_map_list == 'all')
			else { //info: if ($multi_layer_map == 1) but no layers selected
				$layer_marker_list_table = array();
			}
    } //info: end main - else if ($multi_layer_map == 1)
	//info: check if layer exists - part 1
	if ($layerviewlat === NULL) {
		$error_layer_not_exists = sprintf( esc_attr__('Error: a layer with the ID %1$s does not exist!','lmm'), htmlspecialchars($_GET['id']));
		echo '<p><div class="error" style="padding:10px;">' . $error_layer_not_exists . '</div></p>';
		echo '<p><a class=\'button-secondary lmm-nav-secondary\' href=\'' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layers\'>' . __('list all layers','lmm') . '</a>&nbsp;&nbsp;&nbsp;<a class=\'button-secondary lmm-nav-secondary\' href=\'' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer\'>' . __('add new layer','lmm') . '</a></p>';
	} else {
		$edit_status = isset($_GET['status']) ? $_GET['status'] : '';
		if ( $edit_status == 'updated') {
			echo '<p><div class="updated" style="padding:10px;">' . __('Layer has been successfully updated','lmm') . '</div>';
		} else if ( $edit_status == 'published') {
			echo '<p><div class="updated" style="padding:10px;">' . __('Layer has been successfully published','lmm') . '</div>';
		} else if ( $edit_status == 'duplicated') {
			echo '<p><div class="updated" style="padding:10px;">' . __('Layer has been successfully duplicated','lmm') . '</div>';
		} ?>

		<?php $nonce= wp_create_nonce('layer-nonce'); ?>
		<form method="post">
		<?php wp_nonce_field('layer-nonce'); ?>
		<input type="hidden" name="id" value="<?php echo $id ?>" />
		<input type="hidden" name="action" value="<?php echo ($isedit ? 'edit' : 'add') ?>" />
		<input type="hidden" id="basemap" name="basemap" value="<?php echo $basemap ?>" />
		<input type="hidden" id="overlays_custom" name="overlays_custom" value="<?php echo $loverlays_custom ?>" />
		<input type="hidden" id="overlays_custom2" name="overlays_custom2" value="<?php echo $loverlays_custom2 ?>" />
		<input type="hidden" id="overlays_custom3" name="overlays_custom3" value="<?php echo $loverlays_custom3 ?>" />
		<input type="hidden" id="overlays_custom4" name="overlays_custom4" value="<?php echo $loverlays_custom4 ?>" />
		<input type="hidden" id="active_editor" name="active_editor" value="<?php echo $current_editor ?>" />
		<?php
			if ($current_editor == 'simplified') {
				echo '<div id="switch-link-visible" class="switch-link-rtl">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div style="float:right;"><a style="text-decoration:none;cursor:pointer;" id="editor-switch-link-to-advanced-href"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-editorswitch.png" width="24" height="24" alt="' . esc_attr__('switch to advanced editor','lmm') . '" style="margin:-2px 0 0 5px;" /></div>' . __('switch to advanced editor','lmm') . '</a></div>';
				echo '<div id="switch-link-hidden" class="switch-link-rtl" style="display:none;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div style="float:right;"><a style="text-decoration:none;cursor:pointer;" id="editor-switch-link-to-simplified-href"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-editorswitch.png" width="24" height="24" alt="' . esc_attr__('switch to simplified editor','lmm') . '" style="margin:-2px 0 0 5px;" /></div>' . __('switch to simplified editor','lmm') . '</a></div>';	
			} else {
				echo '<div id="switch-link-visible" class="switch-link-rtl">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div style="float:right;"><a style="text-decoration:none;cursor:pointer;" id="editor-switch-link-to-simplified-href"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-editorswitch.png" width="24" height="24" alt="' . esc_attr__('switch to simplified editor','lmm') . '" style="margin:-2px 0 0 5px;" /></div>' . __('switch to simplified editor','lmm') . '</a></div>';
				echo '<div id="switch-link-hidden" class="switch-link-rtl" style="display:none;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div style="float:right;"><a style="text-decoration:none;cursor:pointer;" id="editor-switch-link-to-advanced-href"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-editorswitch.png" width="24" height="24" alt="' . esc_attr__('switch to advanced editor','lmm') . '" style="margin:-2px 0 0 5px;" /></div>' . __('switch to advanced editor','lmm') . '</a></div>';
			}
		?>
		
		<h3 style="font-size:23px;margin-bottom:15px;"><?php ($isedit === true) ? _e('Edit layer','lmm') : _e('Add new layer','lmm') ?>
		<?php 
			if ($isedit === true) {	echo ' "' . stripslashes($name) . '" (ID '.$id.')'; }
			if (lmm_check_capability_edit($lcreatedby) == TRUE) {
				if ($isedit === true) { $button_text = __('update','lmm'); } else { $button_text = __('publish','lmm'); }
				echo '<input id="submit_top" style="font-weight:bold;margin-left:10px;" type="submit" name="layer" class="button button-primary" value="' . $button_text . '" />';
			} else {
				if ($isedit === true) { 
					echo '<span style="font-size:13px;margin-left:20px;">' . __('Your user does not have the permission to update this layer!','lmm') . '</span>';
				} else { 
					$button_text = __('publish','lmm'); 
					echo '<input id="submit_top" style="font-weight:bold;margin-left:10px;" type="submit" name="layer" class="button button-primary" value="' . $button_text . '" />';
				}
			}
			$multi_layer_map_edit_button = ( ($multi_layer_map == 0) && ($id != NULL) ) ? '<a class="button button-secondary" style="font-size:13px;margin-left:15px;text-decoration:none;" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_marker&addtoLayer=' . $oid . '">' . __('add new marker to this layer','lmm') . '</a>' : '';
			echo $multi_layer_map_edit_button;
		?>
		</h3>		

		<table class="widefat">
			<?php if ($isedit === true) { ?>
			<tr>
				<td style="width:230px;" class="lmm-border"><label for="shortcode"><strong><?php _e('Shortcode and API links','lmm') ?>:</strong></label></td>
				<td class="lmm-border"><input <?php echo $shortcode_select; ?> style="width:206px;background:#f3efef;" type="text" value="[<?php echo htmlspecialchars($lmm_options[ 'shortcode' ]); ?> layer=&quot;<?php echo $id?>&quot;]">
				<?php
					if ($current_editor == 'simplified') {
						echo '<div id="apilinkstext" style="display:inline;"><a tabindex="100" style="cursor:pointer;">' . __('show API links','lmm') . '</a></div>';
					}
					echo '<span id="apilinks"  style="' . $current_editor_css_inline . '">';
					echo '<a tabindex="101" href="' . LEAFLET_PLUGIN_URL . 'leaflet-kml.php?layer=' . $id . '&name=' . $lmm_options[ 'misc_kml' ] . '" title="' . esc_attr__('Export as KML for Google Earth/Google Maps','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-kml.png" width="14" height="14" alt="' . esc_attr__('Export as KML for Google Earth/Google Maps','lmm') . '" /> KML</a> <a tabindex="102" href="https://www.mapsmarker.com/kml" target="_blank" title="' . __('Click here for more information on how to use as KML in Google Earth or Google Maps','lmm') . '"> <img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0" alt="' . esc_attr__('Click here for more information on how to use as KML in Google Earth or Google Maps','lmm') . '"></a>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a tabindex="103" href="' . LEAFLET_PLUGIN_URL . 'leaflet-fullscreen.php?layer=' . $id . '" target="_blank" title="' . esc_attr__('Open standalone map in fullscreen mode','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-fullscreen.png" width="14" height="14" alt="' . esc_attr__('Open standalone map in fullscreen mode','lmm') . '" /> ' . __('Fullscreen','lmm') . '</a> <span title="' . __('Open standalone map in fullscreen mode','lmm') . '"> <img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0"/></span>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a tabindex="104" href="' . LEAFLET_PLUGIN_URL . 'leaflet-qr.php?layer=' . $id . '" target="_blank" title="' . esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-qr-code.png" width="14" height="14" alt="' . esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm') . '" /> ' . __('QR code','lmm') . '</a> <span title="' . __('Create QR code image for standalone map in fullscreen mode','lmm') . '"> <img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0" alt="' . esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm') . '" /></span>';
					if ($multi_layer_map == 0) {
						echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a tabindex="105" href="' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?layer=' . $id . '&callback=jsonp&full=yes&full_icon_url=yes' . '" target="_blank" title="' . esc_attr__('Export as GeoJSON','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-json.png" width="14" height="14" alt="' . esc_attr__('Export as GeoJSON','lmm') . '" /> GeoJSON</a> <a tabindex="106" href="https://www.mapsmarker.com/geojson" target="_blank" title="' . __('Click here for more information on how to integrate GeoJSON into external websites or apps','lmm') . '"> <img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0" alt="' . esc_attr__('Click here for more information on how to integrate GeoJSON into external websites or apps','lmm') . '"></a>';
					} 
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a tabindex="107" href="' . LEAFLET_PLUGIN_URL . 'leaflet-georss.php?layer=' . $id . '" target="_blank" title="' . esc_attr__('Export as GeoRSS','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-georss.png" width="14" height="14" alt="' . esc_attr__('Export as GeoRSS','lmm') . '" /> GeoRSS</a> <a tabindex="108" href="https://www.mapsmarker.com/georss" target="_blank" title="' . __('Click here for more information on how to subscribe to new markers via GeoRSS','lmm') . '"> <img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0" alt="' . esc_attr__('Click here for more information on how to subscribe to new markers via GeoRSS','lmm') . '"></a>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a tabindex="109" href="' . LEAFLET_PLUGIN_URL . 'leaflet-wikitude.php?layer=' . $id . '" target="_blank" title="' . esc_attr__('Export as ARML for Wikitude Augmented-Reality browser','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-wikitude.png" width="14" height="14" alt="' . esc_attr__('Export as ARML for Wikitude Augmented-Reality browser','lmm') . '" /> Wikitude</a> <a tabindex="110" href="https://www.mapsmarker.com/wikitude" target="_blank" title="' . __('Click here for more information on how to display in Wikitude Augmented-Reality browser','lmm') . '"> <img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0" alt="' . esc_attr__('Click here for more information on how to display in Wikitude Augmented-Reality browser','lmm') . '" /></a>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a tabindex="134" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-misc-section9"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-menu-page.png" width="16" height="16" alt="' . esc_attr__('Settings','lmm') . '" /> Maps Marker API</a>';
					echo '</span>';
				?>
					<br/><small><?php _e('Use this shortcode in posts or pages on your website or one of the API URLs for embedding in external websites or apps','lmm') ?></small>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td style="width:230px;" class="lmm-border"><label for="layername"><strong><?php _e('Layer name', 'lmm') ?></strong></label></td>
				<td class="lmm-border"><input <?php if (get_option('leafletmapsmarker_update_info') == 'hide') { echo 'autofocus'; } ?> style="width: 640px;" maxlenght="255" type="text" id="layername" name="name" value="<?php echo stripslashes($name) ?>" /></td>
			</tr>
			<tr>
				<td class="lmm-border"><label for="address"><strong><?php _e('Location','lmm') ?></strong></label><br/><a tabindex="111" href="https://developers.google.com/places/documentation/autocomplete" target="_blank"><img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/powered-by-google.png" width="104" height="16" style="padding-top:9px;<?php echo $css_whitelabel = (($lmm_options['misc_whitelabel_backend'] == 'enabled') || ($lmm_options['google_places_status'] == 'disabled')) ? 'display:none' : '' ?>" /></a></td>
				<td class="lmm-border">
					<?php if ($lmm_options['google_places_status'] == 'enabled') { ?>
						<label for="address"><?php _e('Please select a place or an address','lmm') ?></label> <?php if (current_user_can('activate_plugins')) { echo '<span id="toggle-google-settings" style="' . $current_editor_css_inline . '"><a tabindex="112" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-google-section4">(' . __('Settings','lmm') . ')</a></span>'; } ?><br/>
						<input style="width:640px;height:25px;" type="text" id="address" name="address" value="<?php echo stripslashes(htmlspecialchars($laddress)); ?>" disabled="disabled" />
					<?php } else {
							$google_places_info = sprintf(__('<a href="%1s">"Google Places Autocomplete API"</a> is disabled!', 'lmm'), LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-google-section1');
							echo $google_places_info;
						} 
					?>
					<div id="toggle-coordinates" style="clear:both;margin-top:5px;<?php echo $current_editor_css; ?>">
					<?php if ($lmm_options['google_places_status'] == 'enabled') { 
						_e('or paste coordinates here','lmm') . ' - ';
					} ?>
					<?php _e('latitude','lmm') ?>: <input style="width: 100px;height:24px;" type="text" id="layerviewlat" name="layerviewlat" value="<?php echo $layerviewlat; ?>" />
					<?php _e('longitude','lmm') ?>: <input style="width: 100px;height:24px;" type="text" id="layerviewlon" name="layerviewlon" value="<?php echo $layerviewlon; ?>" />
					</div>
				</td>
			</tr>
			<tr>
				<td class="lmm-border"><p>
				<strong><?php _e('Map size','lmm') ?></strong><br/>
				<label for="mapwidth"><?php _e('Width','lmm') ?>:</label>
				<input size="3" maxlength="4" type="text" id="mapwidth" name="mapwidth" value="<?php echo $mapwidth ?>" style="margin-left:5px;height:24px;" />
				<input id="mapwidthunit_px" type="radio" name="mapwidthunit" value="px" <?php checked($mapwidthunit, 'px'); ?>><label for="mapwidthunit_px" title="<?php esc_attr_e('pixel','lmm'); ?>">px</label>&nbsp;&nbsp;&nbsp;
				<input id="mapwidthunit_percent" type="radio" name="mapwidthunit" value="%" <?php checked($mapwidthunit, '%'); ?>><label for="mapwidthunit_percent">%</label><br/>
				<label for="mapheight"><?php _e('Height','lmm') ?>:</label>
				<input size="3" maxlength="4" type="text" id="mapheight" name="mapheight" value="<?php echo $mapheight ?>" style="height:24px;" /> <span title="<?php esc_attr_e('pixel','lmm'); ?>">px</span>
				
				<hr style="border:none;color:#edecec;background:#edecec;height:1px;">
				
				<label for="layerzoom"><strong><?php _e('Zoom','lmm') ?></strong> <img src="<?php echo LEAFLET_PLUGIN_URL; ?>inc/img/icon-question-mark.png" title="<?php esc_attr_e('You can also change zoom level by clicking on + or - on preview map or using your mouse wheel'); ?>" width="12" height="12" border="0"/></label>&nbsp;<input id="layerzoom" style="width:40px;height:24px;" type="text" id="layerzoom" name="layerzoom" value="<?php echo $layerzoom ?>" />
				<small>
				<?php 
				echo '<span id="toogle-global-maximum-zoom-level" style="' . $current_editor_css_inline . '"><br/>' . __('Global maximum zoom level','lmm') . ': ';
				if (current_user_can('activate_plugins')) { 
					echo '<a title="' . esc_attr__('If the native maximum zoom level of a basemap is lower, tiles will be upscaled automatically.','lmm') . '" tabindex="111" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-mapdefaults-section1">' . intval($lmm_options['global_maxzoom_level']) . '</a>'; 
				} else {
					echo intval($lmm_options['global_maxzoom_level']);
				}
				?>
				</span>
				</small>
				
				<hr style="border:none;color:#edecec;background:#edecec;height:1px;">
				
				<strong><label for="listmarkers"><?php _e('Show list of markers below map','lmm') ?></label></strong>&nbsp;<input type="checkbox" name="listmarkers" id="listmarkers" <?php checked($llistmarkers, 1 ); ?>><br/>
				<?php
						echo '<small>';
						_e('Max. number of markers to display:','lmm');
						echo ' ' . intval($lmm_options[ 'defaults_layer_listmarkers_limit' ]);
						if (current_user_can('activate_plugins')) {
							echo ' <span id="toggle-listofmarkerssettings" style="' . $current_editor_css_inline . '"><a tabindex="113" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#mapdefaults-section9">(' . __('Settings','lmm') . ')</a></span>';
						}
						echo '</small>';
				?>
				
				<hr style="border:none;color:#edecec;background:#edecec;height:1px;">
				
				<label for="clustering"><strong><?php _e('Marker clustering','lmm') ?></strong></label>&nbsp;&nbsp;<input type="checkbox" name="clustering" id="clustering" <?php checked($lclustering, 1 ); ?>>
				<?php if (current_user_can('activate_plugins')) {
					echo '<span id="toggle-clustersettings" style="' . $current_editor_css_inline . '">&nbsp;&nbsp;<small>(<a tabindex="115" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#mapdefaults-section18">' . __('Settings','lmm') . '</a>)</small></span>';
				} ?>
				
				<hr style="border:none;color:#edecec;background:#edecec;height:1px;">
				
				<div style="float:right;"><label for="gpx_panel"><?php _e('display panel','lmm') ?></label>&nbsp;&nbsp;<input style="margin-top:1px;" type="checkbox" name="gpx_panel" id="gpx_panel" <?php checked($gpx_panel, 1 ); ?>></div>
				<label for="gpx_url"><strong><?php _e('URL to GPX track','lmm') ?></strong></label><br/>
				<?php
					if ($gpx_url != NULL) {
						//info: load gpx_content
						$gpx_content_array = wp_remote_get( $gpx_url, array( 'sslverify' => false, 'timeout' => 30 ) );
					}
					if ($gpx_url != NULL) {
						if ( !lmm_isValidURL( $gpx_url ) ) {
							echo '<div class="error" style="padding:10px;">' . __('The URL to the GPX file you entered seems to be invalid (it has to start with http for example)!','lmm') . '</div>';
						} else if (is_wp_error($gpx_content_array)) {
							echo '<div class="error" style="padding:10px;">' . sprintf(__('The GPX file could not be loaded due to the following error:<br/>%s!','lmm'), $gpx_content_array->get_error_message()) . '</div>';
						} else if ($gpx_content_array['body'] == NULL) {
							echo '<div class="error" style="padding:10px;">' . sprintf(__('The GPX file at %s could not be found!','lmm'), $gpx_url) . '</div>';
						}
					}
				?>
				<input style="width:229px;" type="text" id="gpx_url" name="gpx_url" value="<?php echo $gpx_url ?>" /><br/>
				<?php if (current_user_can('upload_files')) { echo '<small><span style="color:#21759B;cursor:pointer;" onMouseOver="this.style.color=\'#D54E21\'" onMouseOut="this.style.color=\'#21759B\'" id="upload_gpx_file">' . __('add','lmm') . '</span> |'; } ?>
				<a tabindex="117" href="https://www.mapsmarker.com/gpx-convert" target="_blank" title="<?php esc_attr_e('Click here for a tutorial on how to convert a non-GPX-track file into a GPX track file','lmm'); ?>"><?php _e('convert','lmm'); ?></a> | 
				<a tabindex="118" href="https://www.mapsmarker.com/gpx-merge" target="_blank" title="<?php esc_attr_e('Click here for a tutorial on how to merge multiple GPX-track files into one GPX track file','lmm'); ?>"><?php _e('merge','lmm'); ?></a>
				<?php if (current_user_can('activate_plugins')) { echo ' | <a tabindex="116" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#mapdefaults-section19">' . __('settings','lmm') . '</a>'; } ?>
				<?php if ($gpx_url != NULL) { $fitbounds_css = 'display:inline;'; } else { $fitbounds_css = 'display:none;'; }
				echo '<span id="gpx_fitbounds_link" style="color:#21759B;cursor:pointer;' . $fitbounds_css . '" onMouseOver="this.style.color=\'#D54E21\'" onMouseOut="this.style.color=\'#21759B\'" class="gpxfitbounds"> | ' . __('fit bounds','lmm') . '</small></span>'; ?>
				</p>
				<div id="toggle-controlbox-panel-kmltimestamp-backlinks-minimaps" style="<?php echo $current_editor_css; ?>">
				<p>
				<hr style="border:none;color:#edecec;background:#edecec;height:1px;">
				<strong><?php _e('Controlbox for basemaps/overlays','lmm') ?>:</strong><br/>
				<input style="margin-top:1px;" id="controlbox_hidden" type="radio" name="controlbox" value="0" <?php checked($lcontrolbox, 0); ?>><label for="controlbox_hidden"><?php _e('hidden','lmm') ?></label><br/>
				<input style="margin-top:1px;" id="controlbox_collapsed" type="radio" name="controlbox" value="1" <?php checked($lcontrolbox, 1); ?>><label for="controlbox_collapsed"><?php _e('collapsed','lmm') ?></label><br/>
				<input style="margin-top:1px;" id="controlbox_expanded" type="radio" name="controlbox" value="2" <?php checked($lcontrolbox, 2); ?>><label for="controlbox_expanded"><?php _e('expanded','lmm') ?></label>
				
				<hr style="border:none;color:#edecec;background:#edecec;height:1px;">
				
				<strong><label for="panel"><?php _e('Display panel','lmm') ?></panel></strong>&nbsp;&nbsp;<input style="margin-top:1px;" type="checkbox" name="panel" id="panel" <?php checked($panel, 1 ); ?>><br/>
				<small><?php _e('If checked, panel on top of map is displayed','lmm') ?></small>
				</p>
				</div>
				</td>
				<td style="padding-bottom:5px;" class="lmm-border">
					<?php
					echo '<div id="lmm" class="lmm-rtl" style="width:' . $mapwidth.$mapwidthunit . ';">'.PHP_EOL;

					//info: markercluster progress bar
					if ($mapwidthunit == '%') {
						$mcpb_left = 'left:36%;';
						$mcpb_top = 'top:90px;';
						$mcpb_width = 'width:200px;';
					} else {
						$mcpb_top = 'top:' . (($mapheight/2)+40) . 'px;';
						if ($mapwidth >= 200) {
							$mcpb_left = 'left:' . (($mapwidth/2)-100) . 'px;';
							$mcpb_width = 'width:200px;';
						} else {
							$mcpb_left = 'left:2%;';
							$mcpb_width = 'width:95%;';
						}
					}
					echo '<div id="selectlayer-progress" class="markercluster-progress" style="' . $mcpb_left . $mcpb_top . $mcpb_width . '"><div id="selectlayer-progress-bar" class="markercluster-progress-bar"></div></div>'.PHP_EOL; 

					//info: panel for layer name and API URLs
					$panel_state = ($panel == 1) ? 'block' : 'none';
					echo '<div id="lmm-panel" class="lmm-panel" style="display:' . $panel_state . '; background: ' . htmlspecialchars(addslashes($lmm_options[ 'defaults_layer_panel_background_color' ])) . ';">'.PHP_EOL;
					echo '<div class="lmm-panel-api">';
						if ( (isset($lmm_options[ 'defaults_layer_panel_kml' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_panel_kml' ] == 1 ) ) {
							echo '<a tabindex="114" href="' . LEAFLET_PLUGIN_URL . 'leaflet-kml.php?layer=' . $id . '&name=' . $lmm_options[ 'misc_kml' ] . '" style="text-decoration:none;" title="' . esc_attr__('Export as KML for Google Earth/Google Maps','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-kml.png" width="14" height="14" alt="' . esc_attr__('Export as KML for Google Earth/Google Maps','lmm') . '" class="lmm-panel-api-images" /></a>';
						}
						if ( (isset($lmm_options[ 'defaults_layer_panel_fullscreen' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_panel_fullscreen' ] == 1 ) ) {
							echo '<a tabindex="115" href="' . LEAFLET_PLUGIN_URL . 'leaflet-fullscreen.php?layer=' . $id . '" style="text-decoration:none;" title="' . esc_attr__('Open standalone map in fullscreen mode','lmm') . '" target="_blank" title="' . esc_attr__('Open standalone map in fullscreen mode','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-fullscreen.png" width="14" height="14" alt="' . esc_attr__('Open standalone map in fullscreen mode','lmm') . '" class="lmm-panel-api-images" /></a>';
						}
						if ( (isset($lmm_options[ 'defaults_layer_panel_qr_code' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_panel_qr_code' ] == 1 ) ) {
							echo '<a tabindex="116" href="' . LEAFLET_PLUGIN_URL . 'leaflet-qr.php?layer=' . $id . '" target="_blank" title="' . esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-qr-code.png" width="14" height="14" alt="' . esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm') . '" class="lmm-panel-api-images" /></a>';
						}
						if ( (isset($lmm_options[ 'defaults_layer_panel_geojson' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_panel_geojson' ] == 1 ) ) {
							if ($multi_layer_map == 0 ) { $geojson_api_link = $id; } else { $geojson_api_link = $multi_layer_map_list; } 
							echo '<a tabindex="117" href="' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?layer=' . $geojson_api_link . '&callback=jsonp&full=yes&full_icon_url=yes" style="text-decoration:none;" title="' . esc_attr__('Export as GeoJSON','lmm') . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-json.png" width="14" height="14" alt="' . esc_attr__('Export as GeoJSON','lmm') . '" class="lmm-panel-api-images" /></a>';
						}
						if ( (isset($lmm_options[ 'defaults_layer_panel_georss' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_panel_georss' ] == 1 ) ) {
							echo '<a tabindex="118" href="' . LEAFLET_PLUGIN_URL . 'leaflet-georss.php?layer=' . $id . '" style="text-decoration:none;" title="' . esc_attr__('Export as GeoRSS','lmm') . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-georss.png" width="14" height="14" alt="' . esc_attr__('Export as GeoRSS','lmm') . '" class="lmm-panel-api-images" /></a>';
						}
						if ( (isset($lmm_options[ 'defaults_layer_panel_wikitude' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_panel_wikitude' ] == 1 ) ) {
							echo '<a tabindex="119" href="' . LEAFLET_PLUGIN_URL . 'leaflet-wikitude.php?layer=' . $id . '" style="text-decoration:none;" title="' . esc_attr__('Export as ARML for Wikitude Augmented-Reality browser','lmm') . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-wikitude.png" width="14" height="14" alt="' . esc_attr__('Export as ARML for Wikitude Augmented-Reality browser','lmm') . '" class="lmm-panel-api-images" /></a>';
						}
					echo '</div>'.PHP_EOL;
					echo '<div id="lmm-panel-text" class="lmm-panel-text" style="' . htmlspecialchars(addslashes($lmm_options[ 'defaults_layer_panel_paneltext_css' ])) . '">' . (($name == NULL) ? __('if set, layername will be displayed here','lmm') : stripslashes($name)) . '</div>'.PHP_EOL;
					?>
					</div> <!--end lmm-panel-->
					<div id="selectlayer" style="height:<?php echo $mapheight; ?>px;"></div>
					<?php $gpx_panel_state = ($gpx_panel == 1) ? 'block' : 'none'; ?>
					<div id="gpx-panel-selectlayer" class="gpx-panel" style="display:<?php echo $gpx_panel_state; ?>; background: <?php echo htmlspecialchars(addslashes($lmm_options[ 'defaults_layer_panel_background_color' ])); ?>;">
					<?php
					if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') { $gpx_unit_distance = 'km'; $gpx_unit_elevation = 'm'; } else { $gpx_unit_distance = 'mi'; $gpx_unit_elevation = 'ft'; }
					if ( (isset($lmm_options[ 'gpx_metadata_name' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_name' ] == 1 ) ) {
						$gpx_metadata_name = '<label for="gpx-name">' . __('Track name','lmm') . ':</label> <span id="gpx-name" class="gpx-name"></span>';
					} else { $gpx_metadata_name = NULL; }
					if ( (isset($lmm_options[ 'gpx_metadata_start' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_start' ] == 1 ) ) {
						$gpx_metadata_start = '<label for="gpx-start">' . __('Start','lmm') . ':</label> <span id="gpx-start" class="gpx-start"></span>';
					} else { $gpx_metadata_start = NULL; }
					if ( (isset($lmm_options[ 'gpx_metadata_end' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_end' ] == 1 ) ) {
						$gpx_metadata_end = '<label for="gpx-end">' . __('End','lmm') . ':</label> <span id="gpx-end" class="gpx-end"></span>';
					} else { $gpx_metadata_end = NULL; }
					if ( (isset($lmm_options[ 'gpx_metadata_distance' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_distance' ] == 1 ) ) {
						$gpx_metadata_distance = '<label for="gpx-distance">' . __('Distance','lmm') . ':</label> <span id="gpx-distance"><span class="gpx-distance"></span> ' . $gpx_unit_distance . '</span>';
					} else { $gpx_metadata_distance = NULL; }
					if ( (isset($lmm_options[ 'gpx_metadata_duration_moving' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_duration_moving' ] == 1 ) ) {
						$gpx_metadata_duration_moving = '<label for="gpx-duration-moving">' . __('Moving time','lmm') . ':</label> <span id="gpx-duration-moving" class="gpx-duration-moving"></span> ';
					} else { $gpx_metadata_duration_moving = NULL; }
					if ( (isset($lmm_options[ 'gpx_metadata_duration_total' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_duration_total' ] == 1 ) ) {
						$gpx_metadata_duration_total = '<label for="gpx-duration-total">' . __('Duration','lmm') . ':</label> <span id="gpx-duration-total" class="gpx-duration-total"></span> ';
					} else { $gpx_metadata_duration_total = NULL; }
					if ( (isset($lmm_options[ 'gpx_metadata_avpace' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_avpace' ] == 1 ) ) {
						$gpx_metadata_avpace = '<label for="gpx-avpace">&#216;&nbsp;' . __('Pace','lmm') . ':</label> <span id="gpx-avpace"><span class="gpx-avpace"></span>/' . $gpx_unit_distance . '</span>';
					} else { $gpx_metadata_avpace = NULL; }
					if ( (isset($lmm_options[ 'gpx_metadata_avhr' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_avhr' ] == 1 ) ) {
						$gpx_metadata_avhr = '<label for="gpx-avghr">&#216;&nbsp;' . __('Heart rate','lmm') . ':</label> <span id="gpx-avghr" class="gpx-avghr"></span>';
					} else { $gpx_metadata_avhr = NULL; }
					if ( ((isset($lmm_options[ 'gpx_metadata_elev_gain' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_gain' ] == 1 )) || ((isset($lmm_options[ 'gpx_metadata_elev_loss' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_loss' ] == 1 )) || ((isset($lmm_options[ 'gpx_metadata_elev_net' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_net' ] == 1 )) ) {
						$gpx_metadata_elevation_title = '<label for="gpx-elevation">' . __('Elevation','lmm') . ':</label> <span id="gpx-elevation">';
					} else { $gpx_metadata_elevation_title = NULL; }
					if ( (isset($lmm_options[ 'gpx_metadata_elev_gain' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_gain' ] == 1 ) ) {
						$gpx_metadata_elev_gain = '+<span class="gpx-elevation-gain"></span>' . $gpx_unit_elevation;
					} else { $gpx_metadata_elev_gain = NULL; }
					if ( (isset($lmm_options[ 'gpx_metadata_elev_loss' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_loss' ] == 1 ) ) {
						$gpx_metadata_elev_loss = '-<span class="gpx-elevation-loss"></span>' . $gpx_unit_elevation;
					} else { $gpx_metadata_elev_loss = NULL; }
					if ( (isset($lmm_options[ 'gpx_metadata_elev_net' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_net' ] == 1 ) ) {
						$gpx_metadata_elev_net = '(' . __('net','lmm') . ': <span class="gpx-elevation-net"></span>' . $gpx_unit_elevation . ')</span>'; //info: </span> ->elevation-ID
					} else { $gpx_metadata_elev_net = NULL; }
					if ( (isset($lmm_options[ 'gpx_metadata_elev_full' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_full' ] == 1 ) ) {
						$gpx_metadata_elev_full = '<br/><label for="gpx-elevation-full">' . __('Full elevation data','lmm') . ':</label><br/><span id="gpx-elevation-full" class="gpx-elevation-full"></span>';
					} else { $gpx_metadata_elev_full = NULL; }
					if ( (isset($lmm_options[ 'gpx_metadata_hr_full' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_hr_full' ] == 1 ) ) {
						$gpx_metadata_hr_full = '<br/><label for="gpx-heartrate-full">' . __('Full heart rate data','lmm') . ':</label><br/><span id="gpx-heartrate-full" class="gpx-heartrate-full"></span>';
					} else { $gpx_metadata_hr_full = NULL; }
					$gpx_metadata_elevation_array = array($gpx_metadata_elevation_title, $gpx_metadata_elev_gain, $gpx_metadata_elev_loss, $gpx_metadata_elev_net);
					$gpx_metadata_elevation = implode(' ',$gpx_metadata_elevation_array);
					if ($gpx_metadata_elevation == '   ') { $gpx_metadata_elevation = NULL; } //info: for no trailing |
					$gpx_metadata_array_all = array($gpx_metadata_name, $gpx_metadata_start, $gpx_metadata_end, $gpx_metadata_distance, $gpx_metadata_duration_moving, $gpx_metadata_duration_total, $gpx_metadata_avpace, $gpx_metadata_avhr, $gpx_metadata_elevation, $gpx_metadata_elev_full, $gpx_metadata_hr_full);

					$gpx_metadata_array_not_null = array();
					foreach ($gpx_metadata_array_all as $key => $value) {
						if (is_null($value) === false) {
							$gpx_metadata_array_not_null[$key] = $value;
						}
					}
					$gpx_metadata = implode(' <span class="gpx-delimiter">|</span> ',$gpx_metadata_array_not_null);
					echo $gpx_metadata;
					if ( (isset($lmm_options[ 'gpx_metadata_gpx_download' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_gpx_download' ] == 1 ) ) {
						echo '<span class="gpx-delimiter">|</span> <span id="gpx-download"><a href="' . $gpx_url . '" title="' . esc_attr__('download GPX file','lmm') . '" download>' . esc_attr__('download GPX file','lmm') . ' <img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-download-gpx.png" width="10" height="10" alt="' . esc_attr__('download GPX file','lmm') . '"></a></span>';
					}
					?>
					</div>
					<?php
					//info: display a list of markers
					$listmarkers_state = ($llistmarkers == 0) ? 'none' : 'block';
					echo '<div id="lmm-listmarkers" class="lmm-listmarkers" style="display:' . $listmarkers_state . ';">'.PHP_EOL;
					//info: set list markers width to be 100% of maps width
					if ($mapwidthunit == '%') {
						$layer_marker_list_width = '100%';
					} else {
						$layer_marker_list_width = $mapwidth.$mapwidthunit;
					}
					echo '<table id="lmm-listmarkers-table" cellspacing="0" style="width:' . $layer_marker_list_width . ';" class="lmm-listmarkers-table">';
					if ($markercount == 0) {
						echo '<tr><td style="border-style:none;width:35px;"><img src="' . LEAFLET_PLUGIN_URL . 'leaflet-dist/images/marker.png" /></td>';
						echo '<td style="border-style:none;"><div style="float:right;"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-car.png" width="14" height="14" class="lmm-panel-api-images" />&nbsp;<img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-fullscreen.png" width="14" height="14" class="lmm-panel-api-images" />&nbsp;<img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-kml.png" width="14" height="14" class="lmm-panel-api-images" /></div><strong>'.__('Markers assigned to this layer will be listed here', 'lmm').'</strong></td></tr>';
					} else {
						if ($layer_marker_list != NULL) { //info: to prevent PHP errors
							foreach ($layer_marker_list as $row) {
								if ( (isset($lmm_options[ 'defaults_layer_listmarkers_show_icon' ]) == TRUE ) && ($lmm_options[ 'defaults_layer_listmarkers_show_icon' ] == 1 ) ) {
									echo '<tr><td class="lmm-listmarkers-icon">';
									if ($lmm_options['defaults_layer_listmarkers_link_action'] != 'disabled') { 
										$listmarkers_href_a = '<a href="#address" onclick="javascript:listmarkers_action(' . $row['markerid'] . ')">'; 
										$listmarkers_href_b = '</a>'; 
									} else { 
										$listmarkers_href_a = ''; 
										$listmarkers_href_b = ''; 
									}
									if ($row['micon'] != null) {
										echo $listmarkers_href_a . '<img src="' . $defaults_marker_icon_url . '/'.$row['micon'].'" title="' . stripslashes(htmlspecialchars($row['markername'])) . '" alt="marker icon" />' . $listmarkers_href_b;
									} else {
										echo $listmarkers_href_a . '<img src="' . LEAFLET_PLUGIN_URL . 'leaflet-dist/images/marker.png" title="' . stripslashes(htmlspecialchars($row['markername'])) . '" alt="marker icon" />' . $listmarkers_href_b;
									};
								} else {
									echo '<tr><td>';
								}
								echo '</td><td class="lmm-listmarkers-popuptext"><div class="lmm-listmarkers-panel-icons">';
								if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_directions' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_directions' ] == 1 ) ) {
									if ($lmm_options['directions_provider'] == 'googlemaps') {
										if ( isset($lmm_options['google_maps_base_domain_custom']) && ($lmm_options['google_maps_base_domain_custom'] == NULL) ) { $gmaps_base_domain_directions = $lmm_options['google_maps_base_domain']; } else { $gmaps_base_domain_directions = htmlspecialchars($lmm_options['google_maps_base_domain_custom']); }
										if ((isset($lmm_options[ 'directions_googlemaps_route_type_walking' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_walking' ] == 1 )) { $yours_transport_type_icon = 'icon-walk.png'; } else { $yours_transport_type_icon = 'icon-car.png'; }
										if ( $row['maddress'] != NULL ) { $google_from = urlencode($row['maddress']); } else { $google_from = $row['mlat'] . ',' . $row['mlat']; }
										$avoidhighways = (isset($lmm_options[ 'directions_googlemaps_route_type_highways' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_highways' ] == 1 ) ? '&dirflg=h' : '';
										$avoidtolls = (isset($lmm_options[ 'directions_googlemaps_route_type_tolls' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_tolls' ] == 1 ) ? '&dirflg=t' : '';
										$publictransport = (isset($lmm_options[ 'directions_googlemaps_route_type_public_transport' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_public_transport' ] == 1 ) ? '&dirflg=r' : '';
										$walking = (isset($lmm_options[ 'directions_googlemaps_route_type_walking' ] ) == TRUE ) && ( $lmm_options[ 'directions_googlemaps_route_type_walking' ] == 1 ) ? '&dirflg=w' : '';
										//info: Google language localization (directions)
										if ($lmm_options['google_maps_language_localization'] == 'browser_setting') {
											$google_language = '';
										} else if ($lmm_options['google_maps_language_localization'] == 'wordpress_setting') {
											if ( $locale != NULL ) { $google_language = '&hl=' . substr($locale, 0, 2); } else { $google_language =  '&hl=en'; }
										} else {
											$google_language = '&hl=' . $lmm_options['google_maps_language_localization'];
										}
										echo '<a tabindex="127" href="https://' . $gmaps_base_domain_directions . '/maps?daddr=' . $google_from . '&t=' . $lmm_options[ 'directions_googlemaps_map_type' ] . '&layer=' . $lmm_options[ 'directions_googlemaps_traffic' ] . '&doflg=' . $lmm_options[ 'directions_googlemaps_distance_units' ] . $avoidhighways . $avoidtolls . $publictransport . $walking . $google_language . '&om=' . $lmm_options[ 'directions_googlemaps_overview_map' ] . '" target="_blank" title="' . esc_attr__('Get directions','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/' . $yours_transport_type_icon . '" width="14" height="14" class="lmm-panel-api-images" alt="' . esc_attr__('Get directions','lmm') . '" /></a>';
									} else if ($lmm_options['directions_provider'] == 'yours') {
										if ($lmm_options[ 'directions_yours_type_of_transport' ] == 'motorcar') { $yours_transport_type_icon = 'icon-car.png'; } else if ($lmm_options[ 'directions_yours_type_of_transport' ] == 'bicycle') { $yours_transport_type_icon = 'icon-bicycle.png'; } else if ($lmm_options[ 'directions_yours_type_of_transport' ] == 'foot') { $yours_transport_type_icon = 'icon-walk.png'; }
										echo '<a tabindex="128" href="http://www.yournavigation.org/?tlat=' . $row['mlat'] . '&tlon=' . $row['mlon'] . '&v=' . $lmm_options[ 'directions_yours_type_of_transport' ] . '&fast=' . $lmm_options[ 'directions_yours_route_type' ] . '&layer=' . $lmm_options[ 'directions_yours_layer' ] . '" target="_blank" title="' . esc_attr__('Get directions','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/' . $yours_transport_type_icon . '" width="14" height="14" class="lmm-panel-api-images" alt="' . esc_attr__('Get directions','lmm') . '" /></a>';
									} else if ($lmm_options['directions_provider'] == 'osrm') {
										echo '<a tabindex="129" href="http://map.project-osrm.org/?hl=' . $lmm_options[ 'directions_osrm_language' ] . '&loc=' . $row['mlat'] . ',' . $row['mlon'] . '&df=' . $lmm_options[ 'directions_osrm_units' ] . '" target="_blank" title="' . esc_attr__('Get directions','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/' . $yours_transport_type_icon . '" width="14" height="14" class="lmm-panel-api-images" alt="' . esc_attr__('Get directions','lmm') . '" /></a>';
									} else if ($lmm_options['directions_provider'] == 'ors') {
										if ($lmm_options[ 'directions_ors_route_preferences' ] == 'Pedestrian') { $yours_transport_type_icon = 'icon-walk.png'; } else if ($lmm_options[ 'directions_ors_route_preferences' ] == 'Bicycle') { $yours_transport_type_icon = 'icon-bicycle.png'; } else { $yours_transport_type_icon = 'icon-car.png'; }
										echo '<a tabindex="130" href="http://openrouteservice.org/index.php?end=' . $row['mlon'] . ',' . $row['mlat'] . '&pref=' . $lmm_options[ 'directions_ors_route_preferences' ] . '&lang=' . $lmm_options[ 'directions_ors_language' ] . '&noMotorways=' . $lmm_options[ 'directions_ors_no_motorways' ] . '&noTollways=' . $lmm_options[ 'directions_ors_no_tollways' ] . '" target="_blank" title="' . esc_attr__('Get directions','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/' . $yours_transport_type_icon . '" width="14" height="14" class="lmm-panel-api-images" alt="' . esc_attr__('Get directions','lmm') . '" /></a>';
									} else if ($lmm_options['directions_provider'] == 'bingmaps') {
										if ( $row['maddress'] != NULL ) { $bing_to = '_' . urlencode($row['maddress']); } else { $bing_to = ''; }
										echo '<a tabindex="130" href="https://www.bing.com/maps/default.aspx?v=2&rtp=pos___e_~pos.' . $row['mlat'] . '_' . $row['mlon'] . $bing_to . '" target="_blank" title="' . esc_attr__('Get directions','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-car.png" width="14" height="14" class="lmm-panel-api-images" alt="' . esc_attr__('Get directions','lmm') . '" /></a>';
									}
								}
								if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_fullscreen' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_fullscreen' ] == 1 ) ) {
									echo '&nbsp;<a tabindex="131" href="' . LEAFLET_PLUGIN_URL . 'leaflet-fullscreen.php?marker=' . $row['markerid'] . '" style="text-decoration:none;" title="' . esc_attr__('Open standalone map in fullscreen mode','lmm') . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-fullscreen.png" width="14" height="14" alt="' . esc_attr__('Open standalone map in fullscreen mode','lmm') . '" class="lmm-panel-api-images" /></a>';
								}
								if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_kml' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_kml' ] == 1 ) ) {
									echo '&nbsp;<a tabindex="132" href="' . LEAFLET_PLUGIN_URL . 'leaflet-kml.php?marker=' . $row['markerid'] . '&name=' . $lmm_options[ 'misc_kml' ] . '" style="text-decoration:none;" title="' . esc_attr__('Export as KML for Google Earth/Google Maps','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-kml.png" width="14" height="14" alt="' . esc_attr__('Export as KML for Google Earth/Google Maps','lmm') . '" class="lmm-panel-api-images" /></a>';
								}
								if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_qr_code' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_qr_code' ] == 1 ) ) {
									echo '&nbsp;<a tabindex="133" href="' . LEAFLET_PLUGIN_URL . 'leaflet-qr.php?marker=' . $row['markerid'] . '" target="_blank" title="' . esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-qr-code.png" width="14" height="14" alt="' . esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm') . '" class="lmm-panel-api-images" /></a>';
								}
								if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_geojson' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_geojson' ] == 1 ) ) {
									echo '&nbsp;<a tabindex="134" href="' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?marker=' . $row['markerid'] . '&callback=jsonp&full=yes&full_icon_url=yes" style="text-decoration:none;" title="' . esc_attr__('Export as GeoJSON','lmm') . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-json.png" width="14" height="14" alt="' . esc_attr__('Export as GeoJSON','lmm') . '" class="lmm-panel-api-images" /></a>';
								}
								if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_georss' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_georss' ] == 1 ) ) {
									echo '&nbsp;<a tabindex="135" href="' . LEAFLET_PLUGIN_URL . 'leaflet-georss.php?marker=' . $row['markerid'] . '" style="text-decoration:none;" title="' . esc_attr__('Export as GeoRSS','lmm') . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-georss.png" width="14" height="14" alt="' . esc_attr__('Export as GeoRSS','lmm') . '" class="lmm-panel-api-images" /></a>';
								}
								if ( (isset($lmm_options[ 'defaults_layer_listmarkers_api_wikitude' ] ) == TRUE ) && ( $lmm_options[ 'defaults_layer_listmarkers_api_wikitude' ] == 1 ) ) {
									echo '&nbsp;<a tabindex="136" href="' . LEAFLET_PLUGIN_URL . 'leaflet-wikitude.php?marker=' . $row['markerid'] . '" style="text-decoration:none;" title="' . esc_attr__('Export as ARML for Wikitude Augmented-Reality browser','lmm') . '" target="_blank"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-wikitude.png" width="14" height="14" alt="' . esc_attr__('Export as ARML for Wikitude Augmented-Reality browser','lmm') . '" class="lmm-panel-api-images" /></a>';
								}
								echo '</div>';
								if ( (isset($lmm_options[ 'defaults_layer_listmarkers_show_markername' ]) == TRUE ) && ($lmm_options[ 'defaults_layer_listmarkers_show_markername' ] == 1 ) ) {
									if ($lmm_options['defaults_layer_listmarkers_link_action'] != 'disabled') {
										echo '<span class="lmm-listmarkers-markername"><a title="' . esc_attr__('show marker on map','lmm') . '" href="#address" onclick="javascript:listmarkers_action(' . $row['markerid'] . ')">' . stripslashes(htmlspecialchars($row['markername'])) . '</a></span> (<a title="' . esc_attr__('Edit marker','lmm') . ' (ID ' . $row['markerid'].')" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_marker&id=' . $row['markerid'].'">' . __('edit','lmm') . '</a>)';
									} else { 
										echo '<span class="lmm-listmarkers-markername">' . stripslashes(htmlspecialchars($row['markername'])) . '</span> (<a title="' . esc_attr__('Edit marker','lmm') . ' (ID ' . $row['markerid'].')" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_marker&id=' . $row['markerid'].'">' . __('edit','lmm') . '</a>)'; 
									}
								}
								if ( (isset($lmm_options[ 'defaults_layer_listmarkers_show_popuptext' ]) == TRUE ) && ($lmm_options[ 'defaults_layer_listmarkers_show_popuptext' ] == 1 ) ) {
									$sanitize_popuptext_from = array(
										'#<ul(.*?)>(\s)*(<br\s*/?>)*(\s)*<li(.*?)>#si',
										'#</li>(\s)*(<br\s*/?>)*(\s)*<li(.*?)>#si',
										'#</li>(\s)*(<br\s*/?>)*(\s)*</ul>#si',
										'#<ol(.*?)>(\s)*(<br\s*/?>)*(\s)*<li(.*?)>#si',
										'#</li>(\s)*(<br\s*/?>)*(\s)*</ol>#si',
										'#(<br\s*/?>){1}\s*<ul(.*?)>#si',
										'#(<br\s*/?>){1}\s*<ol(.*?)>#si',
										'#</ul>\s*(<br\s*/?>){1}#si',
										'#</ol>\s*(<br\s*/?>){1}#si',
									);
									$sanitize_popuptext_to = array(
										'<ul$1><li$5>',
										'</li><li$4>',
										'</li></ul>',
										'<ol$1><li$5>',
										'</li></ol>',
										'<ul$2>',
										'<ol$2>',
										'</ul>',
										'</ol>'
									);
									$popuptext_sanitized = preg_replace($sanitize_popuptext_from, $sanitize_popuptext_to, stripslashes(preg_replace( '/(\015\012)|(\015)|(\012)/','<br />', $row['mpopuptext'])));
									echo '<br/>' . do_shortcode($popuptext_sanitized);
								}
								if ( (isset($lmm_options[ 'defaults_layer_listmarkers_show_address' ]) == TRUE ) && ($lmm_options[ 'defaults_layer_listmarkers_show_address' ] == 1 ) ) {
									if ( $row['mpopuptext'] == NULL ) {
										echo stripslashes(htmlspecialchars($row['maddress']));
									} else if ( ($row['mpopuptext'] != NULL) && ($row['maddress'] != NULL) ) {
										echo '<br/><div class="lmm-listmarkers-hr">' . stripslashes(htmlspecialchars($row['maddress'])) . '</div>';
									}
								}
								echo '</td></tr>';
							} //info: end foreach
						} //info: end ($layer_marker_list != NULL)
					} //info: end $isedit

					//info: adding info if more markers are available than listed in markers list
					if ($markercount > $lmm_options[ 'defaults_layer_listmarkers_limit' ]) {
						$asc_desc = ($lmm_options['defaults_layer_listmarkers_sort_order'] == 'ASC') ? __('ascending','lmm') : __('descending','lmm');
						if ($lmm_options['defaults_layer_listmarkers_order_by'] == 'm.id') {
							$orderby = 'ID';
						} else if ($lmm_options['defaults_layer_listmarkers_order_by'] == 'm.markername') {
							$orderby = __('marker name','lmm');
						} else if ($lmm_options['defaults_layer_listmarkers_order_by'] == 'm.createdon') {
							$orderby = __('created on','lmm');
						} else if ($lmm_options['defaults_layer_listmarkers_order_by'] == 'm.updatedon') {
							$orderby = __('updated on','lmm');
						} else if ($lmm_options['defaults_layer_listmarkers_order_by'] == 'm.layer') {
							$orderby = __('layer ID','lmm');
						}
						echo '<tr><td colspan="2" style="text-align:center">' . sprintf(__('The table above is listing %1s out of %2s markers (sorted by %3s %4s)','lmm'), intval($lmm_options[ 'defaults_layer_listmarkers_limit' ]), $markercount, $orderby, $asc_desc) . '</td></tr>';
					}
					?>
					</table>
					</div> <!--end lmm-listmarkers-->
					</div><!--end mapsmarker div-->
				</td>
			</tr>
			<tr>
				<td class="lmm-border"><p><strong><label for="multi_layer_map"><?php _e('Multi Layer Map','lmm') ?></label></strong>&nbsp;
					<input style="margin-top:1px;" type="checkbox" name="multi_layer_map" id="multi_layer_map" <?php checked($multi_layer_map, 1 ); ?>><br/>
					<small><?php _e('Show markers from other layers on this map','lmm') ?></small></p>
				</td>
				<td class="lmm-border">
					<?php
					$multi_layer_map_state = ($multi_layer_map == 1) ? 'block' : 'none';
					echo '<div id="lmm-multi_layer_map" style="display:' . $multi_layer_map_state . ';">'.PHP_EOL;
					echo __('Please select the layers, whose markers you would like to display on this multi layer map.','lmm') . ' ' . 		
					__('The following features are not supported for multi layer maps: adding markers directly and dynamic preview on backend.','lmm') . ' ' . 
					__('Please do not change an existing layer map with assigned markers into a multi layer map, as those assigned markers will not be displayed on the multi layer map!','lmm').PHP_EOL;
					$mlm_checked_all = ( in_array('all', $multi_layer_map_list_exploded) ) ? ' checked="checked"' : '';
					echo '<br/><br/><input id="mlm-all" type="checkbox" id="mlm-all" name="mlm-all" ' . $mlm_checked_all . '> <label for="mlm-all">' . __('display all markers','lmm') . '</label><br/><br/><strong>' . __('Display markers from selected layers only','lmm') . '</strong><br/>';
					foreach ($layerlist as $mlmrow){
						if ($markercount_toggle == 'show') { //info: to prevent to many mysql queries
							$mlm_markercount = $wpdb->get_var('SELECT count(*) FROM `'.$table_name_layers.'` as l INNER JOIN `'.$table_name_markers.'` AS m ON m.layer LIKE concat(\'%"\',l.id,\'"%\') WHERE l.id='.$mlmrow['lid']);
						} else { 
							$mlm_markercount = 'hide';
						}
						if ( in_array($mlmrow['lid'], $multi_layer_map_list_exploded) ) {
							$mlm_checked{$mlmrow['lid']} = ' checked="checked"';
						} else {
							$mlm_checked{$mlmrow['lid']} = '';
						}
						if ( ($id != $mlmrow['lid']) || ($mlm_markercount != 0) ) { //info: make current layer selectable for MLM if has markers only
							if ($markercount_toggle == 'show') { 
								echo '<input type="checkbox" id="mlm-'.$mlmrow['lid'].'" name="mlm-'.$mlmrow['lid'].'" ' . $mlm_checked{$mlmrow['lid']} . '> <label for="mlm-'.$mlmrow['lid'].'">' . stripslashes(htmlspecialchars($mlmrow['lname'])) . ' (' . $mlm_markercount . ' ' .  __('marker','lmm') . ', ID ' . $mlmrow['lid'] . ' - <a href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id='.$mlmrow['lid'].'" title="' . esc_attr__('show map','lmm') . '" target="_blank">' . __('show map','lmm') . '</a>)</label><br/>';
							} else {
								if ($isedit) {
									$show_markercount_link = LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id=' . $id . '&markercount_toggle=show';
								} else {
									$show_markercount_link = LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&markercount_toggle=show';
								}
								echo '<input type="checkbox" id="mlm-'.$mlmrow['lid'].'" name="mlm-'.$mlmrow['lid'].'" ' . $mlm_checked{$mlmrow['lid']} . '> <label for="mlm-'.$mlmrow['lid'].'">' . stripslashes(htmlspecialchars($mlmrow['lname'])) . ' (<a href="' . $show_markercount_link . '" title="' . esc_attr__('hidden by default for a better performance','lmm') . '">' .  __('show marker count','lmm') . '</a>), ID ' . $mlmrow['lid'] . ' - <a href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_layer&id='.$mlmrow['lid'].'" title="' . esc_attr__('show map','lmm') . '" target="_blank">' . __('show map','lmm') . '</a></label><br/>';
							}
						}
					};
					echo '</div>'.PHP_EOL;
					?>
				</td>
			</tr>
			
			<tr id="toggle-advanced-settings" style="<?php echo $current_editor_css_audit; ?>">
				<td class="lmm-border"><strong><?php _e('Advanced settings','lmm') ?></strong></td>
				<td class="lmm-border">		
					<p><strong><?php _e('WMS layers','lmm') ?></strong> <?php if (current_user_can('activate_plugins')) { echo '<a tabindex="101" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#wms">(' . __('Settings','lmm') . ')</a>'; } ?></p>
					<?php
					//info: define available wms layers (for markers and layers)
					if ( (isset($lmm_options[ 'wms_wms_available' ] ) == TRUE ) && ( $lmm_options[ 'wms_wms_available' ] == 1 ) ) {
						echo '<input type="checkbox" id="wms" name="wms"';
						if ($wms == 1) { echo ' checked="checked"'; }
						echo '/>&nbsp;<label for="wms">' . strip_tags($lmm_options[ 'wms_wms_name' ]) . ' </label> <a title="' . esc_attr__('WMS layer 1 settings','lmm') . '" tabindex="104" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-wms-sections2"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a><br/>';
					}
					if ( (isset($lmm_options[ 'wms_wms2_available' ] ) == TRUE ) && ( $lmm_options[ 'wms_wms2_available' ] == 1 ) ) {
						echo '<input type="checkbox" id="wms2" name="wms2"';
						if ($wms2 == 1) { echo ' checked="checked"'; }
						echo '/>&nbsp;<label for="wms2">' . strip_tags($lmm_options[ 'wms_wms2_name' ]) . ' </label> <a title="' . esc_attr__('WMS layer 2 settings','lmm') . '" tabindex="104" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-wms-sections3"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a><br/>';
					}
					if ( (isset($lmm_options[ 'wms_wms3_available' ] ) == TRUE ) && ( $lmm_options[ 'wms_wms3_available' ] == 1 ) ) {
						echo '<input type="checkbox" id="wms3" name="wms3"';
						if ($wms3 == 1) { echo ' checked="checked"'; }
						echo '/>&nbsp;<label for="wms3">' . strip_tags($lmm_options[ 'wms_wms3_name' ]) . ' </label> <a title="' . esc_attr__('WMS layer 3 settings','lmm') . '" tabindex="104" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-wms-sections4"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a><br/>';
					}
					if ( (isset($lmm_options[ 'wms_wms4_available' ] ) == TRUE ) && ( $lmm_options[ 'wms_wms4_available' ] == 1 ) ) {
						echo '<input type="checkbox" id="wms4" name="wms4"';
						if ($wms4 == 1) { echo ' checked="checked"'; }
						echo '/>&nbsp;<label for="wms4">' . strip_tags($lmm_options[ 'wms_wms4_name' ]) . ' </label> <a title="' . esc_attr__('WMS layer 4 settings','lmm') . '" tabindex="104" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-wms-sections5"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a><br/>';
					}
					if ( (isset($lmm_options[ 'wms_wms5_available' ] ) == TRUE ) && ( $lmm_options[ 'wms_wms5_available' ] == 1 ) ) {
						echo '<input type="checkbox" id="wms5" name="wms5"';
						if ($wms5 == 1) { echo ' checked="checked"'; }
						echo '/>&nbsp;<label for="wms5">' . strip_tags($lmm_options[ 'wms_wms5_name' ]) . ' </label> <a title="' . esc_attr__('WMS layer 5 settings','lmm') . '" tabindex="104" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-wms-sections6"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a><br/>';
					}
					if ( (isset($lmm_options[ 'wms_wms6_available' ] ) == TRUE ) && ( $lmm_options[ 'wms_wms6_available' ] == 1 ) ) {
						echo '<input type="checkbox" id="wms6" name="wms6"';
						if ($wms6 == 1) { echo ' checked="checked"'; }
						echo '/>&nbsp;<label for="wms6">' . strip_tags($lmm_options[ 'wms_wms6_name' ]) . ' </label> <a title="' . esc_attr__('WMS layer 6 settings','lmm') . '" tabindex="104" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-wms-sections7"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a><br/>';
					}
					if ( (isset($lmm_options[ 'wms_wms7_available' ] ) == TRUE ) && ( $lmm_options[ 'wms_wms7_available' ] == 1 ) ) {
						echo '<input type="checkbox" id="wms7" name="wms7"';
						if ($wms7 == 1) { echo ' checked="checked"'; }
						echo '/>&nbsp;<label for="wms7">' . strip_tags($lmm_options[ 'wms_wms7_name' ]) . ' </label> <a title="' . esc_attr__('WMS layer 7 settings','lmm') . '" tabindex="104" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-wms-sections8"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a><br/>';
					}
					if ( (isset($lmm_options[ 'wms_wms8_available' ] ) == TRUE ) && ( $lmm_options[ 'wms_wms8_available' ] == 1 ) ) {
						echo '<input type="checkbox" id="wms8" name="wms8"';
						if ($wms8 == 1) { echo ' checked="checked"'; }
						echo '/>&nbsp;<label for="wms8">' . strip_tags($lmm_options[ 'wms_wms8_name' ]) . ' </label> <a title="' . esc_attr__('WMS layer 8 settings','lmm') . '" tabindex="104" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-wms-sections9"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a><br/>';
					}
					if ( (isset($lmm_options[ 'wms_wms9_available' ] ) == TRUE ) && ( $lmm_options[ 'wms_wms9_available' ] == 1 ) ) {
						echo '<input type="checkbox" id="wms9" name="wms9"';
						if ($wms9 == 1) { echo ' checked="checked"'; }
						echo '/>&nbsp;<label for="wms9">' . strip_tags($lmm_options[ 'wms_wms9_name' ]) . ' </label> <a title="' . esc_attr__('WMS layer 9 settings','lmm') . '" tabindex="104" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-wms-sections10"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a><br/>';
					}
					if ( (isset($lmm_options[ 'wms_wms10_available' ] ) == TRUE ) && ( $lmm_options[ 'wms_wms10_available' ] == 1 ) ) {
						echo '<input type="checkbox" id="wms10" name="wms10"';
						if ($wms10 == 1) { echo ' checked="checked"'; }
						echo '/>&nbsp;<label for="wms10">' . strip_tags($lmm_options[ 'wms_wms10_name' ]) . ' </label> <a title="' . esc_attr__('WMS layer 10 settings','lmm') . '" tabindex="104" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#lmm-wms-sections11"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a>';
					}
					?>
					
					<?php
					if (current_user_can('activate_plugins')) {
						if ( $lmm_options['misc_backlinks'] == 'show' ) {
							echo '<hr style="border:none;color:#edecec;background:#edecec;height:1px;"><strong>' . __('Hide MapsMarker.com backlinks','lmm') .'</strong>: ';
							echo '<a tabindex="110" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_settings#misc">' . __('Please visit Settings / Misc to disable MapsMarker.com backlinks','lmm') . '</a>';
						}
					}
					?>
					<?php if (current_user_can('activate_plugins')) { ?>
					<hr style="border:none;color:#edecec;background:#edecec;height:1px;">
					<strong><?php _e('Minimap settings','lmm'); ?> </strong>
					<a tabindex="110" href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_settings#mapdefaults-section17"><?php _e('Please visit Settings / Maps / Minimap settings','lmm'); ?></a>
					<hr style="border:none;color:#edecec;background:#edecec;height:1px;">
					<strong><?php _e('Geolocate settings','lmm'); ?> </strong>
					<a tabindex="111" href="<?php echo LEAFLET_WP_ADMIN_URL; ?>admin.php?page=leafletmapsmarker_settings#mapdefaults-section20"><?php _e('Please visit Settings / Maps / Geolocate settings','lmm'); ?></a>
					<?php } ?>
				</td>
			</tr>
			
			<?php if ($isedit) { ?>
			<tr id="toggle-audit" style="<?php echo $current_editor_css_audit; ?>">
				<td class="lmm-border"><small><strong><?php _e('Audit','lmm') ?>:</strong></small></td>
				<td class="lmm-border"><small>
					<script type="text/javascript">
						var $j = jQuery.noConflict();
						$j(function() {
						$j("#createdon").datetimepicker({
							dateFormat: 'yy-mm-dd',
							changeMonth: true,
							changeYear: true,
							timeText: '<?php esc_attr_e('Time','lmm'); ?>',
							hourText: '<?php esc_attr_e('Hour','lmm'); ?>',
							minuteText: '<?php esc_attr_e('Minute','lmm'); ?>',
							secondText: '<?php esc_attr_e('Second','lmm'); ?>',
							currentText: '<?php esc_attr_e('Now','lmm'); ?>',
							closeText: '<?php esc_attr_e('Add','lmm'); ?>',
							timeFormat: 'HH:mm:ss',
							showSecond: true,
						});});
					</script>
					<?php
					echo __('Layer added by','lmm') . ' ';
					if (current_user_can('activate_plugins')) {
						echo '<input title="' . esc_attr__('Please use valid WordPress usernames as otherwise non-admins might not be able to access this map on backend (depending on your access settings)','lmm') . '" type="text" id="createdby" name="createdby" value="' . $lcreatedby . '" style="font-size:small;width:110px;height:24px;" />';
						echo '<input type="text" id="createdon" name="createdon" value="' . $lcreatedon . '" style="font-size:small;width:138px;height:24px;" /> ';
						if ($lupdatedon != $lcreatedon) {
							echo __('last update by','lmm');
							echo ' ' . $lupdatedby . ' - ' . $lupdatedon;
						}					
					} else {
						echo '<input type="hidden" id="createdby" name="createdby" value="' . $lcreatedby . '" />';
						echo '<input type="hidden" id="createdon" name="createdon" value="' . $lcreatedon . '" /> ';
						echo $lcreatedby . ' - ' . $lcreatedon;
						if ($lupdatedon != $lcreatedon) {
							echo ', ' . __('last update by','lmm');
							echo ' ' . $lupdatedby . ' - ' . $lupdatedon;
						}; 
					}	
					?>
					</small></td>
			</tr>
			<?php }; ?>
		</table>

		<table><tr><td>
		<?php
			if ($isedit === true) { $edit_button_css = ''; } else { $edit_button_css = 'margin-top:17px;'; }
			if (lmm_check_capability_edit($lcreatedby) == TRUE) {
				if ($isedit === true) { $button_text = __('update','lmm'); } else { $button_text = __('publish','lmm'); }
				echo '<input id="submit_bottom" style="font-weight:bold;' . $edit_button_css . '" type="submit" name="layer" class="button button-primary" value="' . $button_text . '" />';
			} else {
				if ($isedit === true) { 
					echo __('Your user does not have the permission to update this layer!','lmm');
				} else { 
					$button_text = __('publish','lmm'); 
					echo '<input id="submit_bottom" style="font-weight:bold;' . $edit_button_css . '" type="submit" name="layer" class="button button-primary" value="' . $button_text . '" />';
				}
			}
		?>
		</form>
		</td>

		<?php if ($isedit) { ?>
		<td>
		<?php
			if (lmm_check_capability_edit($lcreatedby) == TRUE) {
				echo '<form method="post">';
				wp_nonce_field('layer-nonce');
				echo '<input type="hidden" name="id" value="' . $id . '" />';
				echo '<input type="hidden" name="action" value="duplicate" />';
				echo '<div class="submit" style="margin:0 0 0 40px;">';
				echo '<input class="button button-secondary" type="submit" name="layer" value="' . __('duplicate layer only', 'lmm') . '" />';
				echo '</div></form>';
			} else {
				echo '<span style="margin-left:20px;">' . __('Your user does not have the permission to duplicate this layer!','lmm') . '</span>';
			}
		?>
		</td>
		<td>
			<?php
			if (lmm_check_capability_delete($lcreatedby) == TRUE) {
				echo '<form method="post">';
				wp_nonce_field('layer-nonce');
				echo '<input type="hidden" name="id" value="' . $id . '" />';
				echo '<input type="hidden" name="action" value="delete" />';
				$confirm = sprintf( esc_attr__('Do you really want to delete layer %1$s (ID %2$s)?','lmm'), $row['lname'], $id);
				echo '<div class="submit" style="margin:0 0 0 40px;">';
				echo '<input id="delete" class="button button-secondary" style="color:#FF0000;" type="submit" name="layer" value="' . __('delete', 'lmm') . '" onclick="return confirm(\'' . $confirm . '\')" />';
				echo '</div></form>';
			} else {
				echo '<span style="margin-left:20px;">' . __('Your user does not have the permission to delete this layer!','lmm') . '</span>';
			}
			?>
		</td>
		<td>
			<?php
			if (lmm_check_capability_delete($lcreatedby) == TRUE) {
				echo '<form method="post">';
				wp_nonce_field('layer-nonce');
				echo '<input type="hidden" name="id" value="' . $id . '" />';
				echo '<input type="hidden" name="action" value="deleteboth" />';
				$confirm2 = sprintf( esc_attr__('Do you really want to delete layer %1$s (ID %2$s) and all assigned markers? (if a marker is assigned to multiple layers only the reference to the layer will be removed)','lmm'), $row['lname'], $id);
				if ($multi_layer_map == 0) {
					echo "<input id='delete_layer_and_markers' class='button button-secondary' style='color:#FF0000;margin-left:40px;' type='submit' name='layer' value='" . __('delete layer AND assigned markers', 'lmm') . "' onclick='return confirm(\"".$confirm2 ."\")' />";
				}
				echo '</div></form>';
			} else {
				echo '<span style="margin-left:20px;">' . __('Your user does not have the permission to delete this layer and all assigned markers!','lmm') . '</span>';
			}
			?>
		</td>
		<?php } ?>
		</tr></table>

		<?php if ($isedit) { ?>
		<h3 style="font-size:23px;" id="assigned_markers">
			<?php
			if ($multi_layer_map == 0) {
				$assigned_markers_layername = sprintf(__('Markers assigned to layer "%1s" (ID %2s)','lmm'), $name, $id);
				echo $assigned_markers_layername;
			} else if ($multi_layer_map == 1) {
				$assigned_markers_layername = sprintf(__('Markers assigned to multi layer map "%1s" (ID %2s)','lmm'), $name, $id);
				echo $assigned_markers_layername;
			}
			?>
		</h3>
		<p>
			<?php _e('Total','lmm') ?>: <?php echo $markercount; ?> <?php _e('markers','lmm') ?>
		</p>
		<p> 
		<?php
		if ($multi_layer_map == 0) {
			echo "<a href=\"" . LEAFLET_WP_ADMIN_URL . "admin.php?page=leafletmapsmarker_marker&addtoLayer=$id\" style=\"text-decoration:none;\"><img src=\"" . LEAFLET_PLUGIN_URL . "inc/img/icon-add.png\" /></a> <a href=\"" . LEAFLET_WP_ADMIN_URL . "admin.php?page=leafletmapsmarker_marker&addtoLayer=$id\" style=\"text-decoration:none;\">" . __('add new marker to this layer','lmm') . "</a>";
		} 
		?> 
		</p>
		<table cellspacing="0" class="wp-list-table widefat fixed">
			<thead>
				<tr>
					<!--<th class="manage-column column-cb check-column" scope="col"><input type="checkbox"></th>-->
					<th class="manage-column column-id" scope="col"><span>ID</span></span></th>
					<th class="manage-column column-icon" scope="col"><span><?php _e('Icon', 'lmm') ?></span></span></th>
					<th class="manage-column column-markername" scope="col"><span><?php _e('Marker name','lmm') ?></span></span></a></th>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_address' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_address' ] == 1 )) { ?>
					<th class="manage-column column-address" scope="col"><span><?php _e('Location','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_popuptext' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_popuptext' ] == 1 )) { ?>
					<th class="manage-column column-popuptext" scope="col"><span><?php _e('Popup text','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_layername' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_layername' ] == 1 )) { ?>
					<th class="manage-column column-layername" scope="col"><span><?php _e('Layer name','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_openpopup' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_openpopup' ] == 1 )) { ?>
					<th class="manage-column column-openpopup"><span><?php _e('Popup status', 'lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_coordinates' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_coordinates' ] == 1 )) { ?>
					<th class="manage-column column-coords" scope="col"><?php _e('Coordinates', 'lmm') ?></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_mapsize' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_mapsize' ] == 1 )) { ?>
					<th class="manage-column column-mapsize" scope="col"><?php _e('Map size','lmm') ?></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_zoom' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_zoom' ] == 1 )) { ?>
					<th class="manage-column column-zoom" scope="col"><span><?php _e('Zoom', 'lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_basemap' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_basemap' ] == 1 )) { ?>
					<th class="manage-column column-basemap" scope="col"><span><?php _e('Basemap', 'lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_createdby' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_createdby' ] == 1 )) { ?>
					<th class="manage-column column-createdby" scope="col"><span><?php _e('Created by','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_createdon' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_createdon' ] == 1 )) { ?>
					<th class="manage-column column-createdon" scope="col"><span><?php _e('Created on','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_updatedby' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_updatedby' ] == 1 )) { ?>
					<th class="manage-column column-updatedby" scope="col"><span><?php _e('Updated by','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_updatedon' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_updatedon' ] == 1 )) { ?>
					<th class="manage-column column-updatedon" scope="col"><span><?php _e('Updated on','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_controlbox' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_controlbox' ] == 1 )) { ?>
					<th class="manage-column column-code" scope="col"><span><?php _e('Controlbox status','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_shortcode' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_shortcode' ] == 1 )) { ?>
					<th class="manage-column column-code" scope="col"><?php _e('Shortcode', 'lmm') ?></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_kml' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_kml' ] == 1 )) { ?>
					<th class="manage-column column-kml" scope="col">KML<a href="https://www.mapsmarker.com/kml" target="_blank" title="<?php esc_attr_e('Click here for more information on how to use as KML in Google Earth or Google Maps','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_fullscreen' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_fullscreen' ] == 1 )) { ?>
					<th class="manage-column column-fullscreen" scope="col"><?php _e('Fullscreen', 'lmm') ?><span title="<?php esc_attr_e('Open standalone map in fullscreen mode','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_qr_code' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_qr_code' ] == 1 )) { ?>
					<th class="manage-column column-qr-code" scope="col"><?php _e('QR code', 'lmm') ?><span title="<?php esc_attr_e('Create QR code image for standalone map in fullscreen mode','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_geojson' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_geojson' ] == 1 )) { ?>
					<th class="manage-column column-geojson" scope="col">GeoJSON<a href="https://www.mapsmarker.com/geojson" target="_blank" title="<?php esc_attr_e('Click here for more information on how to integrate GeoJSON into external websites or apps','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_georss' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_georss' ] == 1 )) { ?>
					<th class="manage-column column-georss" scope="col">GeoRSS<a href="https://www.mapsmarker.com/georss" target="_blank" title="<?php esc_attr_e('Click here for more information on how to subscribe to new markers via GeoRSS','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_wikitude' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_wikitude' ] == 1 )) { ?>
					<th class="manage-column column-wikitude" scope="col">Wikitude<a href="https://www.mapsmarker.com/wikitude" target="_blank" title="<?php esc_attr_e('Click here for more information on how to display in Wikitude Augmented-Reality browser','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<!--<th class="manage-column column-cb check-column" scope="col"><input type="checkbox"></th>-->
					<th class="manage-column column-id" scope="col"><span>ID</span></span></th>
					<th class="manage-column column-icon" scope="col"><span><?php _e('Icon', 'lmm') ?></span></span></th>
					<th class="manage-column column-markername" scope="col"><span><?php _e('Marker name','lmm') ?></span></span></a></th>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_address' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_address' ] == 1 )) { ?>
					<th class="manage-column column-address" scope="col"><span><?php _e('Location','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_popuptext' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_popuptext' ] == 1 )) { ?>
					<th class="manage-column column-popuptext" scope="col"><span><?php _e('Popup text','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_layername' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_layername' ] == 1 )) { ?>
					<th class="manage-column column-layername" scope="col"><span><?php _e('Layer name','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_openpopup' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_openpopup' ] == 1 )) { ?>
					<th class="manage-column column-openpopup"><span><?php _e('Popup status', 'lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_coordinates' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_coordinates' ] == 1 )) { ?>
					<th class="manage-column column-coords" scope="col"><?php _e('Coordinates', 'lmm') ?></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_mapsize' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_mapsize' ] == 1 )) { ?>
					<th class="manage-column column-mapsize" scope="col"><?php _e('Map size','lmm') ?></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_zoom' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_zoom' ] == 1 )) { ?>
					<th class="manage-column column-zoom" scope="col"><span><?php _e('Zoom', 'lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_basemap' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_basemap' ] == 1 )) { ?>
					<th class="manage-column column-basemap" scope="col"><span><?php _e('Basemap', 'lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_createdby' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_createdby' ] == 1 )) { ?>
					<th class="manage-column column-createdby" scope="col"><span><?php _e('Created by','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_createdon' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_createdon' ] == 1 )) { ?>
					<th class="manage-column column-createdon" scope="col"><span><?php _e('Created on','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_updatedby' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_updatedby' ] == 1 )) { ?>
					<th class="manage-column column-updatedby" scope="col"><span><?php _e('Updated by','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_updatedon' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_updatedon' ] == 1 )) { ?>
					<th class="manage-column column-updatedon" scope="col"><span><?php _e('Updated on','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_controlbox' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_controlbox' ] == 1 )) { ?>
					<th class="manage-column column-code" scope="col"><span><?php _e('Controlbox status','lmm') ?></span></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_shortcode' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_shortcode' ] == 1 )) { ?>
					<th class="manage-column column-code" scope="col"><?php _e('Shortcode', 'lmm') ?></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_kml' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_kml' ] == 1 )) { ?>
					<th class="manage-column column-kml" scope="col">KML<a href="https://www.mapsmarker.com/kml" target="_blank" title="<?php esc_attr_e('Click here for more information on how to use as KML in Google Earth or Google Maps','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_fullscreen' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_fullscreen' ] == 1 )) { ?>
					<th class="manage-column column-fullscreen" scope="col"><?php _e('Fullscreen', 'lmm') ?><span title="<?php esc_attr_e('Open standalone map in fullscreen mode','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_qr_code' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_qr_code' ] == 1 )) { ?>
					<th class="manage-column column-qr-code" scope="col"><?php _e('QR code', 'lmm') ?><span title="<?php esc_attr_e('Create QR code image for standalone map in fullscreen mode','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></span></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_geojson' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_geojson' ] == 1 )) { ?>
					<th class="manage-column column-geojson" scope="col">GeoJSON<a href="https://www.mapsmarker.com/geojson" target="_blank" title="<?php esc_attr_e('Click here for more information on how to integrate GeoJSON into external websites or apps','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_georss' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_georss' ] == 1 )) { ?>
					<th class="manage-column column-georss" scope="col">GeoRSS<a href="https://www.mapsmarker.com/georss" target="_blank" title="<?php esc_attr_e('Click here for more information on how to subscribe to new markers via GeoRSS','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
					<?php if ((isset($lmm_options[ 'misc_marker_listing_columns_wikitude' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_wikitude' ] == 1 )) { ?>
					<th class="manage-column column-wikitude" scope="col">Wikitude<a href="https://www.mapsmarker.com/wikitude" target="_blank" title="<?php esc_attr_e('Click here for more information on how to display in Wikitude Augmented-Reality browser','lmm') ?>">&nbsp;<img src="<?php echo LEAFLET_PLUGIN_URL ?>inc/img/icon-question-mark.png" width="12" height="12" border="0"/></a></th><?php } ?>
				</tr>
			</tfoot>
			<tbody id="the-list">
				<?php
				$markernonce = wp_create_nonce('massaction-nonce'); //info: for delete-links
				if (count($layer_marker_list_table) < 1) {
					echo '<tr><td colspan="7">'.__('No marker assigned to this layer', 'lmm').'</td></tr>';
				} else {
					foreach ($layer_marker_list_table as $row){
						//info: delete link
						if (lmm_check_capability_delete($row['mcreatedby']) == TRUE) {
							$confirm3 = sprintf( esc_attr__('Do you really want to delete marker %1$s (ID %2$s)?','lmm'), stripslashes($row['markername']), $row['markerid']);
							$delete_link_marker = ' | </span><span class="delete"><a onclick="if ( confirm( \'' . $confirm3 . '\' ) ) { return true;}return false;" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_markers&action=delete&id='.$row['markerid'].'&_wpnonce=' . $markernonce . '" class="submitdelete">' . __('delete','lmm') . '</a></span>';
						} else {
							$delete_link_marker = '';
						}
						if (lmm_check_capability_edit($row['mcreatedby']) == TRUE) {
							$edit_link_marker = '<strong><a title="' . esc_attr__('Edit marker','lmm') . ' (ID ' . $row['markerid'].')" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_marker&id=' . $row['markerid'].'" class="row-title">' . stripslashes(htmlspecialchars($row['markername'])) . '</a></strong><br/><div class="row-actions"><span class="edit"><a href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_marker&id='.$row['markerid'].'">' . __('edit','lmm') . '</a>';
						} else {
							$edit_link_marker = '<strong><a title="' . esc_attr__('View marker','lmm') . ' (ID ' . $row['markerid'].')" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_marker&id=' . $row['markerid'].'" class="row-title">' . stripslashes(htmlspecialchars($row['markername'])) . '</a></strong><br/><div class="row-actions"><span class="edit"><a href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_marker&id='.$row['markerid'].'">' . __('view','lmm') . '</a>';
						}
						//info: set column display variables - need for for-each
						$column_layer_name = '<td class="lmm-border">' . $row['lname'] . '</td>';
						$column_address = ((isset($lmm_options[ 'misc_marker_listing_columns_address' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_address' ] == 1 )) ? '<td class="lmm-border">' . stripslashes(htmlspecialchars($row['maddress'])) . '</td>' : '';
						$column_openpopup = ((isset($lmm_options[ 'misc_marker_listing_columns_openpopup' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_openpopup' ] == 1 )) ? '<td class="lmm-border">' . $row['mopenpopup'] . '</td>' : '';
						$column_coordinates = ((isset($lmm_options[ 'misc_marker_listing_columns_coordinates' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_coordinates' ] == 1 )) ? '<td class="lmm-border">Lat: ' . $row['mlat'] . '<br/>Lon: ' . $row['mlon'] . '</td>' : '';
						$column_mapsize = ((isset($lmm_options[ 'misc_marker_listing_columns_mapsize' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_mapsize' ] == 1 )) ? '<td class="lmm-border">' . __('Width','lmm') . ': '.$row['mmapwidth'].$row['mmapwidthunit'].'<br/>' . __('Height','lmm') . ': '.$row['mmapheight'].'px</td>' : '';
						$column_zoom = ((isset($lmm_options[ 'misc_marker_listing_columns_zoom' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_zoom' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border">' . $row['mzoom'] . '</td>' : '';
						$column_controlbox = ((isset($lmm_options[ 'misc_marker_listing_columns_controlbox' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_controlbox' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border">'.$row['mcontrolbox'].'</td>' : '';
						$column_shortcode = ((isset($lmm_options[ 'misc_marker_listing_columns_shortcode' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_shortcode' ] == 1 )) ? '<td class="lmm-border"><input ' . $shortcode_select . ' style="width:206px;background:#f3efef;" type="text" value="[' . htmlspecialchars($lmm_options[ 'shortcode' ]) . ' marker=&quot;' . $row['markerid'] . '&quot;]" readonly></td>' : '';
						$column_kml = ((isset($lmm_options[ 'misc_marker_listing_columns_kml' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_kml' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border"><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-kml.php?marker=' . $row['markerid'] . '&name=' . $lmm_options[ 'misc_kml' ] . '" title="' . esc_attr__('Export as KML for Google Earth/Google Maps','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-kml.png" width="14" height="14" alt="' . esc_attr__('Export as KML for Google Earth/Google Maps','lmm') . '" /><br/>KML</a></td>' : '';
						$column_fullscreen = ((isset($lmm_options[ 'misc_marker_listing_columns_fullscreen' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_fullscreen' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border"><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-fullscreen.php?marker=' . $row['markerid'] . '" target="_blank" title="' . esc_attr__('Open standalone map in fullscreen mode','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-fullscreen.png" width="14" height="14" alt="' . esc_attr__('Open standalone map in fullscreen mode','lmm') . '"><br/>' . __('Fullscreen','lmm') . '</a></td>' : '';
						$column_qr_code = ((isset($lmm_options[ 'misc_marker_listing_columns_qr_code' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_qr_code' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border"><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-qr.php?marker=' . $row['markerid'] . '" target="_blank" title="' . esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-qr-code.png" width="14" height="14" alt="' . esc_attr__('Create QR code image for standalone map in fullscreen mode','lmm') . '"><br/>' . __('QR code','lmm') . '</a></td>' : '';
						$column_geojson = ((isset($lmm_options[ 'misc_marker_listing_columns_geojson' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_geojson' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border"><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?marker=' . $row['markerid'] . '&callback=jsonp&full=yes&full_icon_url=yes" target="_blank" title="' . esc_attr__('Export as GeoJSON','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-json.png" width="14" height="14" alt="' . esc_attr__('Export as GeoJSON','lmm') . '"><br/>GeoJSON</a></td>' : '';
						$column_georss = ((isset($lmm_options[ 'misc_marker_listing_columns_georss' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_georss' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border"><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-georss.php?marker=' . $row['markerid'] . '" target="_blank" title="' . esc_attr__('Export as GeoRSS','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-georss.png" width="14" height="14" alt="' . esc_attr__('Export as GeoRSS','lmm') . '"><br/>GeoRSS</a></td>' : '';
						$column_wikitude = ((isset($lmm_options[ 'misc_marker_listing_columns_wikitude' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_wikitude' ] == 1 )) ? '<td style="text-align:center;" class="lmm-border"><a href="' . LEAFLET_PLUGIN_URL . 'leaflet-wikitude.php?marker=' . $row['markerid'] . '" target="_blank" title="' . esc_attr__('Export as ARML for Wikitude Augmented-Reality browser','lmm') . '"><img src="' . LEAFLET_PLUGIN_URL . 'inc/img/icon-wikitude.png" width="14" height="14" alt="' . esc_attr__('Export as ARML for Wikitude Augmented-Reality browser','lmm') . '"><br/>Wikitude</a></td>' : '';
						$column_basemap = ((isset($lmm_options[ 'misc_marker_listing_columns_basemap' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_basemap' ] == 1 )) ? '<td class="lmm-border">' . $row['mbasemap'] . '</td>' : '';
						$column_createdby = ((isset($lmm_options[ 'misc_marker_listing_columns_createdby' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_createdby' ] == 1 )) ? '<td class="lmm-border">' . $row['mcreatedby'] . '</td>' : '';
						$column_createdon = ((isset($lmm_options[ 'misc_marker_listing_columns_createdon' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_createdon' ] == 1 )) ? '<td class="lmm-border">' . $row['mcreatedon'] . '</td>' : '';
						$column_updatedby = ((isset($lmm_options[ 'misc_marker_listing_columns_updatedby' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_updatedby' ] == 1 )) ? '<td class="lmm-border">' . $row['mupdatedby'] . '</td>' : '';
						$column_updatedon = ((isset($lmm_options[ 'misc_marker_listing_columns_updatedon' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_updatedon' ] == 1 )) ? '<td class="lmm-border">' . $row['mupdatedon'] . '</td>' : '';
						$openpopupstatus = ($row['mopenpopup'] == 1) ? __('open','lmm') : __('closed','lmm');
						$popuptextabstract = (strlen($row['mpopuptext']) >= 90) ? "...": "";
						$column_popuptext = ((isset($lmm_options[ 'misc_marker_listing_columns_popuptext' ] ) == TRUE ) && ( $lmm_options[ 'misc_marker_listing_columns_popuptext' ] == 1 )) ?
						'<td class="lmm-border"><a title="' . esc_attr__('Edit marker', 'lmm') . ' ' . $row['markerid'] . '" href="' . LEAFLET_WP_ADMIN_URL . 'admin.php?page=leafletmapsmarker_marker&id=' . $row['markerid'] . '" >' . mb_substr(strip_tags(stripslashes($row['mpopuptext'])), 0, 90) . $popuptextabstract . '</a></td>' : '';
						if (lmm_check_capability_edit($row['mcreatedby']) == TRUE) { 
							$css_table_background = '';
						} else {
							$css_table_background = 'background:#f6f6f6;';
						}
						echo '<tr valign="middle" class="alternate" id="link-'.$row['markerid'].'" style="' . $css_table_background . '">
							<td class="lmm-border">'.$row['markerid'].'</td>
							<td class="lmm-border">';
						if ($row['micon'] != null) {
							echo '<img src="' . $defaults_marker_icon_url . '/'.$row['micon'].'" title="'.$row['micon'].'" />';
						} else {
							echo '<img src="' . LEAFLET_PLUGIN_URL . 'leaflet-dist/images/marker.png" title="' . esc_attr__('standard icon','lmm') . '" />';
						};
						echo '</td>
							<td class="lmm-border">' . $edit_link_marker . $delete_link_marker . '</div></td>
						' . $column_address . '
						' . $column_popuptext . '
						' . $column_layer_name . '
						' . $column_openpopup . '
						' . $column_coordinates . '
						' . $column_mapsize . '
						' . $column_zoom . '
						' . $column_basemap . '
						' . $column_createdby . '
						' . $column_createdon . '
						' . $column_updatedby . '
						' . $column_updatedon . '
						' . $column_controlbox . '
						' . $column_shortcode . '
						' . $column_kml . '
						' . $column_fullscreen . '
						' . $column_qr_code . '
						' . $column_geojson . '
						' . $column_georss . '
						' . $column_wikitude . '
						  </tr>';
					}//info: end foreach
				}
				?>
			</tbody>
		</table>
			<p>
			<?php
			if ($multi_layer_map == 0) {
				echo "<a href=\"" . LEAFLET_WP_ADMIN_URL . "admin.php?page=leafletmapsmarker_marker&addtoLayer=$id\" style=\"text-decoration:none;\"><img src=\"" . LEAFLET_PLUGIN_URL . "inc/img/icon-add.png\" /></a> <a href=\"" . LEAFLET_WP_ADMIN_URL . "admin.php?page=leafletmapsmarker_marker&addtoLayer=$id\" style=\"text-decoration:none;\">" . __('add new marker to this layer','lmm') . "</a>";
			} 
			?>
			</p>
		<?php } //end $isedit ?>
		<!--isedit-->
	</div>
	<!--wrap-->
	<script type="text/javascript">
	/* //<![CDATA[ */
	var marker,selectlayer,googleLayer_roadmap,googleLayer_satellite,googleLayer_hybrid,googleLayer_terrain,bingaerial,bingaerialwithlabels,bingroad,osm_mapnik,mapquest_osm,mapquest_aerial,ogdwien_basemap,ogdwien_satellite,mapbox,mapbox2,mapbox3,custom_basemap,custom_basemap2,custom_basemap3,empty_basemap,overlays_custom,overlays_custom2,overlays_custom3,overlays_custom4,wms,wms2,wms3,wms4,wms5,wms6,wms7,wms8,wms9,wms10,layersControl,markercluster,geojson_markers;
	var markerID = {};
	
	(function($) {
		selectlayer = new L.Map("selectlayer", { dragging: <?php echo $lmm_options['misc_map_dragging'] ?>, touchZoom: <?php echo $lmm_options['misc_map_touchzoom'] ?>, scrollWheelZoom: <?php echo $lmm_options['misc_map_scrollwheelzoom'] ?>, doubleClickZoom: <?php echo $lmm_options['misc_map_doubleclickzoom'] ?>, boxzoom: <?php echo $lmm_options['map_interaction_options_boxzoom'] ?>, trackResize: <?php echo $lmm_options['misc_map_trackresize'] ?>, worldCopyJump: <?php echo $lmm_options['map_interaction_options_worldcopyjump'] ?>, closePopupOnClick: <?php echo $lmm_options['misc_map_closepopuponclick'] ?>, keyboard: <?php echo $lmm_options['map_keyboard_navigation_options_keyboard'] ?>, keyboardPanOffset: <?php echo intval($lmm_options['map_keyboard_navigation_options_keyboardpanoffset']) ?>, keyboardZoomOffset: <?php echo intval($lmm_options['map_keyboard_navigation_options_keyboardzoomoffset']) ?>, inertia: <?php echo $lmm_options['map_panning_inertia_options_inertia'] ?>, inertiaDeceleration: <?php echo intval($lmm_options['map_panning_inertia_options_inertiadeceleration']) ?>, inertiaMaxSpeed: <?php echo intval($lmm_options['map_panning_inertia_options_inertiamaxspeed']) ?>, zoomControl: <?php echo $lmm_options['misc_map_zoomcontrol'] ?>, crs: <?php echo $lmm_options['misc_projections'] ?>, fullscreenControl: <?php echo $lmm_options['map_fullscreen_button'] ?> });
		<?php
			if ( $lmm_options['misc_backlinks'] == 'show' ) {
				$attrib_prefix_affiliate = ($lmm_options['affiliate_id'] == NULL) ? 'go' : intval($lmm_options['affiliate_id']) . '.html';
				$attrib_prefix = '<a tabindex=\"115\" href=\"https://www.mapsmarker.com/' . $attrib_prefix_affiliate . '\" target=\"_blank\" title=\"' . esc_attr__('Maps Marker Pro - #1 mapping plugin for WordPress','lmm') . '\">MapsMarker.com</a> (<a href=\"http://www.leafletjs.com\" target=\"_blank\" title=\"' . esc_attr__('Leaflet Maps Marker is based on the javascript library Leaflet maintained by Vladimir Agafonkin and Cloudmade','lmm') . '\">Leaflet</a>/<a href=\"https://mapicons.mapsmarker.com\" target=\"_blank\" title=\"' . esc_attr__('Leaflet Maps Marker uses icons from the Maps Icons Collection maintained by Nicolas Mollet','lmm') . '\">icons</a>/<a href=\"http://www.visualead.com/go\" target=\"_blank\" rel=\"nofollow\" title=\"' . esc_attr__('Visual QR codes for fullscreen maps are created by Visualead.com','lmm') . '\">QR</a>)';
			} else {
				$attrib_prefix = '';
			}
			$osm_editlink = ($lmm_options['misc_map_osm_editlink'] == 'show') ? '&nbsp;(<a href=\"http://www.openstreetmap.org/edit?editor=' . $lmm_options['misc_map_osm_editlink_editor'] . '&amp;lat=' . $layerviewlat . '&amp;lon=' . $layerviewlon . '&zoom=' . $layerzoom . '\" target=\"_blank\" title=\"' . esc_attr__('help OpenStreetMap.org to improve map details','lmm') . '\">' . __('edit','lmm') . '</a>)' : '';
			$attrib_osm_mapnik = __("Map",'lmm').': &copy; <a tabindex=\"123\" href=\"http://www.openstreetmap.org/copyright\" target=\"_blank\">' . __('OpenStreetMap contributors','lmm') . '</a>' . $osm_editlink;
			$attrib_mapquest_osm = __("Map",'lmm').': Tiles Courtesy of <a tabindex=\"125\" href=\"http://www.mapquest.com/\" target=\"_blank\">MapQuest</a> <img src=\"' . LEAFLET_PLUGIN_URL . 'inc/img/logo-mapquest.png\" style=\"display:inline;\" /> - &copy; <a tabindex=\"126\" href=\"http://www.openstreetmap.org/copyright\" target=\"_blank\">' . __('OpenStreetMap contributors','lmm') . '</a>' . $osm_editlink;
			$attrib_mapquest_aerial = __("Map",'lmm').': <a href=\"http://www.mapquest.com/\" target=\"_blank\">MapQuest</a> <img src=\"' . LEAFLET_PLUGIN_URL . 'inc/img/logo-mapquest.png\" style=\"display:inline;\" />, Portions Courtesy NASA/JPL-Caltech and U.S. Depart. of Agriculture, Farm Service Agency';
			$attrib_ogdwien_basemap = __("Map",'lmm').': ' . __("City of Vienna","lmm") . ' (<a href=\"http://data.wien.gv.at\" target=\"_blank\" style=\"\">data.wien.gv.at</a>)';
			$attrib_ogdwien_satellite = __("Map",'lmm').': ' . __("City of Vienna","lmm") . ' (<a href=\"http://data.wien.gv.at\" target=\"_blank\">data.wien.gv.at</a>)';
			$attrib_custom_basemap = __("Map",'lmm').': ' . addslashes(wp_kses($lmm_options[ 'custom_basemap_attribution' ], $allowedtags));
			$attrib_custom_basemap2 = __("Map",'lmm').': ' . addslashes(wp_kses($lmm_options[ 'custom_basemap2_attribution' ], $allowedtags));
			$attrib_custom_basemap3 = __("Map",'lmm').': ' . addslashes(wp_kses($lmm_options[ 'custom_basemap3_attribution' ], $allowedtags));
		?>
		selectlayer.attributionControl.setPrefix("<?php echo $attrib_prefix; ?>");
		
		<?php 
		$maxzoom = intval($lmm_options['global_maxzoom_level']); 
		if (is_ssl() == TRUE) {
			$protocol_handler = 'https';
			$mapquest_ssl = '-s';
		} else {
			$protocol_handler = 'http';
			$mapquest_ssl = '';
		}
		?>
		osm_mapnik = new L.TileLayer("<?php echo $protocol_handler; ?>://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {mmid: 'osm_mapnik', maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: 18, minZoom: 1, errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", attribution: "<?php echo $attrib_osm_mapnik; ?>", detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		mapquest_osm = new L.TileLayer("<?php echo $protocol_handler; ?>://{s}<?php echo $mapquest_ssl; ?>.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png", {mmid: 'mapquest_osm', maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: 18, minZoom: 1, errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", attribution: "<?php echo $attrib_mapquest_osm; ?>", subdomains: ['otile1','otile2','otile3','otile4'], detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		mapquest_aerial = new L.TileLayer("<?php echo $protocol_handler; ?>://{s}<?php echo $mapquest_ssl; ?>.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.png", {mmid: 'mapquest_aerial', maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: 11, minZoom: 1, errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", attribution: "<?php echo $attrib_mapquest_aerial; ?>", subdomains: ['otile1','otile2','otile3','otile4'], detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		<?php if ($lmm_options['google_maps_api_status'] == 'enabled') { ?>
			googleLayer_roadmap = new L.Google("ROADMAP", {mmid: 'googleLayer_roadmap', detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
			googleLayer_satellite = new L.Google("SATELLITE", {mmid: 'googleLayer_satellite', detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
			googleLayer_hybrid = new L.Google("HYBRID", {mmid: 'googleLayer_hybrid', detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
			googleLayer_terrain = new L.Google("TERRAIN", {mmid: 'googleLayer_terrain', detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		<?php }; ?>
		<?php if ( isset($lmm_options['bingmaps_api_key']) && ($lmm_options['bingmaps_api_key'] != NULL ) ) { ?>
			bingaerial = new L.BingLayer("<?php echo htmlspecialchars($lmm_options[ 'bingmaps_api_key' ]); ?>", {mmid: 'bingaerial', type: 'Aerial', maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: 19, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
			bingaerialwithlabels = new L.BingLayer("<?php echo htmlspecialchars($lmm_options[ 'bingmaps_api_key' ]); ?>", {mmid: 'bingaerialwithlabels', type: 'AerialWithLabels', maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: 19, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
			bingroad = new L.BingLayer("<?php echo htmlspecialchars($lmm_options[ 'bingmaps_api_key' ]); ?>", {mmid: 'bingroad', type: 'Road', maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: 19, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		<?php }; ?>
		ogdwien_basemap = new L.TileLayer("<?php echo $protocol_handler; ?>://{s}.wien.gv.at/wmts/fmzk/pastell/google3857/{z}/{y}/{x}.jpeg", {mmid: 'ogdwien_basemap', maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: 19, minZoom: 11, errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", attribution: "<?php echo $attrib_ogdwien_basemap; ?>", subdomains: ['maps','maps1', 'maps2', 'maps3'], detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		ogdwien_satellite = new L.TileLayer("<?php echo $protocol_handler; ?>://{s}.wien.gv.at/wmts/lb/farbe/google3857/{z}/{y}/{x}.jpeg", {mmid: 'ogdwien_satellite', maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: 19, minZoom: 11, errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", attribution: "<?php echo $attrib_ogdwien_satellite; ?>", subdomains: ['maps','maps1', 'maps2', 'maps3'], detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		//info: MapBox basemaps
		<?php 
		$mapbox_ssl = (is_ssl() == FALSE) ? '' : '&secure=1';
		if ($lmm_options[ 'mapbox_access_token' ] != NULL) {
			echo 'var mapbox = new L.TileLayer("' . $protocol_handler . '://{s}.tiles.mapbox.com/v4/' . htmlspecialchars($lmm_options[ 'mapbox_user' ]) . '.' . htmlspecialchars($lmm_options[ 'mapbox_map' ]) . '/{z}/{x}/{y}.png?access_token=' . esc_js($lmm_options[ 'mapbox_access_token' ]) . $mapbox_ssl . '", {minZoom: ' . intval($lmm_options[ 'mapbox_minzoom' ]) . ', maxZoom: ' . $maxzoom . ', maxNativeZoom: ' . intval($lmm_options[ 'mapbox_maxzoom' ]) . ', errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . addslashes(wp_kses($lmm_options[ 'mapbox_attribution' ], $allowedtags)) . '", subdomains: ["a","b","c","d"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
		} else {  //info: v3 fallback for default maps
			echo 'var mapbox = new L.TileLayer("' . $protocol_handler . '://{s}.tiles.mapbox.com/v3/' . htmlspecialchars($lmm_options[ 'mapbox_user' ]) . '.' . htmlspecialchars($lmm_options[ 'mapbox_map' ]) . '/{z}/{x}/{y}.png", {minZoom: ' . intval($lmm_options[ 'mapbox_minzoom' ]) . ', maxZoom: ' . $maxzoom . ', maxNativeZoom: ' . intval($lmm_options[ 'mapbox_maxzoom' ]) . ', errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . addslashes(wp_kses($lmm_options[ 'mapbox_attribution' ], $allowedtags)) . '", subdomains: ["a","b","c","d"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
		}
		if ($lmm_options[ 'mapbox2_access_token' ] != NULL) {
			echo 'var mapbox2 = new L.TileLayer("' . $protocol_handler . '://{s}.tiles.mapbox.com/v4/' . htmlspecialchars($lmm_options[ 'mapbox2_user' ]) . '.' . htmlspecialchars($lmm_options[ 'mapbox2_map' ]) . '/{z}/{x}/{y}.png?access_token=' . esc_js($lmm_options[ 'mapbox2_access_token' ]) . $mapbox_ssl . '", {minZoom: ' . intval($lmm_options[ 'mapbox2_minzoom' ]) . ', maxZoom: ' . $maxzoom . ', maxNativeZoom: ' . intval($lmm_options[ 'mapbox2_maxzoom' ]) . ', errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . addslashes(wp_kses($lmm_options[ 'mapbox2_attribution' ], $allowedtags)) . '", subdomains: ["a","b","c","d"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;		
		} else {
			echo 'var mapbox2 = new L.TileLayer("' . $protocol_handler . '://{s}.tiles.mapbox.com/v3/' . htmlspecialchars($lmm_options[ 'mapbox2_user' ]) . '.' . htmlspecialchars($lmm_options[ 'mapbox2_map' ]) . '/{z}/{x}/{y}.png", {minZoom: ' . intval($lmm_options[ 'mapbox2_minzoom' ]) . ', maxZoom: ' . $maxzoom . ', maxNativeZoom: ' . intval($lmm_options[ 'mapbox2_maxzoom' ]) . ', errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . addslashes(wp_kses($lmm_options[ 'mapbox2_attribution' ], $allowedtags)) . '", subdomains: ["a","b","c","d"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
		}
		if ($lmm_options[ 'mapbox3_access_token' ] != NULL) {
			echo 'var mapbox3 = new L.TileLayer("' . $protocol_handler . '://{s}.tiles.mapbox.com/v4/' . htmlspecialchars($lmm_options[ 'mapbox3_user' ]) . '.' . htmlspecialchars($lmm_options[ 'mapbox3_map' ]) . '/{z}/{x}/{y}.png?access_token=' . esc_js($lmm_options[ 'mapbox3_access_token' ]) . $mapbox_ssl . '", {minZoom: ' . intval($lmm_options[ 'mapbox3_minzoom' ]) . ', maxZoom: ' . $maxzoom . ', maxNativeZoom: ' . intval($lmm_options[ 'mapbox3_maxzoom' ]) . ', errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . addslashes(wp_kses($lmm_options[ 'mapbox3_attribution' ], $allowedtags)) . '", subdomains: ["a","b","c","d"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;		
		} else {
			echo 'var mapbox3 = new L.TileLayer("' . $protocol_handler . '://{s}.tiles.mapbox.com/v3/' . htmlspecialchars($lmm_options[ 'mapbox3_user' ]) . '.' . htmlspecialchars($lmm_options[ 'mapbox3_map' ]) . '/{z}/{x}/{y}.png", {minZoom: ' . intval($lmm_options[ 'mapbox3_minzoom' ]) . ', maxZoom: ' . $maxzoom . ', maxNativeZoom: ' . intval($lmm_options[ 'mapbox3_maxzoom' ]) . ', errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . addslashes(wp_kses($lmm_options[ 'mapbox3_attribution' ], $allowedtags)) . '", subdomains: ["a","b","c","d"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
		}
		?>
		//info: check if subdomains are set for custom basemaps
		<?php
		$custom_basemap_subdomains = ((isset($lmm_options[ 'custom_basemap_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'custom_basemap_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'custom_basemap_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$custom_basemap2_subdomains = ((isset($lmm_options[ 'custom_basemap2_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'custom_basemap2_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'custom_basemap2_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$custom_basemap3_subdomains = ((isset($lmm_options[ 'custom_basemap3_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'custom_basemap3_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'custom_basemap3_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$error_tile_url_custom_basemap = ($lmm_options['custom_basemap_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';
		$error_tile_url_custom_basemap2 = ($lmm_options['custom_basemap2_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';
		$error_tile_url_custom_basemap3 = ($lmm_options['custom_basemap3_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';
		?>
		custom_basemap = new L.TileLayer("<?php echo str_replace('"','&quot;',$lmm_options[ 'custom_basemap_tileurl' ]) ?>", {mmid: 'custom_basemap', maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: <?php echo intval($lmm_options[ 'custom_basemap_maxzoom' ]) ?>, minZoom: <?php echo intval($lmm_options[ 'custom_basemap_minzoom' ]) ?>, tms: <?php echo $lmm_options[ 'custom_basemap_tms' ] ?>, <?php echo $error_tile_url_custom_basemap; ?>attribution: "<?php echo $attrib_custom_basemap; ?>"<?php echo $custom_basemap_subdomains ?>, continuousWorld: <?php echo $lmm_options[ 'custom_basemap_continuousworld_enabled' ] ?>, noWrap: <?php echo $lmm_options[ 'custom_basemap_nowrap_enabled' ] ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		custom_basemap2 = new L.TileLayer("<?php echo str_replace('"','&quot;',$lmm_options[ 'custom_basemap2_tileurl' ]) ?>", {mmid: 'custom_basemap2', maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: <?php echo intval($lmm_options[ 'custom_basemap2_maxzoom' ]) ?>, minZoom: <?php echo intval($lmm_options[ 'custom_basemap2_minzoom' ]) ?>, tms: <?php echo $lmm_options[ 'custom_basemap2_tms' ] ?>, <?php echo $error_tile_url_custom_basemap; ?>attribution: "<?php echo $attrib_custom_basemap2; ?>"<?php echo $custom_basemap2_subdomains ?>, continuousWorld: <?php echo $lmm_options[ 'custom_basemap2_continuousworld_enabled' ] ?>, noWrap: <?php echo $lmm_options[ 'custom_basemap2_nowrap_enabled' ] ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		custom_basemap3 = new L.TileLayer("<?php echo str_replace('"','&quot;',$lmm_options[ 'custom_basemap3_tileurl' ]) ?>", {mmid: 'custom_basemap3', maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: <?php echo intval($lmm_options[ 'custom_basemap3_maxzoom' ]) ?>, minZoom: <?php echo intval($lmm_options[ 'custom_basemap3_minzoom' ]) ?>, tms: <?php echo $lmm_options[ 'custom_basemap3_tms' ] ?>, <?php echo $error_tile_url_custom_basemap; ?>attribution: "<?php echo $attrib_custom_basemap3; ?>"<?php echo $custom_basemap3_subdomains ?>, continuousWorld: <?php echo $lmm_options[ 'custom_basemap3_continuousworld_enabled' ] ?>, noWrap: <?php echo $lmm_options[ 'custom_basemap3_nowrap_enabled' ] ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		empty_basemap = new L.TileLayer("", {mmid: 'empty_basemap'});

		//info: check if subdomains are set for custom overlays
		<?php
		$overlays_custom_subdomains = ((isset($lmm_options[ 'overlays_custom_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'overlays_custom_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'overlays_custom_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$overlays_custom2_subdomains = ((isset($lmm_options[ 'overlays_custom2_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'overlays_custom2_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'overlays_custom2_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$overlays_custom3_subdomains = ((isset($lmm_options[ 'overlays_custom3_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'overlays_custom3_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'overlays_custom3_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$overlays_custom4_subdomains = ((isset($lmm_options[ 'overlays_custom4_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'overlays_custom4_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'overlays_custom4_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$error_tile_url_overlays_custom = ($lmm_options['overlays_custom_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';
		$error_tile_url_overlays_custom2 = ($lmm_options['overlays_custom2_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';
		$error_tile_url_overlays_custom3 = ($lmm_options['overlays_custom3_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';
		$error_tile_url_overlays_custom4 = ($lmm_options['overlays_custom4_errortileurl'] == 'true') ? 'errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", ' : '';
		?>
		overlays_custom = new L.TileLayer("<?php echo str_replace('"','&quot;',$lmm_options[ 'overlays_custom_tileurl' ]) ?>", {olid: 'overlays_custom', tms: <?php echo $lmm_options[ 'overlays_custom_tms' ] ?>, <?php echo $error_tile_url_overlays_custom; ?>attribution: "<?php echo addslashes(wp_kses($lmm_options[ 'overlays_custom_attribution' ], $allowedtags)) ?>", opacity: <?php echo floatval($lmm_options[ 'overlays_custom_opacity' ]) ?>, maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: <?php echo intval($lmm_options[ 'overlays_custom_maxzoom' ]) ?>, minZoom: <?php echo intval($lmm_options[ 'overlays_custom_minzoom' ]) ?><?php echo $overlays_custom_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		overlays_custom2 = new L.TileLayer("<?php echo str_replace('"','&quot;',$lmm_options[ 'overlays_custom2_tileurl' ]) ?>", {olid: 'overlays_custom2', tms: <?php echo $lmm_options[ 'overlays_custom2_tms' ] ?>, <?php echo $error_tile_url_overlays_custom2; ?>attribution: "<?php echo addslashes(wp_kses($lmm_options[ 'overlays_custom2_attribution' ], $allowedtags)) ?>", opacity: <?php echo floatval($lmm_options[ 'overlays_custom2_opacity' ]) ?>, maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: <?php echo intval($lmm_options[ 'overlays_custom2_maxzoom' ]) ?>, minZoom: <?php echo intval($lmm_options[ 'overlays_custom2_minzoom' ]) ?><?php echo $overlays_custom2_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		overlays_custom3 = new L.TileLayer("<?php echo str_replace('"','&quot;',$lmm_options[ 'overlays_custom3_tileurl' ]) ?>", {olid: 'overlays_custom3', tms: <?php echo $lmm_options[ 'overlays_custom3_tms' ] ?>, <?php echo $error_tile_url_overlays_custom3; ?>attribution: "<?php echo addslashes(wp_kses($lmm_options[ 'overlays_custom3_attribution' ], $allowedtags)) ?>", opacity: <?php echo floatval($lmm_options[ 'overlays_custom3_opacity' ]) ?>, maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: <?php echo intval($lmm_options[ 'overlays_custom3_maxzoom' ]) ?>, minZoom: <?php echo intval($lmm_options[ 'overlays_custom3_minzoom' ]) ?><?php echo $overlays_custom3_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		overlays_custom4 = new L.TileLayer("<?php echo str_replace('"','&quot;',$lmm_options[ 'overlays_custom4_tileurl' ]) ?>", {olid: 'overlays_custom4', tms: <?php echo $lmm_options[ 'overlays_custom4_tms' ] ?>, <?php echo $error_tile_url_overlays_custom4; ?>attribution: "<?php echo addslashes(wp_kses($lmm_options[ 'overlays_custom4_attribution' ], $allowedtags)) ?>", opacity: <?php echo floatval($lmm_options[ 'overlays_custom4_opacity' ]) ?>, maxZoom: <?php echo $maxzoom; ?>, maxNativeZoom: <?php echo intval($lmm_options[ 'overlays_custom4_maxzoom' ]) ?>, minZoom: <?php echo intval($lmm_options[ 'overlays_custom4_minzoom' ]) ?><?php echo $overlays_custom4_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		//info: check if subdomains are set for wms layers
		<?php
		$wms_subdomains = ((isset($lmm_options[ 'wms_wms_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$wms2_subdomains = ((isset($lmm_options[ 'wms_wms2_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms2_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms2_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$wms3_subdomains = ((isset($lmm_options[ 'wms_wms3_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms3_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms3_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$wms4_subdomains = ((isset($lmm_options[ 'wms_wms4_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms4_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms4_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$wms5_subdomains = ((isset($lmm_options[ 'wms_wms5_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms5_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms5_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$wms6_subdomains = ((isset($lmm_options[ 'wms_wms6_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms6_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms6_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$wms7_subdomains = ((isset($lmm_options[ 'wms_wms7_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms7_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms7_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$wms8_subdomains = ((isset($lmm_options[ 'wms_wms8_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms8_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms8_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$wms9_subdomains = ((isset($lmm_options[ 'wms_wms9_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms9_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms9_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";
		$wms10_subdomains = ((isset($lmm_options[ 'wms_wms10_subdomains_enabled' ]) == TRUE ) && ($lmm_options[ 'wms_wms10_subdomains_enabled' ] == 'yes' )) ? ", subdomains: [" . htmlspecialchars_decode(wp_kses($lmm_options[ 'wms_wms10_subdomains_names' ], $allowedtags), ENT_QUOTES) . "]" :  "";

		//info: define wms legends
		$wms_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms_legend' ] != NULL )) ? ' (<a href="' . wp_kses($lmm_options[ 'wms_wms_legend' ], $allowedtags) . '" target=&quot;_blank&quot;>' . __('Legend','lmm') . '</a>)' : '') . '';
		$wms2_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms2_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms2_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms2_legend' ] != NULL )) ? ' (<a href="' . wp_kses($lmm_options[ 'wms_wms2_legend' ], $allowedtags) . '" target=&quot;_blank&quot;>' . __('Legend','lmm') . '</a>)' : '') . '';
		$wms3_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms3_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms3_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms3_legend' ] != NULL )) ? ' (<a href="' . wp_kses($lmm_options[ 'wms_wms3_legend' ], $allowedtags) . '" target=&quot;_blank&quot;>' . __('Legend','lmm') . '</a>)' : '') . '';
		$wms4_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms4_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms4_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms4_legend' ] != NULL )) ? ' (<a href="' . wp_kses($lmm_options[ 'wms_wms4_legend' ], $allowedtags) . '" target=&quot;_blank&quot;>' . __('Legend','lmm') . '</a>)' : '') . '';
		$wms5_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms5_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms5_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms5_legend' ] != NULL )) ? ' (<a href="' . wp_kses($lmm_options[ 'wms_wms5_legend' ], $allowedtags) . '" target=&quot;_blank&quot;>' . __('Legend','lmm') . '</a>)' : '') . '';
		$wms6_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms6_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms6_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms6_legend' ] != NULL )) ? ' (<a href="' . wp_kses($lmm_options[ 'wms_wms6_legend' ], $allowedtags) . '" target=&quot;_blank&quot;>' . __('Legend','lmm') . '</a>)' : '') . '';
		$wms7_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms7_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms7_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms7_legend' ] != NULL )) ? ' (<a href="' . wp_kses($lmm_options[ 'wms_wms7_legend' ], $allowedtags) . '" target=&quot;_blank&quot;>' . __('Legend','lmm') . '</a>)' : '') . '';
		$wms8_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms8_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms8_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms8_legend' ] != NULL )) ? ' (<a href="' . wp_kses($lmm_options[ 'wms_wms8_legend' ], $allowedtags) . '" target=&quot;_blank&quot;>' . __('Legend','lmm') . '</a>)' : '') . '';
		$wms9_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms9_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms9_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms9_legend' ] != NULL )) ? ' (<a href="' . wp_kses($lmm_options[ 'wms_wms9_legend' ], $allowedtags) . '" target=&quot;_blank&quot;>' . __('Legend','lmm') . '</a>)' : '') . '';
		$wms10_attribution = addslashes(wp_kses($lmm_options[ 'wms_wms10_attribution' ], $allowedtags)) . ( (($lmm_options[ 'wms_wms10_legend_enabled' ] == 'yes' ) && ($lmm_options[ 'wms_wms10_legend' ] != NULL )) ? ' (<a href="' . wp_kses($lmm_options[ 'wms_wms10_legend' ], $allowedtags) . '" target=&quot;_blank&quot;>' . __('Legend','lmm') . '</a>)' : '') . '';
		?>

		//info: define wms layers
		wms = new L.TileLayer.WMS("<?php echo  htmlspecialchars($lmm_options[ 'wms_wms_baseurl' ]) ?>", {wmsid: 'wms', layers: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms_layers' ]))?>', styles: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms_styles' ]))?>', format: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms_format' ]))?>', attribution: '<?php echo $wms_attribution; ?>', transparent: '<?php echo $lmm_options[ 'wms_wms_transparent' ]?>', errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", version: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms_version' ]))?>'<?php echo $wms_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		wms2 = new L.TileLayer.WMS("<?php echo  htmlspecialchars($lmm_options[ 'wms_wms2_baseurl' ]) ?>", {wmsid: 'wms2', layers: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms2_layers' ]))?>', styles: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms2_styles' ]))?>', format: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms2_format' ]))?>', attribution: '<?php echo $wms2_attribution; ?>', transparent: '<?php echo $lmm_options[ 'wms_wms2_transparent' ]?>', errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", version: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms2_version' ]))?>'<?php echo $wms2_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		wms3 = new L.TileLayer.WMS("<?php echo  htmlspecialchars($lmm_options[ 'wms_wms3_baseurl' ]) ?>", {wmsid: 'wms3', layers: '<?php echo htmlspecialchars(htmlspecialchars(addslashes($lmm_options[ 'wms_wms3_layers' ])))?>', styles: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms3_styles' ]))?>', format: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms3_format' ]))?>', attribution: '<?php echo $wms3_attribution; ?>', transparent: '<?php echo $lmm_options[ 'wms_wms3_transparent' ]?>', errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", version: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms3_version' ]))?>'<?php echo $wms3_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		wms4 = new L.TileLayer.WMS("<?php echo  htmlspecialchars($lmm_options[ 'wms_wms4_baseurl' ]) ?>", {wmsid: 'wms4', layers: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms4_layers' ]))?>', styles: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms4_styles' ]))?>', format: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms4_format' ]))?>', attribution: '<?php echo $wms4_attribution ?>', transparent: '<?php echo $lmm_options[ 'wms_wms4_transparent' ]?>', errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", version: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms4_version' ]))?>'<?php echo $wms4_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		wms5 = new L.TileLayer.WMS("<?php echo  htmlspecialchars($lmm_options[ 'wms_wms5_baseurl' ]) ?>", {wmsid: 'wms5', layers: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms5_layers' ]))?>', styles: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms5_styles' ]))?>', format: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms5_format' ]))?>', attribution: '<?php echo $wms5_attribution; ?>', transparent: '<?php echo $lmm_options[ 'wms_wms5_transparent' ]?>', errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", version: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms5_version' ]))?>'<?php echo $wms5_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		wms6 = new L.TileLayer.WMS("<?php echo  htmlspecialchars($lmm_options[ 'wms_wms6_baseurl' ]) ?>", {wmsid: 'wms6', layers: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms6_layers' ]))?>', styles: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms6_styles' ]))?>', format: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms6_format' ]))?>', attribution: '<?php echo $wms6_attribution; ?>', transparent: '<?php echo $lmm_options[ 'wms_wms6_transparent' ]?>', errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", version: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms6_version' ]))?>'<?php echo $wms6_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		wms7 = new L.TileLayer.WMS("<?php echo  htmlspecialchars($lmm_options[ 'wms_wms7_baseurl' ]) ?>", {wmsid: 'wms7', layers: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms7_layers' ]))?>', styles: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms7_styles' ]))?>', format: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms7_format' ]))?>', attribution: '<?php echo $wms7_attribution; ?>', transparent: '<?php echo $lmm_options[ 'wms_wms7_transparent' ]?>', errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", version: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms7_version' ]))?>'<?php echo $wms7_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		wms8 = new L.TileLayer.WMS("<?php echo  htmlspecialchars($lmm_options[ 'wms_wms8_baseurl' ]) ?>", {wmsid: 'wms8', layers: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms8_layers' ]))?>', styles: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms8_styles' ]))?>', format: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms8_format' ]))?>', attribution: '<?php echo $wms8_attribution; ?>', transparent: '<?php echo $lmm_options[ 'wms_wms8_transparent' ]?>', errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", version: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms8_version' ]))?>'<?php echo $wms8_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		wms9 = new L.TileLayer.WMS("<?php echo  htmlspecialchars($lmm_options[ 'wms_wms9_baseurl' ]) ?>", {wmsid: 'wms9', layers: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms9_layers' ]))?>', styles: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms9_styles' ]))?>', format: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms9_format' ]))?>', attribution: '<?php echo $wms9_attribution; ?>', transparent: '<?php echo $lmm_options[ 'wms_wms9_transparent' ]?>', errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", version: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms9_version' ]))?>'<?php echo $wms9_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});
		wms10 = new L.TileLayer.WMS("<?php echo  htmlspecialchars($lmm_options[ 'wms_wms10_baseurl' ]) ?>", {wmsid: 'wms10', layers: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms10_layers' ]))?>', styles: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms10_styles' ]))?>', format: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms10_format' ]))?>', attribution: '<?php echo $wms10_attribution; ?>', transparent: '<?php echo $lmm_options[ 'wms_wms10_transparent' ]?>', errorTileUrl: "<?php echo LEAFLET_PLUGIN_URL ?>inc/img/error-tile-image.png", version: '<?php echo htmlspecialchars(addslashes($lmm_options[ 'wms_wms10_version' ]))?>'<?php echo $wms10_subdomains ?>, detectRetina: <?php echo $lmm_options['map_retina_detection'] ?>});

		//info: controlbox - define basemaps
		layersControl = new L.Control.Layers(
		{
		<?php
			$basemaps_available = "";
			if ( (isset($lmm_options[ 'controlbox_osm_mapnik' ]) == TRUE ) && ($lmm_options[ 'controlbox_osm_mapnik' ] == 1) )
				$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_osm_mapnik' ])) . "': osm_mapnik,";
			if ( (isset($lmm_options[ 'controlbox_mapquest_osm' ]) == TRUE ) && ($lmm_options[ 'controlbox_mapquest_osm' ] == 1) )
				$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_mapquest_osm' ])) . "': mapquest_osm,";
			if ( (isset($lmm_options[ 'controlbox_mapquest_aerial' ]) == TRUE ) && ($lmm_options[ 'controlbox_mapquest_aerial' ] == 1) )
				$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_mapquest_aerial' ])) . "': mapquest_aerial,";
			if ( (isset($lmm_options[ 'controlbox_googleLayer_roadmap' ]) == TRUE ) && ($lmm_options[ 'controlbox_googleLayer_roadmap' ] == 1) && ($lmm_options['google_maps_api_status'] == 'enabled') )
				$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_googleLayer_roadmap' ])) . "': googleLayer_roadmap,";
			if ( (isset($lmm_options[ 'controlbox_googleLayer_satellite' ]) == TRUE ) && ($lmm_options[ 'controlbox_googleLayer_satellite' ] == 1) && ($lmm_options['google_maps_api_status'] == 'enabled') )
				$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_googleLayer_satellite' ])) . "': googleLayer_satellite,";
			if ( (isset($lmm_options[ 'controlbox_googleLayer_hybrid' ]) == TRUE ) && ($lmm_options[ 'controlbox_googleLayer_hybrid' ] == 1) && ($lmm_options['google_maps_api_status'] == 'enabled') )
				$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_googleLayer_hybrid' ])) . "': googleLayer_hybrid,";
			if ( (isset($lmm_options[ 'controlbox_googleLayer_terrain' ]) == TRUE ) && ($lmm_options[ 'controlbox_googleLayer_terrain' ] == 1) && ($lmm_options['google_maps_api_status'] == 'enabled') )
				$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_googleLayer_terrain' ])) . "': googleLayer_terrain,";
			if ( isset($lmm_options['bingmaps_api_key']) && ($lmm_options['bingmaps_api_key'] != NULL ) ) {
				if ( (isset($lmm_options[ 'controlbox_bingaerial' ]) == TRUE ) && ($lmm_options[ 'controlbox_bingaerial' ] == 1) )
					$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_bingaerial' ])) . "': bingaerial,";
				if ( (isset($lmm_options[ 'controlbox_bingaerialwithlabels' ]) == TRUE ) && ($lmm_options[ 'controlbox_bingaerialwithlabels' ] == 1) )
					$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_bingaerialwithlabels' ])) . "': bingaerialwithlabels,";
				if ( (isset($lmm_options[ 'controlbox_bingroad' ]) == TRUE ) && ($lmm_options[ 'controlbox_bingroad' ] == 1) )
					$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_bingroad' ])) . "': bingroad,";
			};
			if ( (((isset($lmm_options[ 'controlbox_ogdwien_basemap' ]) == TRUE ) && ($lmm_options[ 'controlbox_ogdwien_basemap' ] == 1)) && ((($layerviewlat <= '48.326583')  && ($layerviewlat >= '48.114308')) && (($layerviewlon <= '16.55056')  && ($layerviewlon >= '16.187325')) )) || ($basemap == 'ogdwien_basemap') )
				$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_ogdwien_basemap' ])) . "': ogdwien_basemap,";
			if ( (((isset($lmm_options[ 'controlbox_ogdwien_satellite' ]) == TRUE ) && ($lmm_options[ 'controlbox_ogdwien_satellite' ] == 1)) && ((($layerviewlat <= '48.326583')  && ($layerviewlat >= '48.114308')) && (($layerviewlon <= '16.55056')  && ($layerviewlon >= '16.187325')) )) || ($basemap == 'ogdwien_satellite') )
				$basemaps_available .= "'" . htmlspecialchars(addslashes($lmm_options[ 'default_basemap_name_ogdwien_satellite' ])) . "': ogdwien_satellite,";
			if ( (isset($lmm_options[ 'controlbox_mapbox' ]) == TRUE ) && ($lmm_options[ 'controlbox_mapbox' ] == 1) )
				$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'mapbox_name' ]))."': mapbox,";
			if ( (isset($lmm_options[ 'controlbox_mapbox2' ]) == TRUE ) && ($lmm_options[ 'controlbox_mapbox2' ] == 1) )
				$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'mapbox2_name' ]))."': mapbox2,";
			if ( (isset($lmm_options[ 'controlbox_mapbox3' ]) == TRUE ) && ($lmm_options[ 'controlbox_mapbox3' ] == 1) )
				$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'mapbox3_name' ]))."': mapbox3,";
			if ( (isset($lmm_options[ 'controlbox_custom_basemap' ]) == TRUE ) && ($lmm_options[ 'controlbox_custom_basemap' ] == 1) )
				$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'custom_basemap_name' ]))."': custom_basemap,";
			if ( (isset($lmm_options[ 'controlbox_custom_basemap2' ]) == TRUE ) && ($lmm_options[ 'controlbox_custom_basemap2' ] == 1) )
				$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'custom_basemap2_name' ]))."': custom_basemap2,";
			if ( (isset($lmm_options[ 'controlbox_custom_basemap3' ]) == TRUE ) && ($lmm_options[ 'controlbox_custom_basemap3' ] == 1) )
				$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'custom_basemap3_name' ]))."': custom_basemap3,";
			if ( (isset($lmm_options[ 'controlbox_empty_basemap' ]) == TRUE ) && ($lmm_options[ 'controlbox_empty_basemap' ] == 1) )
				$basemaps_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'empty_basemap_name' ]))."': empty_basemap,";
			//info: needed for IE7 compatibility
			echo substr($basemaps_available, 0, -1);
		?>
		},

		//info: controlbox - add available overlays
		{
		<?php
			$overlays_custom_available = '';
			if ( ((isset($lmm_options[ 'overlays_custom' ] ) == TRUE ) && ( $lmm_options[ 'overlays_custom' ] == 1 )) || ($loverlays_custom == 1) )
				$overlays_custom_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'overlays_custom_name' ]))."': overlays_custom,";
			if ( ((isset($lmm_options[ 'overlays_custom2' ] ) == TRUE ) && ( $lmm_options[ 'overlays_custom2' ] == 1 )) || ($loverlays_custom2 == 1) )
				$overlays_custom_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'overlays_custom2_name' ]))."': overlays_custom2,";
			if ( ((isset($lmm_options[ 'overlays_custom3' ] ) == TRUE ) && ( $lmm_options[ 'overlays_custom3' ] == 1 )) || ($loverlays_custom3 == 1) )
				$overlays_custom_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'overlays_custom3_name' ]))."': overlays_custom3,";
			if ( ((isset($lmm_options[ 'overlays_custom4' ] ) == TRUE ) && ( $lmm_options[ 'overlays_custom4' ] == 1 )) || ($loverlays_custom4 == 1) )
				$overlays_custom_available .= "'".htmlspecialchars(addslashes($lmm_options[ 'overlays_custom4_name' ]))."': overlays_custom4,";
			//info: needed for IE7 compatibility
			echo substr($overlays_custom_available, 0, -1);
		?>
		},
		{
		//info: set controlbox visibility 1/2
		collapsed: true
		});

		selectlayer.setView(new L.LatLng(<?php echo $layerviewlat . ', ' . $layerviewlon; ?>), <?php echo $layerzoom ?>);
		selectlayer.addLayer(<?php echo $basemap ?>)
		//info: controlbox - check active overlays on layer level
		<?php
			if ( (isset($loverlays_custom) == TRUE) && ($loverlays_custom == 1) )
				echo ".addLayer(overlays_custom)";
			if ( (isset($loverlays_custom2) == TRUE) && ($loverlays_custom2 == 1) )
				echo ".addLayer(overlays_custom2)";
			if ( (isset($loverlays_custom3) == TRUE) && ($loverlays_custom3 == 1) )
				echo ".addLayer(overlays_custom3)";
			if ( (isset($loverlays_custom4) == TRUE) && ($loverlays_custom4 == 1) )
				echo ".addLayer(overlays_custom4)";
		?>
		//info: controlbox - add active overlays on layer level
		<?php
			if ( $wms == 1 )
				echo ".addLayer(wms)";
			if ( $wms2 == 1 )
				echo ".addLayer(wms2)";
			if ( $wms3 == 1 )
				echo ".addLayer(wms3)";
			if ( $wms4 == 1 )
				echo ".addLayer(wms4)";
			if ( $wms5 == 1 )
				echo ".addLayer(wms5)";
			if ( $wms6 == 1 )
				echo ".addLayer(wms6)";
			if ( $wms7 == 1 )
				echo ".addLayer(wms7)";
			if ( $wms8 == 1 )
				echo ".addLayer(wms8)";
			if ( $wms9 == 1 )
				echo ".addLayer(wms9)";
			if ( $wms10 == 1 )
				echo ".addLayer(wms10)";
		?>

		.addControl(layersControl);

		<?php //info: add minimap
		if ($lmm_options['minimap_status'] != 'hidden') {
			echo 'var osm_mapnik_minimap = new L.TileLayer("' . $protocol_handler . '://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {maxZoom: ' . $maxzoom . ', maxNativeZoom: 18, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . $attrib_osm_mapnik . '", detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
			echo 'var mapquest_osm_minimap = new L.TileLayer("' . $protocol_handler . '://{s}' . $mapquest_ssl . '.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png", {maxZoom: ' . $maxzoom . ', maxNativeZoom: 18, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . $attrib_mapquest_osm . '", subdomains: ["otile1","otile2","otile3","otile4"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
			echo 'var mapquest_aerial_minimap = new L.TileLayer("' . $protocol_handler . '://{s}' . $mapquest_ssl . '.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.png", {maxZoom: ' . $maxzoom . ', maxNativeZoom: 11, minZoom: 1, errorTileUrl: "' . LEAFLET_PLUGIN_URL . 'inc/img/error-tile-image.png", attribution: "' . $attrib_mapquest_aerial . '", subdomains: ["otile1","otile2","otile3","otile4"], detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
			if ($lmm_options['google_maps_api_status'] == 'enabled') {
				echo 'var googleLayer_roadmap_minimap = new L.Google("ROADMAP", {detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
				echo 'var googleLayer_satellite_minimap = new L.Google("SATELLITE", {detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
				echo 'var googleLayer_hybrid_minimap = new L.Google("HYBRID", {detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
				echo 'var googleLayer_terrain_minimap = new L.Google("TERRAIN", {detectRetina: ' . $lmm_options['map_retina_detection'] . '});'.PHP_EOL;
			}
			//info: bing minimaps
			if ( isset($lmm_options['bingmaps_api_key']) && ($lmm_options['bingmaps_api_key'] != NULL ) ) {
				echo 'var bingaerial_minimap = new L.BingLayer("' . htmlspecialchars($lmm_options[ 'bingmaps_api_key' ]) . '", {type: "Aerial", maxZoom: ' . $maxzoom . ', maxNativeZoom: 19, minZoom: 1});'.PHP_EOL;
				echo 'var bingaerialwithlabels_minimap = new L.BingLayer("' . htmlspecialchars($lmm_options[ 'bingmaps_api_key' ]) . '", {type: "AerialWithLabels", maxZoom: ' . $maxzoom . ', maxNativeZoom: 19, minZoom: 1});'.PHP_EOL;
				echo 'var bingroad_minimap = new L.BingLayer("' . htmlspecialchars($lmm_options[ 'bingmaps_api_key' ]) . '", {type: "Road", maxZoom: ' . $maxzoom . ', maxNativeZoom: 19, minZoom: 1});'.PHP_EOL;
			};
			if ($lmm_options['minimap_zoomLevelFixed'] != NULL) { $zoomlevelfixed =  'zoomLevelFixed: ' . intval($lmm_options['minimap_zoomLevelFixed']) . ','; } else { $zoomlevelfixed = ''; }
			if ($lmm_options['minimap_basemap'] == 'automatic') {
				if ($basemap == 'osm_mapnik') {
					$minimap_basemap = 'osm_mapnik_minimap';
				} else if ($basemap == 'mapquest_osm') {
					$minimap_basemap = 'mapquest_osm_minimap';
				} else if ($basemap == 'mapquest_aerial') {
					$minimap_basemap = 'mapquest_aerial_minimap';
				} else if (($basemap == 'googleLayer_roadmap') && ($lmm_options['google_maps_api_status'] == 'enabled')) {
					$minimap_basemap = 'googleLayer_roadmap_minimap';
				} else if (($basemap == 'googleLayer_satellite') && ($lmm_options['google_maps_api_status'] == 'enabled')) {
					$minimap_basemap = 'googleLayer_satellite_minimap';
				} else if (($basemap == 'googleLayer_hybrid') && ($lmm_options['google_maps_api_status'] == 'enabled')) {
					$minimap_basemap = 'googleLayer_hybrid_minimap';
				} else if (($basemap == 'googleLayer_terrain') && ($lmm_options['google_maps_api_status'] == 'enabled')) {
					$minimap_basemap = 'googleLayer_terrain_minimap';
				} else if ( (isset($lmm_options['bingmaps_api_key']) && ($lmm_options['bingmaps_api_key'] != NULL )) && ($basemap == 'bingaerial')){
					$minimap_basemap = 'bingaerial_minimap';
				} else if ( (isset($lmm_options['bingmaps_api_key']) && ($lmm_options['bingmaps_api_key'] != NULL )) && ($basemap == 'bingaerialwithlabels')){
					$minimap_basemap = 'bingaerialwithlabels_minimap';
				} else if ( (isset($lmm_options['bingmaps_api_key']) && ($lmm_options['bingmaps_api_key'] != NULL )) && ($basemap == 'bingroad')){
					$minimap_basemap = 'bingroad_minimap';
				} else {
					$minimap_basemap = 'osm_mapnik_minimap';
				}
			} else {
				$minimap_basemap = $lmm_options['minimap_basemap'];
				if (($lmm_options['google_maps_api_status'] == 'disabled') && (($minimap_basemap == 'googleLayer_roadmap') || ($minimap_basemap == 'googleLayer_satellite') || ($minimap_basemap == 'googleLayer_hybrid') || ($minimap_basemap == 'googleLayer_terrain')) ) {
					$minimap_basemap = 'osm_mapnik_minimap';
				}
			}
			echo "var miniMap = new L.Control.MiniMap(" . $minimap_basemap . ", {position: '" . $lmm_options['minimap_position'] . "', width: " . intval($lmm_options['minimap_width']) . ", height: " . intval($lmm_options['minimap_height']) . ", collapsedWidth: " . intval($lmm_options['minimap_collapsedWidth']) . ", collapsedHeight: " . intval($lmm_options['minimap_collapsedHeight']) . ", zoomLevelOffset: " . intval($lmm_options['minimap_zoomLevelOffset']) . ", " . $zoomlevelfixed . " zoomAnimation: " . $lmm_options['minimap_zoomAnimation'] . ", toggleDisplay: " . $lmm_options['minimap_toggleDisplay'] . ", autoToggleDisplay: " . $lmm_options['minimap_autoToggleDisplay'] . "}).addTo(selectlayer);".PHP_EOL;
		} ?>

		//info: gpx tracks
		<?php if ( ($gpx_url != NULL) && (lmm_isValidURL( $gpx_url)) ) {
			$gpx_track_color = '#' . str_replace('#', '', htmlspecialchars($lmm_options['gpx_track_color']));
			$gpx_startIconUrl = ($lmm_options['gpx_startIconUrl'] == NULL) ? LEAFLET_PLUGIN_URL . 'leaflet-dist/images/gpx-icon-start.png' : trim(htmlspecialchars($lmm_options['gpx_startIconUrl']));
			$gpx_endIconUrl = ($lmm_options['gpx_endIconUrl'] == NULL) ? LEAFLET_PLUGIN_URL . 'leaflet-dist/images/gpx-icon-end.png' : trim(htmlspecialchars($lmm_options['gpx_endIconUrl']));
			$gpx_shadowUrl = ($lmm_options['gpx_shadowUrl'] == NULL) ? LEAFLET_PLUGIN_URL . 'leaflet-dist/images/gpx-icon-shadow.png' : trim(htmlspecialchars($lmm_options['gpx_shadowUrl']));
			if ( (isset($lmm_options[ 'gpx_metadata_name' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_name' ] == 1 ) ) {
					$gpx_metadata_name_js = 'if (gpx.get_name() != undefined) { _c("gpx-name").innerHTML = gpx.get_name(); } else { _c("gpx-name").innerHTML = "n/a"; }';
			} else { $gpx_metadata_name_js = ''; }
			if ( (isset($lmm_options[ 'gpx_metadata_start' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_start' ] == 1 ) ) {
				$gpx_metadata_start_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-start").innerHTML = gpx.get_start_time().toDateString() + ", " + gpx.get_start_time().toLocaleTimeString(); } else { _c("gpx-start").innerHTML = "n/a"; }';
			} else { $gpx_metadata_start_js = ''; }
			if ( (isset($lmm_options[ 'gpx_metadata_end' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_end' ] == 1 ) ) {
				$gpx_metadata_end_js = 'if (gpx.get_end_time() != undefined) { _c("gpx-end").innerHTML = gpx.get_end_time().toDateString() + ", " + gpx.get_end_time().toLocaleTimeString(); } else { _c("gpx-end").innerHTML = "n/a"; }';
			} else { $gpx_metadata_end_js = ''; }
			if ( (isset($lmm_options[ 'gpx_metadata_distance' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_distance' ] == 1 ) ) {
				if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
					$gpx_metadata_distance_js = 'if (gpx.get_distance() != "0") { _c("gpx-distance").innerHTML = (gpx.get_distance()/1000).toFixed(2); } else { _c("gpx-distance").innerHTML = "n/a"; }';
				} else {
					$gpx_metadata_distance_js = 'if (gpx.get_distance() != "0") { _c("gpx-distance").innerHTML = gpx.get_distance_imp().toFixed(2); } else { _c("gpx-distance").innerHTML = "n/a"; }';
				}
			} else { $gpx_metadata_distance_js = ''; }
			if ( (isset($lmm_options[ 'gpx_metadata_duration_moving' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_duration_moving' ] == 1 ) ) {
				$gpx_metadata_duration_moving_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-duration-moving").innerHTML = gpx.get_duration_string(gpx.get_moving_time()); } else { _c("gpx-duration-moving").innerHTML = "n/a"; }';
			} else { $gpx_metadata_duration_moving_js = ''; }
			if ( (isset($lmm_options[ 'gpx_metadata_duration_total' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_duration_total' ] == 1 ) ) {
				$gpx_metadata_duration_total_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-duration-total").innerHTML = gpx.get_duration_string(gpx.get_total_time()); } else { _c("gpx-duration-total").innerHTML = "n/a"; }';
			} else { $gpx_metadata_duration_total_js = ''; }
			if ( (isset($lmm_options[ 'gpx_metadata_avpace' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_avpace' ] == 1 ) ) {
				if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
					$gpx_metadata_avpace_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-avpace").innerHTML = gpx.get_duration_string(gpx.get_moving_pace(), true); } else { _c("gpx-avpace").innerHTML = "n/a"; }';
				} else {
				$gpx_metadata_avpace_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-avpace").innerHTML = gpx.get_duration_string(gpx.get_moving_pace_imp(), true); } else { _c("gpx-avpace").innerHTML = "n/a"; }';
				}
			} else { $gpx_metadata_avpace_js = ''; }
			if ( (isset($lmm_options[ 'gpx_metadata_avhr' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_avhr' ] == 1 ) ) {
				$gpx_metadata_avhr_js = 'if (isNaN(gpx.get_average_hr())) { _c("gpx-avghr").innerHTML = "n/a"; } else { _c("gpx-avghr").innerHTML = gpx.get_average_hr() + "bpm"; }';
			} else { $gpx_metadata_avhr_js = ''; }
			if ( ((isset($lmm_options[ 'gpx_metadata_elev_gain' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_gain' ] == 1 )) || ((isset($lmm_options[ 'gpx_metadata_elev_loss' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_loss' ] == 1 )) || ((isset($lmm_options[ 'gpx_metadata_elev_net' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_net' ] == 1 )) ) {
				$gpx_metadata_elevation_title_js = '';
			} else { $gpx_metadata_elevation_title_js = ''; }
			if ( (isset($lmm_options[ 'gpx_metadata_elev_gain' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_gain' ] == 1 ) ) {
				if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
					$gpx_metadata_elev_gain_js = '_c("gpx-elevation-gain").innerHTML = gpx.get_elevation_gain().toFixed(0);';
				} else {
					$gpx_metadata_elev_gain_js = '_c("gpx-elevation-gain").innerHTML = gpx.to_ft(gpx.get_elevation_gain()).toFixed(0);';
				}
			} else { $gpx_metadata_elev_gain_js = ''; }
			if ( (isset($lmm_options[ 'gpx_metadata_elev_loss' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_loss' ] == 1 ) ) {
				if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
					$gpx_metadata_elev_loss_js = '_c("gpx-elevation-loss").innerHTML = gpx.get_elevation_loss().toFixed(0);';
				} else {
					$gpx_metadata_elev_loss_js = '_c("gpx-elevation-loss").innerHTML = gpx.to_ft(gpx.get_elevation_loss()).toFixed(0);';
				}
			} else { $gpx_metadata_elev_loss_js = ''; }
			if ( (isset($lmm_options[ 'gpx_metadata_elev_net' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_net' ] == 1 ) ) {
				if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
					$gpx_metadata_elev_net_js = '_c("gpx-elevation-net").innerHTML  = gpx.get_elevation_gain().toFixed(0) - gpx.get_elevation_loss().toFixed(0);';
				} else {
					$gpx_metadata_elev_net_js = '_c("gpx-elevation-net").innerHTML  = gpx.to_ft(gpx.get_elevation_gain() - gpx.get_elevation_loss()).toFixed(0);';
				}
			} else { $gpx_metadata_elev_net_js = ''; }
			if ( (isset($lmm_options[ 'gpx_metadata_elev_full' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_full' ] == 1 ) ) {
				if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
					$gpx_metadata_elev_full_js = '_c("gpx-elevation-full").innerHTML    = gpx.get_elevation_data();';
				} else {
					$gpx_metadata_elev_full_js = '_c("gpx-elevation-full").innerHTML    = gpx.get_elevation_data_imp();';
				}
			} else { $gpx_metadata_elev_full_js = ''; }
			if ( (isset($lmm_options[ 'gpx_metadata_hr_full' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_hr_full' ] == 1 ) ) {
				if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
					$gpx_metadata_hr_full_js = '_c("gpx-heartrate-full").innerHTML    = gpx.get_heartrate_data();';
				} else {
					$gpx_metadata_hr_full_js = '_c("gpx-heartrate-full").innerHTML    = gpx.get_heartrate_data_imp();';
				}
			} else { $gpx_metadata_hr_full_js = ''; }

			//info: do not load GPX if error on wp_remote_get occured
			if (!is_wp_error($gpx_content_array)) {
				$gpx_content = esc_js(str_replace("\xEF\xBB\xBF",'',$gpx_content_array['body'])); //info: replace UTF8-BOM for Chrome
			} else {
				$gpx_content = '';
			}
			echo 'function display_gpx_selectlayer() {
						var gpx_panel = document.getElementById("gpx-panel-selectlayer");
						var gpx_url = "'.$gpx_url.'";

						function _c(c) { return gpx_panel.querySelectorAll("."+c)[0]; }

					   var gpx_track = new L.GPX(gpx_url, {
						gpx_content: "'.$gpx_content.'",
						async: true,
						max_point_interval: ' . intval($lmm_options['gpx_max_point_interval']) . ',
						marker_options: {
							startIconUrl: "' . $gpx_startIconUrl . '",
							endIconUrl: "' . $gpx_endIconUrl . '",
							shadowUrl: "' . $gpx_shadowUrl . '",
							iconSize: [' . $lmm_options['gpx_iconSize_x'] . ', ' . $lmm_options['gpx_iconSize_y'] . '],
							shadowSize: [' . $lmm_options['gpx_shadowSize_x'] . ', ' . $lmm_options['gpx_shadowSize_y'] . '],
							iconAnchor: [' . $lmm_options['gpx_iconAnchor_x'] . ', ' . $lmm_options['gpx_iconAnchor_y'] . '],
						shadowAnchor: [' . $lmm_options['gpx_shadowAnchor_x'] . ', ' . $lmm_options['gpx_shadowAnchor_y'] . '],
						className: "lmm_gpx_icons"
						},
						polyline_options: {
							color: "' . $gpx_track_color . '",
							weight: ' . intval($lmm_options['gpx_track_weight']) . ',
							opacity: "' . str_replace(',', '.', floatval($lmm_options['gpx_track_opacity'])) . '",
							smoothFactor: "' . str_replace(',', '.', floatval($lmm_options['gpx_track_smoothFactor'])) . '",
							clickable: ' . $lmm_options['gpx_track_clickable'] . ',
							noClip: ' . $lmm_options['gpx_track_noClip'] . '
						}
						}).addTo(selectlayer);
						gpx_track.on("gpx_loaded", function(e) {
								var gpx = e.target;
								' . $gpx_metadata_name_js . '
								' . $gpx_metadata_start_js . '
								' . $gpx_metadata_end_js . '
								' . $gpx_metadata_distance_js . '
								' . $gpx_metadata_duration_moving_js . '
								' . $gpx_metadata_duration_total_js . '
								' . $gpx_metadata_avpace_js . '
								' . $gpx_metadata_avhr_js . '
								' . $gpx_metadata_elev_gain_js . '
								' . $gpx_metadata_elev_loss_js . '
								' . $gpx_metadata_elev_net_js . '
								' . $gpx_metadata_elev_full_js . '
								' . $gpx_metadata_hr_full_js . '
						});
				}
				display_gpx_selectlayer();'.PHP_EOL;
		}
		?>

		//info: add scale control
		<?php if ( $lmm_options['map_scale_control'] == 'enabled' ) { ?>
		L.control.scale({position:'<?php echo $lmm_options['map_scale_control_position'] ?>', maxWidth: <?php echo intval($lmm_options['map_scale_control_maxwidth']) ?>, metric: <?php echo $lmm_options['map_scale_control_metric'] ?>, imperial: <?php echo $lmm_options['map_scale_control_imperial'] ?>, updateWhenIdle: <?php echo $lmm_options['map_scale_control_updatewhenidle'] ?>}).addTo(selectlayer);
		<?php }; ?>

		//info: add geolocate control
		<?php 
		if ($lmm_options['geolocate_status'] == 'true') {
			echo "var locatecontrol_selectlayer = L.control.locate({
					position: '" . $lmm_options[ 'geolocate_position' ] . "', 
					drawCircle: " . $lmm_options[ 'geolocate_drawCircle' ] . ",
					follow: " . $lmm_options[ 'geolocate_follow' ] . ",
					setView: " . $lmm_options[ 'geolocate_setView' ] . ",
					keepCurrentZoomLevel: " . $lmm_options[ 'geolocate_keepCurrentZoomLevel' ] . ",
					remainActive: " . $lmm_options[ 'geolocate_remainActive' ] . ",
					circleStyle: {" . htmlspecialchars($lmm_options[ 'geolocate_circleStyle' ]) . "},
					markerStyle: {" . htmlspecialchars($lmm_options[ 'geolocate_markerStyle' ]) . "},
					followCircleStyle: {" . htmlspecialchars($lmm_options[ 'geolocate_followCircleStyle' ]) . "},
					followMarkerStyle: {" . htmlspecialchars($lmm_options[ 'geolocate_followMarkerStyle' ]) . "},
					icon: '" . $lmm_options[ 'geolocate_icon' ] . "',
					circlePadding: " . htmlspecialchars($lmm_options[ 'geolocate_circlePadding' ]) . ",
					metric: " . $lmm_options[ 'geolocate_units' ] . ",
					showPopup: " . $lmm_options[ 'geolocate_showPopup' ] . ",
					strings: {
						title: '" . __('Show me where I am','lmm') . "',
						popup: '" . sprintf(__('You are within %1$s %2$s from this point','lmm'), '{distance}', '{unit}') . "',
						outsideMapBoundsMsg: '" . __('You seem located outside the boundaries of the map','lmm') . "'
					},
					locateOptions: { " . htmlspecialchars($lmm_options[ 'geolocate_locateOptions' ]) . " }
				}).addTo(selectlayer);".PHP_EOL;
			if ( $lmm_options['geolocate_autostart'] == 'true' ) {
				echo "locatecontrol_selectlayer.start();";
			}
		} 
		?>

		mapcentermarker = new L.Marker(new L.LatLng(<?php echo $layerviewlat . ', ' . $layerviewlon; ?>),{ title: '<?php esc_attr_e('use this pin to center the layer (will only be shown in the admin area)','lmm'); ?>', clickable: true, draggable: true, zIndexOffset: 1000, opacity: 0.6 });
		mapcentermarker.options.icon = new L.Icon({iconUrl:'<?php echo LEAFLET_PLUGIN_URL . 'inc/img/icon-layer-center.png' ?>',iconSize: [32, 37],iconAnchor: [17, 37],shadowUrl: ''});
		mapcentermarker.addTo(selectlayer);
		var layers = {};
		var geojsonObj, mapIcon, marker_clickable, marker_title;

		<?php 
			if ($id != NULL) { //info: dont load geojson.php on new layer maps to save mysql queries+http requests
				if ($multi_layer_map == 0) { $id_for_geojson_url = $id; } else { $id_for_geojson_url = $multi_layer_map_list; }
				echo 'jQuery.get("' . LEAFLET_PLUGIN_URL . 'leaflet-geojson.php?layer=' . $id_for_geojson_url . '").done(function(data) {'.PHP_EOL; //info: async 1/2
					echo 'geojsonObj = data;'.PHP_EOL;
					$polygon_options_stroke = 'stroke: ' . $lmm_options['clustering_polygonOptions_stroke'];
					$polygon_options_color = 'color: \'#' . str_replace('#', '', htmlspecialchars($lmm_options['clustering_polygonOptions_color'])) . '\'';
					$polygon_options_weight = 'weight: ' . intval($lmm_options['clustering_polygonOptions_weight']);
					$polygon_options_opacity = 'opacity: ' . str_replace(',', '.', floatval($lmm_options['clustering_polygonOptions_opacity']));
					$polygon_options_fillcolor = 'fillColor: \'#' . str_replace('#', '', htmlspecialchars($lmm_options['clustering_polygonOptions_fillColor'])) . '\'';
					$polygon_options_fillopacity = 'fillOpacity: ' . str_replace(',', '.', floatval($lmm_options['clustering_polygonOptions_fillopacity']));
					$polygon_options_clickable= 'clickable: ' . $lmm_options['clustering_polygonOptions_clickable'];
					if ($lmm_options['clustering_polygonOptions_fill'] == 'auto') {
						$polygon_options_array = array($polygon_options_stroke, $polygon_options_color, $polygon_options_weight, $polygon_options_opacity, $polygon_options_fillcolor, $polygon_options_fillopacity, $polygon_options_clickable);
					} else {
						$polygon_options_fill = 'fill: false';
						$polygon_options_array = array($polygon_options_stroke, $polygon_options_color, $polygon_options_weight, $polygon_options_opacity, $polygon_options_fill, $polygon_options_fillcolor, $polygon_options_fillopacity, $polygon_options_clickable);
					}
					$polygon_options = implode(', ',$polygon_options_array);?>
				
					//info: markercluster progress bar
					var progress = document.getElementById('selectlayer-progress');
					var progressBar = document.getElementById('selectlayer-progress-bar');
					function updateProgressBar(processed, total, elapsed, layersArray) {
							if (elapsed > 1000) { //info: if it takes more than a second to load, display the progress bar:
							progress.style.display = 'block';
							progressBar.style.width = Math.round(processed/total*100) + '%';
						}
						if (processed === total) {
							progress.style.display = 'none'; //info: all markers processed - hide the progress bar
						}
					}
					markercluster = new L.MarkerClusterGroup({ zoomToBoundsOnClick: <?php echo $lmm_options['clustering_zoomToBoundsOnClick'] ?>, showCoverageOnHover: <?php echo $lmm_options['clustering_showCoverageOnHover'] ?>, spiderfyOnMaxZoom: <?php echo $lmm_options['clustering_spiderfyOnMaxZoom'] ?>, animateAddingMarkers: <?php echo $lmm_options['clustering_animateAddingMarkers'] ?>, disableClusteringAtZoom: <?php echo intval($lmm_options['clustering_disableClusteringAtZoom']) ?>, maxClusterRadius: <?php echo intval($lmm_options['clustering_maxClusterRadius']) ?>, polygonOptions: {<?php echo $polygon_options ?>}, singleMarkerMode: <?php echo $lmm_options['clustering_singleMarkerMode'] ?>, spiderfyDistanceMultiplier: <?php echo intval($lmm_options['clustering_spiderfyDistanceMultiplier']) ?>, chunkedLoading: true, chunkProgress: updateProgressBar });

					geojson_markers = L.geoJson(geojsonObj, {
						onEachFeature: function(feature, marker) {
							markerID[feature.properties.markerid] = marker;
						<?php  
						if ($lmm_options['directions_popuptext_panel'] == 'yes') {
							echo 'if (feature.properties.text != "") { var css = "border-top:1px solid #f0f0e7;padding-top:5px;margin-top:5px;clear:both;"; } else { var css = ""; }'.PHP_EOL;
							if ($lmm_options['defaults_marker_popups_add_markername'] == 'true') {
								echo 'if (feature.properties.markername != "") { var divmarkername1 = "<div class=\"popup-markername\"  style=\"border-bottom:1px solid #f0f0e7;padding-bottom:5px;margin-bottom:6px;\">"; var divmarkername2 = "</div>" } else { var divmarkername1 = ""; var divmarkername2 = ""; }'.PHP_EOL;
								echo 'marker.bindPopup(divmarkername1+feature.properties.markername+divmarkername2+feature.properties.text+"<div class=\"popup-directions\" style=\""+css+"\">"+feature.properties.address+" <a href=\""+feature.properties.dlink+"\" target=\"_blank\" title=\"' . esc_attr__('Get directions','lmm') . '\">(' . __('Directions','lmm') . ')</a></div>", {'.PHP_EOL;
							} else {
								echo 'marker.bindPopup(feature.properties.text+"<div class=\"popup-directions\" style=\""+css+"\">"+feature.properties.address+" <a href=\""+feature.properties.dlink+"\" target=\"_blank\" title=\"' . esc_attr__('Get directions','lmm') . '\">(' . __('Directions','lmm') . ')</a></div>", {'.PHP_EOL;
							}
								echo 'maxWidth: ' . intval($lmm_options['defaults_marker_popups_maxwidth']) . ','.PHP_EOL;
								echo 'minWidth: ' . intval($lmm_options['defaults_marker_popups_minwidth']) . ','.PHP_EOL;
								echo 'maxHeight: ' . intval($lmm_options['defaults_marker_popups_maxheight']) . ','.PHP_EOL;
								echo 'autoPan: ' . $lmm_options['defaults_marker_popups_autopan'] . ','.PHP_EOL;
								echo 'closeButton: ' . $lmm_options['defaults_marker_popups_closebutton'] . ','.PHP_EOL;
								echo 'autoPanPadding: new L.Point(' . intval($lmm_options['defaults_marker_popups_autopanpadding_x']) . ', ' . intval($lmm_options['defaults_marker_popups_autopanpadding_y']) . ')'.PHP_EOL;
							echo '});'.PHP_EOL;
						} else {
							echo 'if (feature.properties.text != "") {'.PHP_EOL;
							if ($lmm_options['defaults_marker_popups_add_markername'] == 'true') {
								echo 'if (feature.properties.markername != "") { var divmarkername1 = "<div class=\"popup-markername\"  style=\"border-bottom:1px solid #f0f0e7;padding-bottom:5px;margin-bottom:6px;\">"; var divmarkername2 = "</div>" } else { var divmarkername1 = ""; var divmarkername2 = ""; }'.PHP_EOL;
								echo 'marker.bindPopup(divmarkername1+feature.properties.markername+divmarkername2+feature.properties.text, {'.PHP_EOL;
							} else {				
								echo 'marker.bindPopup(feature.properties.text, {'.PHP_EOL;
							}
									echo 'maxWidth: ' . intval($lmm_options['defaults_marker_popups_maxwidth']) . ','.PHP_EOL;
									echo 'minWidth: ' . intval($lmm_options['defaults_marker_popups_minwidth']) . ','.PHP_EOL;
									echo 'maxHeight: ' . intval($lmm_options['defaults_marker_popups_maxheight']) . ','.PHP_EOL;
									echo 'autoPan: ' . $lmm_options['defaults_marker_popups_autopan'] . ','.PHP_EOL;
									echo 'closeButton: ' . $lmm_options['defaults_marker_popups_closebutton'] . ','.PHP_EOL;
									echo 'autoPanPadding: new L.Point(' . intval($lmm_options['defaults_marker_popups_autopanpadding_x']) . ', ' . intval($lmm_options['defaults_marker_popups_autopanpadding_y']) . ')'.PHP_EOL;
								echo '});'.PHP_EOL;
							echo '}'.PHP_EOL;
						}
						?>
						},
						pointToLayer: function (feature, latlng) {
							mapIcon = L.icon({
								iconUrl: (feature.properties.icon != '') ? "<?php echo $defaults_marker_icon_url ?>/" + feature.properties.icon : "<?php echo LEAFLET_PLUGIN_URL . 'leaflet-dist/images/marker.png' ?>",
								iconSize: [<?php echo intval($lmm_options[ 'defaults_marker_icon_iconsize_x' ]); ?>, <?php echo intval($lmm_options[ 'defaults_marker_icon_iconsize_y' ]); ?>],
								iconAnchor: [<?php echo intval($lmm_options[ 'defaults_marker_icon_iconanchor_x' ]); ?>, <?php echo intval($lmm_options[ 'defaults_marker_icon_iconanchor_y' ]); ?>],
								popupAnchor: [<?php echo intval($lmm_options[ 'defaults_marker_icon_popupanchor_x' ]); ?>, <?php echo intval($lmm_options[ 'defaults_marker_icon_popupanchor_y' ]); ?>],
								shadowUrl: '<?php echo $marker_shadow_url; ?>',
								shadowSize: [<?php echo intval($lmm_options[ 'defaults_marker_icon_shadowsize_x' ]); ?>, <?php echo intval($lmm_options[ 'defaults_marker_icon_shadowsize_y' ]); ?>],
								shadowAnchor: [<?php echo intval($lmm_options[ 'defaults_marker_icon_shadowanchor_x' ]); ?>, <?php echo intval($lmm_options[ 'defaults_marker_icon_shadowanchor_y' ]); ?>],
								className: (feature.properties.icon == '') ? "lmm_marker_icon_default" : "lmm_marker_icon_"+ feature.properties.icon.slice(0,-4)
							});
							if (feature.properties.text != "" || (feature.properties.dlink != "" && feature.properties.dlink != undefined)) { marker_clickable = true } else { marker_clickable = false };
							<?php if ($lmm_options[ 'defaults_marker_icon_title' ] == 'show') { ?>
								if (feature.properties.markername == '') { marker_title = '' } else { marker_title = feature.properties.markername };
							<?php }; ?>
							return L.marker(latlng, {icon: mapIcon, clickable: marker_clickable, title: marker_title, opacity: <?php echo floatval($lmm_options[ 'defaults_marker_icon_opacity' ]) ?>, alt: marker_title });
						}
					});
					geojson_markers<?php if ($lclustering == '1') {  echo '.addTo(markercluster);'.PHP_EOL; echo 'selectlayer.addLayer(markercluster);'; } else { echo '.addTo(selectlayer);'.PHP_EOL; } ?>
				}); //info: async 2/2
		<?php } //info: end if ($id != NULL) ?>

		<?php
		//info: set controlbox visibility 2/2
		if ($lcontrolbox == '0') { 
			echo "$('.leaflet-control-layers').hide();"; 
		} else if ($lcontrolbox == '2') { 
			echo "layersControl._expand();"; 
		}?>
		
		//info: load wms layer when checkbox gets checked
		$('#toggle-advanced-settings input:checkbox').click(function(el) {
			if(el.target.checked) {
				selectlayer.addLayer(window[el.target.id]);
			} else {
				selectlayer.removeLayer(window[el.target.id]);
			}

		});
		//info: update basemap when chosing from control box
		selectlayer.on('layeradd', function(e) {
		if (e.layer.options != undefined) { //needed for gpx
			if(e.layer.options.mmid) {
				selectlayer.attributionControl._attributions = [];
				$('#basemap').val(e.layer.options.mmid);
			}
		}
		});
		//info: when custom overlay gets checked from control box update hidden field
		selectlayer.on('layeradd', function(e) {
		if (e.layer.options != undefined) { //needed for gpx
			if(e.layer.options.olid) {
				$('#'+e.layer.options.olid).attr('value', '1');
			}
		}
		});
		//info: when custom overlay gets unchecked from control box update hidden field
		selectlayer.on('layerremove', function(e) {
			if(e.layer.options.olid) {
				$('#'+e.layer.options.olid).attr('value', '0');
			}
		});
		selectlayer.on('moveend', function(e) { document.getElementById('layerzoom').value = selectlayer.getZoom();});
		selectlayer.on('click', function(e) {
		  document.getElementById('layerviewlat').value = e.latlng.lat.toFixed(6);
		  document.getElementById('layerviewlon').value = e.latlng.lng.toFixed(6);
		  selectlayer.setView(e.latlng,selectlayer.getZoom());
		  mapcentermarker.setLatLng(e.latlng);
		});
		//info: set new coordinates on mapcentermarker drag
		mapcentermarker.on('dragend', function(e) {
			var newlocation = mapcentermarker.getLatLng();
			var newlat = newlocation['lat'];
			var newlon = newlocation['lng'];
			document.getElementById('layerviewlat').value = newlat.toFixed(6);
			document.getElementById('layerviewlon').value = newlon.toFixed(6);
			selectlayer.setView(newlocation,selectlayer.getZoom());
		});
		var mapElement = $('#selectlayer'), mapWidth = $('#mapwidth'), mapHeight = $('#mapheight'), layerviewlat = $('#layerviewlat'), layerviewlon = $('#layerviewlon'), panel = $('#lmm-panel'), gpxpanel = $('#gpx-panel-selectlayer'), gpxpanelcheckbox = $('#gpx_panel'), lmm = $('#lmm'), gpx_fitbounds_link = $('#gpx_fitbounds_link'), layername = $('#layername'), listmarkers = $('#lmm-listmarkers'), listmarkers_table = $('#lmm-listmarkers-table'), multi_layer_map = $('#lmm-multi_layer_map'), zoom = $('#layerzoom'), clustering = $('#clustering');
		//info: change zoom level when changing form field
		zoom.on('blur', function(e) {
			if(isNaN(zoom.val())) {
					alert('<?php esc_attr_e('Invalid format! Please only use numbers!','lmm') ?>');
			} else {
			selectlayer.setZoom(zoom.val());
			}
		});
		//info: bugfix causing maps not to show up in WP 3.0 and errors in WP <3.3
		layername.on('blur', function(e) {
			if( layername.val() ){
				document.getElementById('lmm-panel-text').innerHTML = layername.val();
			} else {
				document.getElementById('lmm-panel-text').innerHTML = '&nbsp;';
			};
		});
		mapWidth.blur(function() {
			if(!isNaN(mapWidth.val())) {
				lmm.css("width",mapWidth.val()+$('input:radio[name=mapwidthunit]:checked').val());
				listmarkers.css("width",mapWidth.val()+$('input:radio[name=mapwidthunit]:checked').val());
				listmarkers_table.css("width",mapWidth.val()+$('input:radio[name=mapwidthunit]:checked').val());
				selectlayer.invalidateSize();
			}
		});
		$('input:radio[name=mapwidthunit]').click(function() {
				lmm.css("width",mapWidth.val()+$('input:radio[name=mapwidthunit]:checked').val());
				listmarkers.css("width",mapWidth.val()+$('input:radio[name=mapwidthunit]:checked').val());
				listmarkers_table.css("width",mapWidth.val()+$('input:radio[name=mapwidthunit]:checked').val());
				selectlayer.invalidateSize();
		});
		mapHeight.blur(function() {
			if(!isNaN(mapHeight.val())) {
				mapElement.css("height",mapHeight.val()+"px");
				selectlayer.invalidateSize();
			}
		});
		//info: show/hide panel for layername & API URLs
		$('input:checkbox[name=panel]').click(function() {
			if($('input:checkbox[name=panel]').is(':checked')) {
				panel.css("display",'block');
			} else {
				panel.css("display",'none');
			}
		});
		<?php //info: upload to media library
		$gpx_track_color = '#' . str_replace('#', '', htmlspecialchars($lmm_options['gpx_track_color']));
		$gpx_startIconUrl = ($lmm_options['gpx_startIconUrl'] == NULL) ? LEAFLET_PLUGIN_URL . 'leaflet-dist/images/gpx-icon-start.png' : trim(htmlspecialchars($lmm_options['gpx_startIconUrl']));
		$gpx_endIconUrl = ($lmm_options['gpx_endIconUrl'] == NULL) ? LEAFLET_PLUGIN_URL . 'leaflet-dist/images/gpx-icon-end.png' : trim(htmlspecialchars($lmm_options['gpx_endIconUrl']));
		$gpx_shadowUrl = ($lmm_options['gpx_shadowUrl'] == NULL) ? LEAFLET_PLUGIN_URL . 'leaflet-dist/images/gpx-icon-shadow.png' : trim(htmlspecialchars($lmm_options['gpx_shadowUrl']));
		if ( (isset($lmm_options[ 'gpx_metadata_name' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_name' ] == 1 ) ) {
					$gpx_metadata_name_js = 'if (gpx.get_name() != undefined) { _c("gpx-name").innerHTML = gpx.get_name(); } else { _c("gpx-name").innerHTML = "n/a"; }';
		} else { $gpx_metadata_name_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_start' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_start' ] == 1 ) ) {
			$gpx_metadata_start_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-start").innerHTML = gpx.get_start_time().toDateString() + ", " + gpx.get_start_time().toLocaleTimeString(); } else { _c("gpx-start").innerHTML = "n/a"; }';
		} else { $gpx_metadata_start_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_end' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_end' ] == 1 ) ) {
			$gpx_metadata_end_js = 'if (gpx.get_end_time() != undefined) { _c("gpx-end").innerHTML = gpx.get_end_time().toDateString() + ", " + gpx.get_end_time().toLocaleTimeString(); } else { _c("gpx-end").innerHTML = "n/a"; }';
		} else { $gpx_metadata_end_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_distance' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_distance' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_distance_js = 'if (gpx.get_distance() != "0") { _c("gpx-distance").innerHTML = (gpx.get_distance()/1000).toFixed(2); } else { _c("gpx-distance").innerHTML = "n/a"; }';
			} else {
				$gpx_metadata_distance_js = 'if (gpx.get_distance() != "0") { _c("gpx-distance").innerHTML = gpx.get_distance_imp().toFixed(2); } else { _c("gpx-distance").innerHTML = "n/a"; }';
			}
		} else { $gpx_metadata_distance_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_duration_moving' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_duration_moving' ] == 1 ) ) {
			$gpx_metadata_duration_moving_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-duration-moving").innerHTML = gpx.get_duration_string(gpx.get_moving_time()); } else { _c("gpx-duration-moving").innerHTML = "n/a"; }';
		} else { $gpx_metadata_duration_moving_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_duration_total' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_duration_total' ] == 1 ) ) {
			$gpx_metadata_duration_total_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-duration-total").innerHTML = gpx.get_duration_string(gpx.get_total_time()); } else { _c("gpx-duration-total").innerHTML = "n/a"; }';
		} else { $gpx_metadata_duration_total_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_avpace' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_avpace' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_avpace_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-avpace").innerHTML = gpx.get_duration_string(gpx.get_moving_pace(), true); } else { _c("gpx-avpace").innerHTML = "n/a"; }';
			} else {
			$gpx_metadata_avpace_js = 'if (gpx.get_start_time() != undefined) { _c("gpx-avpace").innerHTML = gpx.get_duration_string(gpx.get_moving_pace_imp(), true); } else { _c("gpx-avpace").innerHTML = "n/a"; }';
			}
		} else { $gpx_metadata_avpace_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_avhr' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_avhr' ] == 1 ) ) {
			$gpx_metadata_avhr_js = 'if (isNaN(gpx.get_average_hr())) { _c("gpx-avghr").innerHTML = "n/a"; } else { _c("gpx-avghr").innerHTML = gpx.get_average_hr() + "bpm"; }';
		} else { $gpx_metadata_avhr_js = ''; }
		if ( ((isset($lmm_options[ 'gpx_metadata_elev_gain' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_gain' ] == 1 )) || ((isset($lmm_options[ 'gpx_metadata_elev_loss' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_loss' ] == 1 )) || ((isset($lmm_options[ 'gpx_metadata_elev_net' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_net' ] == 1 )) ) {
			$gpx_metadata_elevation_title_js = '';
		} else { $gpx_metadata_elevation_title_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_elev_gain' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_gain' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_elev_gain_js = '_c("gpx-elevation-gain").innerHTML = gpx.get_elevation_gain().toFixed(0);';
			} else {
				$gpx_metadata_elev_gain_js = '_c("gpx-elevation-gain").innerHTML = gpx.to_ft(gpx.get_elevation_gain()).toFixed(0);';
			}
		} else { $gpx_metadata_elev_gain_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_elev_loss' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_loss' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_elev_loss_js = '_c("gpx-elevation-loss").innerHTML = gpx.get_elevation_loss().toFixed(0);';
			} else {
				$gpx_metadata_elev_loss_js = '_c("gpx-elevation-loss").innerHTML = gpx.to_ft(gpx.get_elevation_loss()).toFixed(0);';
			}
		} else { $gpx_metadata_elev_loss_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_elev_net' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_net' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_elev_net_js = '_c("gpx-elevation-net").innerHTML  = gpx.get_elevation_gain().toFixed(0) - gpx.get_elevation_loss().toFixed(0);';
			} else {
				$gpx_metadata_elev_net_js = '_c("gpx-elevation-net").innerHTML  = gpx.to_ft(gpx.get_elevation_gain() - gpx.get_elevation_loss()).toFixed(0);';
			}
		} else { $gpx_metadata_elev_net_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_elev_full' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_elev_full' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_elev_full_js = '_c("gpx-elevation-full").innerHTML    = gpx.get_elevation_data();';
			} else {
				$gpx_metadata_elev_full_js = '_c("gpx-elevation-full").innerHTML    = gpx.get_elevation_data_imp();';
			}
		} else { $gpx_metadata_elev_full_js = ''; }
		if ( (isset($lmm_options[ 'gpx_metadata_hr_full' ]) == TRUE ) && ($lmm_options[ 'gpx_metadata_hr_full' ] == 1 ) ) {
			if ($lmm_options[ 'gpx_metadata_units' ] == 'metric') {
				$gpx_metadata_hr_full_js = '_c("gpx-heartrate-full").innerHTML    = gpx.get_heartrate_data();';
			} else {
				$gpx_metadata_hr_full_js = '_c("gpx-heartrate-full").innerHTML    = gpx.get_heartrate_data_imp();';
			}
		} else { $gpx_metadata_hr_full_js = ''; }

		//info: transient for gpx proxy
		$transient_proxy = get_transient( 'leafletmapsmarkerpro_proxy_access' );
		if ( $transient_proxy === FALSE ) {
			$rand_number = substr(md5('123'.rand()), 0, 8);
			set_transient( 'leafletmapsmarkerpro_proxy_access', $rand_number, 60*10 );
			$transient_proxy = get_transient( 'leafletmapsmarkerpro_proxy_access' );
		}

		if ( version_compare( $wp_version, '3.5', '>=' ) ) {
			echo "var custom_uploader;
			$('#upload_gpx_file').click(function(e) {
				e.preventDefault();
				if (custom_uploader) {
					custom_uploader.open();
					return;
				}
				custom_uploader = wp.media.frames.file_frame = wp.media({
					title: '" . esc_attr__('Upload GPX track','lmm') . "',
					frame: 'select',
					library: { type: 'text/gpx' },
					button: {
						text: '" . esc_attr__('Insert GPX track','lmm') . "'
					},
					multiple: false
				});
				//info: when a file is selected, grab the URL and set it as the text field's value
				custom_uploader.on('select', function() {
					attachment = custom_uploader.state().get('selection').first().toJSON();
					$('#gpx_url').val(attachment.url);
					gpxpanelcheckbox.attr('checked','checked');
					gpxpanel.css('display','block');
					gpx_fitbounds_link.css('display','inline');

					$.ajax({
						url: '" . LEAFLET_PLUGIN_URL . "inc/proxy.php?url='+attachment.url+'&transient=" . $transient_proxy . "',
						dataType: 'text',
						type: 'POST'
					}).done(function(data) {
							//info: search data for <gpx tag (IIS7.0 issue)	
							try {
								if (window.addEventListener) { //info: indexof only available in IE9+
									if (data.toLowerCase().indexOf('<gpx') >= 0) { if (window.console) { console.log('GPX file seems to be ok'); } } else { jquery.error; };
								}
							} catch (err) {
								alert('" . esc_attr__('GPX file could not be parsed - please check your browser console for more information!','lmm') . "');
								if (window.console) console.log(data);
							}
							var gpx_panel = document.getElementById('gpx-panel-selectlayer');
								function _c(c) { return gpx_panel.querySelectorAll('.'+c)[0]; }
							var gpx_track = new L.GPX(attachment.url, {
								gpx_content: data,
								async: true,
								max_point_interval: " .  intval($lmm_options['gpx_max_point_interval']) . ",
								marker_options: {
									startIconUrl: '" . $gpx_startIconUrl . "',
									endIconUrl: '" . $gpx_endIconUrl . "',
									shadowUrl: '" . $gpx_shadowUrl . "',
									iconSize: [" .  $lmm_options['gpx_iconSize_x'] . ", " .  $lmm_options['gpx_iconSize_y'] . "],
									shadowSize: [" .  $lmm_options['gpx_shadowSize_x'] . ", " .  $lmm_options['gpx_shadowSize_y'] . "],
									iconAnchor: [" .  $lmm_options['gpx_iconAnchor_x'] . ", " .  $lmm_options['gpx_iconAnchor_y'] . "],
									shadowAnchor: [" .  $lmm_options['gpx_shadowAnchor_x'] . ", " .  $lmm_options['gpx_shadowAnchor_y'] . "],
									className: 'lmm_gpx_icons'
								},
								polyline_options: {
									color: '" . $gpx_track_color . "',
									weight: " . intval($lmm_options['gpx_track_weight']) . ",
									opacity: '" . str_replace(',', '.', floatval($lmm_options['gpx_track_opacity'])) . "',
									smoothFactor: '" . str_replace(',', '.', floatval($lmm_options['gpx_track_smoothFactor'])) . "',
									clickable: " . $lmm_options['gpx_track_clickable'] . ",
									noClip: " . $lmm_options['gpx_track_noClip'] . "
								}
								}).addTo(selectlayer);
							gpx_track.on('gpx_loaded', function(e) {
									var gpx = e.target;
									selectlayer.fitBounds(e.target.getBounds(), { padding: [25,25] });

									var new_mapcentermarker = selectlayer.getCenter()
									mapcentermarker.setLatLng(new_mapcentermarker);
									document.getElementById('layerviewlat').value = new_mapcentermarker.lat.toFixed(6);
									document.getElementById('layerviewlon').value = new_mapcentermarker.lng.toFixed(6);

									" . $gpx_metadata_name_js . "
									" . $gpx_metadata_start_js . "
									" . $gpx_metadata_end_js . "
									" . $gpx_metadata_distance_js . "
									" . $gpx_metadata_duration_moving_js . "
									" . $gpx_metadata_duration_total_js . "
									" . $gpx_metadata_avpace_js . "
									" . $gpx_metadata_avhr_js . "
									" . $gpx_metadata_elev_gain_js . "
									" . $gpx_metadata_elev_loss_js . "
									" . $gpx_metadata_elev_net_js . "
									" . $gpx_metadata_elev_full_js . "
									" . $gpx_metadata_hr_full_js . "
							});
						});
				});
				custom_uploader.open();
			});";
		} else { //info: WP <3.5
			echo "jQuery(document).ready(function() {
				jQuery('#upload_gpx_file').click(function() {
					formfield = jQuery('#gpx_url').attr('name');
					tb_show('', 'media-upload.php?tab=library&post_mime_type=text%2Fgpx&amp;TB_iframe=true');
					jQuery('#TB_overlay').css('z-index','1000');
					jQuery('#TB_window').css('z-index','10000');
					return false;
				});
				window.send_to_editor = function(html) {
					gpxurl = jQuery(html).attr('href');
					jQuery('#gpx_url').val(gpxurl);
					tb_remove();
				}
				 });";
		} ?>

		//info: show/hide gpx panel
		$('input:checkbox[name=gpx_panel]').click(function() {
			if($('input:checkbox[name=gpx_panel]').is(':checked')) {
				gpxpanel.css("display",'block');
			} else {
				gpxpanel.css("display",'none');
			}
		});
		//info: show fitbounds link on focus
		$('#gpx_url').focus(function() {
			gpx_fitbounds_link.css("display",'inline');
		});			
		//info: fit gpx map bounds on click
		$('.gpxfitbounds').click(function(e){
			var current_gpx_url = $('#gpx_url').val();
			$.ajax({
				url: '<?php echo LEAFLET_PLUGIN_URL; ?>inc/proxy.php?url='+current_gpx_url+'&transient=<?php echo $transient_proxy; ?>',
				dataType: 'text',
				type: 'POST'
			}).done(function(data) {
				//info: search data for <gpx tag (IIS7.0 issue)	
				try {
					if (window.addEventListener) { //info: indexof only available in IE9+
						if (data.toLowerCase().indexOf("<gpx") >= 0) { if (window.console) { console.log("GPX file seems to be ok"); } } else { jquery.error; };
					}
				} catch (err) {
					alert("<?php echo esc_attr__('GPX file could not be parsed - please check your browser console for more information!','lmm'); ?>");
					if (window.console) console.log(data);
				}
				var gpx_panel = document.getElementById('gpx-panel-selectlayer');
				function _c(c) { return gpx_panel.querySelectorAll('.'+c)[0]; }
				var gpx_track = new L.GPX(gpx_url, {
					gpx_content: data,
					async: true,
					max_point_interval: <?php echo intval($lmm_options['gpx_max_point_interval']); ?>,
					marker_options: {
						startIconUrl: "<?php echo $gpx_startIconUrl; ?>",
						endIconUrl: "<?php echo $gpx_endIconUrl; ?>",
						shadowUrl: "<?php echo $gpx_shadowUrl; ?>",
						iconSize: [<?php echo $lmm_options['gpx_iconSize_x']; ?>, <?php echo $lmm_options['gpx_iconSize_y']; ?>],
						shadowSize: [<?php echo $lmm_options['gpx_shadowSize_x']; ?>, <?php echo $lmm_options['gpx_shadowSize_y']; ?>],
						iconAnchor: [<?php echo $lmm_options['gpx_iconAnchor_x']; ?>, <?php echo $lmm_options['gpx_iconAnchor_y']; ?>],
						shadowAnchor: [<?php echo $lmm_options['gpx_shadowAnchor_x']; ?>, <?php echo $lmm_options['gpx_shadowAnchor_y']; ?>],
						className: 'lmm_gpx_icons'
					},
					polyline_options: {
						color: "<?php echo $gpx_track_color; ?>",
						weight: <?php echo intval($lmm_options['gpx_track_weight']); ?>,
						opacity: "<?php echo str_replace(',', '.', floatval($lmm_options['gpx_track_opacity'])); ?>",
						smoothFactor: "<?php echo str_replace(',', '.', floatval($lmm_options['gpx_track_smoothFactor'])); ?>",
						clickable: <?php echo $lmm_options['gpx_track_clickable']; ?>,
						noClip: <?php echo $lmm_options['gpx_track_noClip']; ?>
					}
					}).addTo(selectlayer);

					gpx_track.on('gpx_loaded', function(e) {
						var gpx = e.target;
						selectlayer.fitBounds(e.target.getBounds(), { padding: [25,25] } );

						var new_mapcentermarker = selectlayer.getCenter()
						mapcentermarker.setLatLng(new_mapcentermarker);
						document.getElementById('layerviewlat').value = new_mapcentermarker.lat.toFixed(6);
						document.getElementById('layerviewlon').value = new_mapcentermarker.lng.toFixed(6);

						<?php echo $gpx_metadata_name_js; ?>
						<?php echo $gpx_metadata_start_js; ?>
						<?php echo $gpx_metadata_end_js; ?>
						<?php echo $gpx_metadata_distance_js; ?>
						<?php echo $gpx_metadata_duration_moving_js; ?>
						<?php echo $gpx_metadata_duration_total_js; ?>
						<?php echo $gpx_metadata_avpace_js; ?>
						<?php echo $gpx_metadata_avhr_js; ?>
						<?php echo $gpx_metadata_elev_gain_js; ?>
						<?php echo $gpx_metadata_elev_loss_js; ?>
						<?php echo $gpx_metadata_elev_net_js; ?>
						<?php echo $gpx_metadata_elev_full_js; ?>
						<?php echo $gpx_metadata_hr_full_js; ?>
					});
			});
		});

		//info: show/hide markers list
		$('input:checkbox[name=listmarkers]').click(function() {
			if($('input:checkbox[name=listmarkers]').is(':checked')) {
				listmarkers.css("display",'block');
			} else {
				listmarkers.css("display",'none');
			}
		});
		//info: show/hide multi-layer-map layer list
		$('input:checkbox[name=multi_layer_map]').click(function() {
			if($('input:checkbox[name=multi_layer_map]').is(':checked')) {
				multi_layer_map.css("display",'block');
			} else {
				multi_layer_map.css("display",'none');
			}
		});
		//info: toggle marker clustering
		$('input:checkbox[name=clustering]').click(function() {
			if($('input:checkbox[name=clustering]').is(':checked')) {
				selectlayer.removeLayer(geojson_markers);
				geojson_markers.addTo(markercluster);
				selectlayer.addLayer(markercluster);
			} else {
				markercluster.clearLayers();
				geojson_markers.addTo(selectlayer);
			}
		});
		//info: check if layerviewlat is a number
		$('input:text[name=layerviewlat]').blur(function(e) {
			if(isNaN(layerviewlat.val())) {
					alert('<?php esc_attr_e('Invalid format! Please only use numbers and a . instead of a , as decimal separator!','lmm') ?>');
			}
		});
		//info: check if layerviewlon is a number
		$('input:text[name=layerviewlon]').blur(function(e) {
			if(isNaN(layerviewlon.val())) {
					alert('<?php esc_attr_e('Invalid format! Please only use numbers and a . instead of a , as decimal separator!','lmm') ?>');
			}
		});
		//info: dynamic update of control box status
		$('input:radio[name=controlbox]').click(function() {
			if($('input:radio[name=controlbox]:checked').val() == 0) {
				$('.leaflet-control-layers').hide();
			}
			if($('input:radio[name=controlbox]:checked').val() == 1) {
				$('.leaflet-control-layers').show();
				layersControl._collapse();
			}
			if($('input:radio[name=controlbox]:checked').val() == 2) {
				$('.leaflet-control-layers').show();
				layersControl._expand();
			}
		});
		//info: show all API links on click on simplified editor
		$('#apilinkstext').click(function(e) {
			$('#apilinkstext').hide();
			$('#apilinks').show('fast');
		});
		//info: sets map center to new layer center position when entering lat/lon manually
		$('input:text[name=layerviewlat],input:text[name=layerviewlon]').blur(function(e) {
			var mapcentermarker_new = new L.LatLng(layerviewlat.val(),layerviewlon.val());
			mapcentermarker.setLatLng(mapcentermarker_new);
			selectlayer.setView(mapcentermarker_new, selectlayer.getZoom());
		});
		
		//info: switch between simplified and advanced editor
		$('#switch-link-visible').click(function(e) {
			$('#switch-link-visible').toggle();
			$('#switch-link-hidden').toggle();
			var active_editor = $('#active_editor').val();
			if (active_editor == 'advanced') {
				$('#active_editor').val('simplified');
			} else {
				$('#active_editor').val('advanced');
			}
			$('#apilinkstext').show();
			$('#apilinks').hide();
			$('#toggle-google-settings').toggle(); 
			$('#toggle-coordinates').toggle(); 
			$('#toogle-global-maximum-zoom-level').toggle();
			$('#toggle-controlbox-panel-kmltimestamp-backlinks-minimaps').toggle();
			$('#toggle-listofmarkerssettings').toggle();
			$('#toggle-clustersettings').toggle();
			$('#toggle-advanced-settings').toggle();
			$('#toggle-audit').toggle();
		});
		$('#switch-link-hidden').click(function(e) {
			$('#switch-link-visible').toggle();
			$('#switch-link-hidden').toggle();
			var active_editor = $('#active_editor').val();
			if (active_editor == 'advanced') {
				$('#active_editor').val('simplified');
			} else {
				$('#active_editor').val('advanced');
			}
			$('#apilinkstext').hide();
			$('#apilinks').show();
			$('#toggle-google-settings').toggle(); 
			$('#toggle-coordinates').toggle();
			$('#toogle-global-maximum-zoom-level').toggle();
			$('#toggle-controlbox-panel-kmltimestamp-backlinks-minimaps').toggle();
			$('#toggle-listofmarkerssettings').toggle();
			$('#toggle-clustersettings').toggle();
			$('#toggle-advanced-settings').toggle();
			$('#toggle-audit').toggle();
		});
		//info: warn on unsaved changes when leaving page
		var unsaved = false;
		$(":input, textarea, tinymce").change(function(){
			unsaved = true;
		});
		selectlayer.on('zoomend click', function(e) {
			unsaved = true;
		});
		mapcentermarker.on('dragend', function(e) {
			unsaved = true;
		});
		$('#submit_top, #submit_bottom, #delete, #delete_layer_and_markers').click(function() {
			unsaved = false;
		});
		function unloadPage(){ 
			if(unsaved){
				return "<?php esc_attr_e('You have unsaved changes on this page. Do you want to leave this page and discard your changes or stay on this page?','lmm'); ?>";
			}
		}
		window.onbeforeunload = unloadPage;		
		//info: remove readonly for address field to prevent typing before Google Places is loaded
		$(document).ready(function(){
			document.getElementById('address').disabled = false;
		});
	})(jQuery)

	//info: openpopup and center map on click on markername in list of markers
	function listmarkers_action(id) {
		var newlocation = markerID[id].getLatLng();
		selectlayer.setView(newlocation,selectlayer.getZoom());
		<?php 
			if ($lclustering == '1') {
				echo "markercluster.clearLayers();".PHP_EOL;
				echo "geojson_markers.addTo(selectlayer);".PHP_EOL; 
			}
			if ($lmm_options['defaults_layer_listmarkers_link_action'] == 'setview-open') {
				echo 'markerID[id].openPopup();';
			} 
		?>
	}
	
	//info: workaround to make google ads clickable on layer maps - not working when GPX track is added too :-/
	<?php if ($lmm_options['google_adsense_status'] == 'enabled') {
			if ($gpx_url == NULL) {
				echo "if (window.addEventListener) { //info: IE9+ check
							document.addEventListener('DOMContentLoaded', function () {
								var leaflet_overlay_pane = document.getElementsByClassName('leaflet-overlay-pane');
								for(var i=0; i<leaflet_overlay_pane.length; i++) {
									leaflet_overlay_pane[i].style.display='none';
								}
							});
					  }
				";
			} else {
				echo "if (window.console) { console.log('Info: Google ads are not clickable on layer maps if a GPX track has been added too!'); }";
			}
	} ?>

	//info: Google address autocomplete
	<?php if ($lmm_options['google_places_status'] == 'enabled')  { ?>
		gLoader = function(){
			function initAutocomplete() {
				var input = document.getElementById('address');
				<?php if ($lmm_options[ 'google_places_bounds_status' ] == 'enabled') { ?>
				var defaultBounds = new google.maps.LatLngBounds(
					new google.maps.LatLng(<?php echo floatval($lmm_options[ 'google_places_bounds_lat1' ]) ?>, <?php echo floatval($lmm_options[ 'google_places_bounds_lon1' ]) ?>),
					new google.maps.LatLng(<?php echo floatval($lmm_options[ 'google_places_bounds_lat2' ]) ?>, <?php echo floatval($lmm_options[ 'google_places_bounds_lon2' ]) ?>));
				<?php }?>
				var autocomplete = new google.maps.places.Autocomplete(input<?php if ($lmm_options[ 'google_places_bounds_status' ] == 'enabled') { echo ', {bounds: defaultBounds}'; } ?>);
				input.onfocus = function(){
					<?php if ($lmm_options[ 'google_places_search_prefix_status' ] == 'enabled' ) { ?>
					input.value = "<?php echo htmlspecialchars(addslashes($lmm_options[ 'google_places_search_prefix' ])); ?>";
					<?php } ?>
				};
				google.maps.event.addListener(autocomplete, 'place_changed', function() {
					var place = autocomplete.getPlace();
					var map = selectlayer;
					var markerLocation = new L.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
					mapcentermarker.setLatLng(markerLocation);
					map.setView(markerLocation, selectlayer.getZoom());
					document.getElementById('layerviewlat').value = place.geometry.location.lat().toFixed(6);
					document.getElementById('layerviewlon').value = place.geometry.location.lng().toFixed(6);
				 });
				var input = document.getElementById('address');
				google.maps.event.addDomListener(input, 'keydown',
				function(e) {
									if (e.keyCode == 13) {
													if (e.preventDefault) {
																	e.preventDefault();
													} else { //info:  Since the google event handler framework does not handle early IE versions, we have to do it by our self. :-(
																	e.cancelBubble = true;
																	e.returnValue = false;
													}
									}
					});
			}
			return{
			autocomplete:initAutocomplete
			}
		}();
		gLoader.autocomplete();
		/* //]]> */
	<?php } ?>
	</script>
<?php
	} //info: check if layer exists - part 2
} //info: !empty($action) 3/3
include('inc' . DIRECTORY_SEPARATOR . 'admin-footer.php');
?>