<?php
//info prevent file from being accessed directly
if (basename($_SERVER['SCRIPT_FILENAME']) == 'ajax-actions.php') { die ("Please do not access this file directly. Thanks!<br/><a href='https://www.mapsmarker.com/go'>www.mapsmarker.com</a>"); }

$ajax_results = array();

if( !isset( $_POST['lmm_ajax_nonce'] ) || !wp_verify_nonce($_POST['lmm_ajax_nonce'], 'lmm-ajax-nonce') ) {
	$ajax_results['status-class'] = 'error';
	$ajax_results['status-text'] = __('Permissions check failed or WordPress nonce has expired - please reload the page to try again!','lmm');
	echo json_encode($ajax_results);
	die();
}

global $wpdb, $current_user;
$table_name_markers = $wpdb->prefix.'leafletmapsmarker_markers';
$table_name_layers = $wpdb->prefix.'leafletmapsmarker_layers';

//info: functions for capability checks (marker+layer)
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

//info: global settings
$ajax_subaction = $_POST['lmm_ajax_subaction'];
$oid = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : '');

//info: individual marker / layer settings
if (($ajax_subaction == 'marker-add') || ($ajax_subaction == 'marker-edit')) {
	$lat_check = isset($_POST['lat']) ? $_POST['lat'] : (isset($_GET['lat']) ? $_GET['lat'] : '');
	$lon_check = isset($_POST['lon']) ? $_POST['lon'] : (isset($_GET['lon']) ? $_GET['lon'] : '');
	$layer = ($_POST['layer']!="") ? json_encode($_POST['layer']) : json_encode(array("0"));
} else if (($ajax_subaction == 'layer-add') || ($ajax_subaction == 'layer-edit')) {
	$lat_check = isset($_POST['layerviewlat']) ? $_POST['layerviewlat'] : (isset($_GET['layerviewlat']) ? $_GET['layerviewlat'] : '');
	$lon_check = isset($_POST['layerviewlon']) ? $_POST['layerviewlon'] : (isset($_GET['layerviewlon']) ? $_GET['layerviewlon'] : '');	
}
/**********************************************/
if ($ajax_subaction == 'marker-add') {

	if ( ($lat_check != NULL) && ($lon_check != NULL) ) {
		$markername_quotes = str_replace("\\\\","/", str_replace("\"","'", $_POST['markername'])); //info: geojson validity fixes
		$popuptext = preg_replace("/\t/", " ", str_replace("\\\\","/", $_POST['popuptext'])); //info: geojson validity fixes
		$address = preg_replace("/(\\\\)(?!')/","/", preg_replace("/\t/", " ", $_POST['address'])); //info: geojson validity fixes
		if ($_POST['kml_timestamp'] == NULL) {

			$result = $wpdb->prepare( "INSERT INTO `$table_name_markers` (`markername`, `basemap`, `layer`, `lat`, `lon`, `icon`, `popuptext`, `zoom`, `openpopup`, `mapwidth`, `mapwidthunit`, `mapheight`, `panel`, `createdby`, `createdon`, `updatedby`, `updatedon`, `controlbox`, `overlays_custom`, `overlays_custom2`, `overlays_custom3`, `overlays_custom4`, `wms`, `wms2`, `wms3`, `wms4`, `wms5`, `wms6`, `wms7`, `wms8`, `wms9`, `wms10`, `address`, `gpx_url`, `gpx_panel`) VALUES (%s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %s, %d, %d, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %d )", $markername_quotes, $_POST['basemap'], $layer, str_replace(',', '.', $_POST['lat']), str_replace(',', '.', $_POST['lon']), $_POST['icon_hidden'], $popuptext, $_POST['zoom'], $_POST['openpopup'], $_POST['mapwidth'], $_POST['mapwidthunit'], $_POST['mapheight'], $_POST['panel'], $_POST['createdby'], $_POST['createdon'], $_POST['updatedby'], $_POST['updatedon'], $_POST['controlbox'], $_POST['overlays_custom'], $_POST['overlays_custom2'], $_POST['overlays_custom3'], $_POST['overlays_custom4'], $_POST['wms'], $_POST['wms2'], $_POST['wms3'], $_POST['wms4'], $_POST['wms5'], $_POST['wms6'], $_POST['wms7'], $_POST['wms8'], $_POST['wms9'], $_POST['wms10'], $address, trim($_POST['gpx_url']), $_POST['gpx_panel'] );
		} else if ($_POST['kml_timestamp'] != NULL) {
			$result = $wpdb->prepare( "INSERT INTO `$table_name_markers` (`markername`, `basemap`, `layer`, `lat`, `lon`, `icon`, `popuptext`, `zoom`, `openpopup`, `mapwidth`, `mapwidthunit`, `mapheight`, `panel`, `createdby`, `createdon`, `updatedby`, `updatedon`, `controlbox`, `overlays_custom`, `overlays_custom2`, `overlays_custom3`, `overlays_custom4`, `wms`, `wms2`, `wms3`, `wms4`, `wms5`, `wms6`, `wms7`, `wms8`, `wms9`, `wms10`, `kml_timestamp`, `address`, `gpx_url`, `gpx_panel`) VALUES (%s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %s, %d, %d, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %s, %d )", $markername_quotes, $_POST['basemap'], $layer, str_replace(',', '.', $_POST['lat']), str_replace(',', '.', $_POST['lon']), $_POST['icon_hidden'], $popuptext, $_POST['zoom'], $_POST['openpopup'], $_POST['mapwidth'], $_POST['mapwidthunit'], $_POST['mapheight'], $_POST['panel'], $_POST['createdby'], $_POST['createdon'], $_POST['updatedby'], $_POST['updatedon'], $_POST['controlbox'], $_POST['overlays_custom'], $_POST['overlays_custom2'], $_POST['overlays_custom3'], $_POST['overlays_custom4'], $_POST['wms'], $_POST['wms2'], $_POST['wms3'], $_POST['wms4'], $_POST['wms5'], $_POST['wms6'], $_POST['wms7'], $_POST['wms8'], $_POST['wms9'], $_POST['wms10'], $_POST['kml_timestamp'], $address, trim($_POST['gpx_url']), $_POST['gpx_panel'] );
		}
		$wpdb->query( $result );
		$wpdb->query( "OPTIMIZE TABLE `$table_name_markers`" );
		$ajax_results['status-class'] = 'updated';
		$ajax_results['status-text'] = sprintf(__('The marker with the ID %1$s has been successfully published','lmm'), $wpdb->insert_id);
		$ajax_results['newmarkerid'] = $wpdb->insert_id;
		$ajax_results['layerid'] = implode(',', json_decode($layer));
		$ajax_results['markername'] = __('Edit marker','lmm') . ' "' . stripslashes($_POST['markername']) . '"';
	} else {
		$ajax_results['status-class'] = 'error';
		$ajax_results['status-text'] = __('Error: coordinates cannot be empty!','lmm');
	}
	echo json_encode($ajax_results);
	die();

/**********************************************/
} else if ($ajax_subaction == 'marker-edit') {
	$createdby_check = $wpdb->get_var( 'SELECT `createdby` FROM `'.$table_name_markers.'` WHERE id='.$oid );
	if (lmm_check_capability_edit($createdby_check) == TRUE) {
		if ( ($lat_check != NULL) && ($lon_check != NULL) ) {
			$markername_quotes = str_replace("\\\\","/", str_replace("\"","'", $_POST['markername'])); //info: geojson validity fixes
			$popuptext = preg_replace("/\t/", " ", str_replace("\\\\","/", $_POST['popuptext'])); //info: geojson validity fixes
			$address = preg_replace("/(\\\\)(?!')/","/", preg_replace("/\t/", " ", $_POST['address'])); //info: geojson validity fixes
			if ($_POST['kml_timestamp'] == NULL) {
				$result = $wpdb->prepare( "UPDATE `$table_name_markers` SET `markername` = %s, `basemap` = %s, `layer` = %s, `lat` = %s, `lon` = %s, `icon` = %s, `popuptext` = %s, `zoom` = %d, `openpopup` = %d, `mapwidth` = %d, `mapwidthunit` = %s, `mapheight` = %d, `panel` = %d, `createdby` = %s, `createdon` = %s, `updatedby` = %s, `updatedon` = %s, `controlbox` = %d, `overlays_custom` = %s, `overlays_custom2` = %s, `overlays_custom3` = %s, `overlays_custom4` = %s, `wms` = %d, `wms2` = %d, `wms3` = %d, `wms4` = %d, `wms5` = %d, `wms6` = %d, `wms7` = %d, `wms8` = %d, `wms9` = %d, `wms10` = %d, `address` = %s, `gpx_url` = %s, `gpx_panel` = %d WHERE `id` = %d", $markername_quotes, $_POST['basemap'], $layer, str_replace(',', '.', $_POST['lat']), str_replace(',', '.', $_POST['lon']), $_POST['icon_hidden'], $popuptext, $_POST['zoom'], $_POST['openpopup'], $_POST['mapwidth'], $_POST['mapwidthunit'], $_POST['mapheight'], $_POST['panel'], $_POST['createdby'], $_POST['createdon'], $_POST['updatedby'], $_POST['updatedon'], $_POST['controlbox'], $_POST['overlays_custom'], $_POST['overlays_custom2'], $_POST['overlays_custom3'], $_POST['overlays_custom4'], $_POST['wms'], $_POST['wms2'], $_POST['wms3'], $_POST['wms4'], $_POST['wms5'], $_POST['wms6'], $_POST['wms7'], $_POST['wms8'], $_POST['wms9'], $_POST['wms10'], $address, trim($_POST['gpx_url']), $_POST['gpx_panel'], $oid );
			} else if ($_POST['kml_timestamp'] != NULL) {
				$result = $wpdb->prepare( "UPDATE `$table_name_markers` SET `markername` = %s, `basemap` = %s, `layer` = %s, `lat` = %s, `lon` = %s, `icon` = %s, `popuptext` = %s, `zoom` = %d, `openpopup` = %d, `mapwidth` = %d, `mapwidthunit` = %s, `mapheight` = %d, `panel` = %d, `createdby` = %s, `createdon` = %s, `updatedby` = %s, `updatedon` = %s, `controlbox` = %d, `overlays_custom` = %s, `overlays_custom2` = %s, `overlays_custom3` = %s, `overlays_custom4` = %s, `wms` = %d, `wms2` = %d, `wms3` = %d, `wms4` = %d, `wms5` = %d, `wms6` = %d, `wms7` = %d, `wms8` = %d, `wms9` = %d, `wms10` = %d, `kml_timestamp` = %s, `address` = %s, `gpx_url` = %s, `gpx_panel` = %d WHERE `id` = %d", $markername_quotes, $_POST['basemap'], $layer, str_replace(',', '.', $_POST['lat']), str_replace(',', '.', $_POST['lon']), $_POST['icon_hidden'], $popuptext, $_POST['zoom'], $_POST['openpopup'], $_POST['mapwidth'], $_POST['mapwidthunit'], $_POST['mapheight'], $_POST['panel'], $_POST['createdby'], $_POST['createdon'], $_POST['updatedby'], $_POST['updatedon'], $_POST['controlbox'], $_POST['overlays_custom'], $_POST['overlays_custom2'], $_POST['overlays_custom3'], $_POST['overlays_custom4'], $_POST['wms'], $_POST['wms2'], $_POST['wms3'], $_POST['wms4'], $_POST['wms5'], $_POST['wms6'], $_POST['wms7'], $_POST['wms8'], $_POST['wms9'], $_POST['wms10'], $_POST['kml_timestamp'], $address, trim($_POST['gpx_url']), $_POST['gpx_panel'], $oid );
			}
			
			$wpdb->query( $result );
			$wpdb->query( "OPTIMIZE TABLE `$table_name_markers`" );
			$ajax_results['status-class'] = 'updated';
			$ajax_results['status-text'] = sprintf(__('The marker with the ID %1$s has been successfully updated','lmm'), intval($_POST['id']));
			$ajax_results['markerid'] = $oid;
			$ajax_results['layerid'] = implode(',', json_decode($layer));
			$ajax_results['markername'] = __('Edit marker','lmm') . ' "' . stripslashes($_POST['markername']) . '"';
			$ajax_results['updatedby_saved'] = $_POST['updatedby'];
			$ajax_results['updatedon_saved'] = $_POST['updatedon'];
			$ajax_results['updatedby_next'] = $current_user->user_login;
			$ajax_results['updatedon_next'] = current_time('mysql',0);
		} else {
			$ajax_results['status-class'] = 'error';
			$ajax_results['status-text'] = __('Error: coordinates cannot be empty!','lmm');
		}
	} else {
		$ajax_results['status-class'] = 'error';
		$ajax_results['status-text'] = __('Error: your user does not have the permission to edit markers from other users!','lmm');
	}
	echo json_encode($ajax_results);
	die();

/**********************************************/
} else if ($ajax_subaction == 'marker-delete') {
	$createdby_check = $wpdb->get_var( 'SELECT `createdby` FROM `'.$table_name_markers.'` WHERE id='.$oid );
	if (lmm_check_capability_edit($createdby_check) == TRUE) {
		if (!empty($oid)) {
			$result = $wpdb->prepare( "DELETE FROM `$table_name_markers` WHERE `id` = %d", $oid );
			$wpdb->query( $result );
			$wpdb->query( "OPTIMIZE TABLE `$table_name_markers`" );
			//info: delete qr code cache image
			if ( file_exists(LEAFLET_PLUGIN_QR_DIR . DIRECTORY_SEPARATOR . 'marker-' . $oid . '.png') ) {
				unlink(LEAFLET_PLUGIN_QR_DIR . DIRECTORY_SEPARATOR . 'marker-' . $oid . '.png');
			}
			$ajax_results['status-class'] = 'updated';
			$ajax_results['status-text'] = sprintf(__('The marker with the ID %1$s has been successfully deleted','lmm'), $oid);
		}
	} else {
		$ajax_results['status-class'] = 'error';
		$ajax_results['status-text'] = __('Error: your user does not have the permission to delete markers from other users!','lmm');
	}
	echo json_encode($ajax_results);
	die();

/**********************************************/
} else if ($ajax_subaction == 'marker-duplicate') {
	$result = $wpdb->get_row( $wpdb->prepare('SELECT * FROM `'.$table_name_markers.'` WHERE `id` = %d', $oid), ARRAY_A);
	if ($result['kml_timestamp'] == NULL) {
		$sql_duplicate = $wpdb->prepare( "INSERT INTO `$table_name_markers` (`markername`, `basemap`, `layer`, `lat`, `lon`, `icon`, `popuptext`, `zoom`, `openpopup`, `mapwidth`, `mapwidthunit`, `mapheight`, `panel`, `createdby`, `createdon`, `updatedby`, `updatedon`, `controlbox`, `overlays_custom`, `overlays_custom2`, `overlays_custom3`, `overlays_custom4`, `wms`, `wms2`, `wms3`, `wms4`, `wms5`, `wms6`, `wms7`, `wms8`, `wms9`, `wms10`, `address`, `gpx_url`, `gpx_panel`) VALUES (%s, %s, %d, %s, %s, %s, %s, %d, %d, %d, %s, %d, %d, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %d )", $result['markername'], $result['basemap'], $result['layer'], $result['lat'], $result['lon'], $result['icon'], $result['popuptext'], $result['zoom'], $result['openpopup'], $result['mapwidth'], $result['mapwidthunit'], $result['mapheight'], $result['panel'], $current_user->user_login, current_time('mysql',0), $current_user->user_login, current_time('mysql',0), $result['controlbox'], $result['overlays_custom'], $result['overlays_custom2'], $result['overlays_custom3'], $result['overlays_custom4'], $result['wms'], $result['wms2'], $result['wms3'], $result['wms4'], $result['wms5'], $result['wms6'], $result['wms7'], $result['wms8'], $result['wms9'], $result['wms10'], $result['address'], $result['gpx_url'], $result['gpx_panel'] );
	} else if ($result['kml_timestamp'] != NULL) {
		$sql_duplicate = $wpdb->prepare( "INSERT INTO `$table_name_markers` (`markername`, `basemap`, `layer`, `lat`, `lon`, `icon`, `popuptext`, `zoom`, `openpopup`, `mapwidth`, `mapwidthunit`, `mapheight`, `panel`, `createdby`, `createdon`, `updatedby`, `updatedon`, `controlbox`, `overlays_custom`, `overlays_custom2`, `overlays_custom3`, `overlays_custom4`, `wms`, `wms2`, `wms3`, `wms4`, `wms5`, `wms6`, `wms7`, `wms8`, `wms9`, `wms10`, `kml_timestamp`, `address`, `gpx_url`, `gpx_panel`) VALUES (%s, %s, %d, %s, %s, %s, %s, %d, %d, %d, %s, %d, %d, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %s, %s, %s, %d )", $result['markername'], $result['basemap'], $result['layer'], $result['lat'], $result['lon'], $result['icon'], $result['popuptext'], $result['zoom'], $result['openpopup'], $result['mapwidth'], $result['mapwidthunit'], $result['mapheight'], $result['panel'], $current_user->user_login, current_time('mysql',0), $current_user->user_login, current_time('mysql',0), $result['controlbox'], $result['overlays_custom'], $result['overlays_custom2'], $result['overlays_custom3'], $result['overlays_custom4'], $result['wms'], $result['wms2'], $result['wms3'], $result['wms4'], $result['wms5'], $result['wms6'], $result['wms7'], $result['wms8'], $result['wms9'], $result['wms10'], $result['kml_timestamp'], $result['address'], $result['gpx_url'], $result['gpx_panel'] );
	}
	$wpdb->query( $sql_duplicate );
	$wpdb->query( "OPTIMIZE TABLE `$table_name_markers`" );
	$ajax_results['status-class'] = 'updated';
	$ajax_results['status-text'] = sprintf(__('The marker has been successfully duplicated - new ID: %1$s','lmm'), $wpdb->insert_id);
	$ajax_results['markername'] = __('Edit marker','lmm') . ' "' . stripslashes($result['markername']) . '"';
	$ajax_results['newmarkerid'] = $wpdb->insert_id;
	echo json_encode($ajax_results);
	die();

/**********************************************/
} else if ($ajax_subaction == 'layer-add') {
	//info: 2do
/**********************************************/
} else if ($ajax_subaction == 'layer-edit') {
	//info: 2do
/**********************************************/
} else if ($ajax_subaction == 'layer-deleteboth') {
	//info: 2do
/**********************************************/
} else if ($ajax_subaction == 'layer-delete') {
	//info: 2do
/**********************************************/
} else if ($ajax_subaction == 'layer-duplicate') {
	//info: 2do
/**********************************************/
} else if ($ajax_subaction == 'editor-switchlink') {
	if ( ($_POST['active_editor'] == 'simplified') || ($_POST['active_editor'] == 'advanced') ) {
		update_option( 'leafletmapsmarker_editor', $_POST['active_editor'] );
		if ($_POST['active_editor'] == 'advanced') {
			$ajax_results['status-class'] = 'updated';
			$ajax_results['status-text'] = __('Settings updated - you successfully switched to the advanced editor!','lmm');
		} else {
			$ajax_results['status-class'] = 'updated';
			$ajax_results['status-text'] = __('Settings updated - you successfully switched to the simplified editor!','lmm');
		}
		echo json_encode($ajax_results);
	} else {
		$ajax_results['status-class'] = 'error';
		$ajax_results['status-text'] = sprintf(__('Error - active_editor value cannot be set to %1$s!','lmm'), $_POST['active_editor']);
		echo json_encode($ajax_results);
	}
	die();
}
die();