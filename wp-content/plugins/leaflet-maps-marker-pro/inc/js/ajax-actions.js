jQuery(document).ready(function($) {

	var current_page = lmm_ajax_vars.lmm_ajax_current_page;
	var admin_url = lmm_ajax_vars.lmm_ajax_admin_url;
	var leaflet_plugin_url = lmm_ajax_vars.lmm_ajax_leaflet_plugin_url;
	var shortcode = lmm_ajax_vars.lmm_ajax_shortcode;

	//info: js for marker edit page
	if (current_page === 'leafletmapsmarker_marker') {

		//info: get popuptext
		function lmm_get_tinymce_content() {
			if ($('#wp-popuptext-wrap').hasClass('tmce-active')) {
				return tinyMCE.activeEditor.getContent();
			} else {
				return jQuery('#popuptext').val();
			}
		}

		//info: submit buttons clickable after load only
		$('#submit_top, #duplicate_button_top, #delete_button_top, #submit_bottom, #duplicate_button_bottom, #delete_button_bottom').attr('disabled', false);

		/************************************/
		//info: 1 submit function for add & edit
		$('#marker-add-edit').submit(function() {

			$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').hide();
			$('#lmm_ajax_loading_top, #lmm_ajax_loading_bottom').show();
			$('#submit_top, #duplicate_button_top, #delete_button_top, #submit_bottom, #duplicate_button_bottom, #delete_button_bottom').attr('disabled', true);

			//info: get values for checkboxes
			if (document.getElementById('openpopup').checked) { var openpopup_prepare = '1'; } else { var openpopup_prepare = '0'; }
			if (document.getElementById('panel').checked) { var panel_prepare = '1'; } else { var panel_prepare = '0'; }
			if (document.getElementById('wms')) { if (document.getElementById('wms').checked) { var wms_prepare = '1'; } else { var wms_prepare = '0'; } } else { var wms_prepare = '0'; }
			if (document.getElementById('wms2')) { if (document.getElementById('wms2').checked) { var wms2_prepare = '1'; } else { var wms2_prepare = '0'; } } else { var wms2_prepare = '0'; }
			if (document.getElementById('wms3')) { if (document.getElementById('wms3').checked) { var wms3_prepare = '1'; } else { var wms3_prepare = '0'; } } else { var wms3_prepare = '0'; }
			if (document.getElementById('wms4')) { if (document.getElementById('wms4').checked) { var wms4_prepare = '1'; } else { var wms4_prepare = '0'; } } else { var wms4_prepare = '0'; }
			if (document.getElementById('wms5')) { if (document.getElementById('wms5').checked) { var wms5_prepare = '1'; } else { var wms5_prepare = '0'; } } else { var wms5_prepare = '0'; }
			if (document.getElementById('wms6')) { if (document.getElementById('wms6').checked) { var wms6_prepare = '1'; } else { var wms6_prepare = '0'; } } else { var wms6_prepare = '0'; }
			if (document.getElementById('wms7')) { if (document.getElementById('wms7').checked) { var wms7_prepare = '1'; } else { var wms7_prepare = '0'; } } else { var wms7_prepare = '0'; }
			if (document.getElementById('wms8')) { if (document.getElementById('wms8').checked) { var wms8_prepare = '1'; } else { var wms8_prepare = '0'; } } else { var wms8_prepare = '0'; }
			if (document.getElementById('wms9')) { if (document.getElementById('wms9').checked) { var wms9_prepare = '1'; } else { var wms9_prepare = '0'; } } else { var wms9_prepare = '0'; }
			if (document.getElementById('wms10')) { if (document.getElementById('wms10').checked) { var wms10_prepare = '1'; } else { var wms10_prepare = '0'; } } else { var wms10_prepare = '0'; }
			if (document.getElementById('gpx_panel').checked) { var gpx_panel_prepare = '1'; } else { var gpx_panel_prepare = '0'; }

			if ($('#action-marker-add-edit').val() === 'add') { var lmm_ajax_subaction_prepare = 'marker-add'; } else { var lmm_ajax_subaction_prepare = 'marker-edit'; }

			data = {
				action: 'mapsmarker_ajax_actions',
				lmm_ajax_subaction: lmm_ajax_subaction_prepare,
				lmm_ajax_nonce: lmm_ajax_vars.lmm_ajax_nonce,
				id: $('#id').val(),
				markername: $('#markername').val(),
				basemap: $('#basemap').val(),
				layer: $('#layer').val(),
				lat: $('#lat').val(),
				lon: $('#lon').val(),
				icon_hidden: $('#icon_hidden').val(),
				popuptext: lmm_get_tinymce_content(),
				zoom: $('#zoom').val(),
				openpopup: openpopup_prepare,
				mapwidth: $('#mapwidth').val(),
				mapwidthunit: $('input[name=mapwidthunit]:checked', '#marker-add-edit').val(),
				mapheight: $('#mapheight').val(),
				panel: panel_prepare,
				createdby: $('#createdby').val(),
				createdon: $('#createdon').val(),
				updatedby: $('#updatedby_next').val(),
				updatedon: $('#updatedon_next').val(),
				controlbox: $('input[name=controlbox]:checked', '#marker-add-edit').val(),
				overlays_custom: $('#overlays_custom').val(),
				overlays_custom2: $('#overlays_custom2').val(),
				overlays_custom3: $('#overlays_custom3').val(),
				overlays_custom4: $('#overlays_custom4').val(),
				wms: wms_prepare,
				wms2: wms2_prepare,
				wms3: wms3_prepare,
				wms4: wms4_prepare,
				wms5: wms5_prepare,
				wms6: wms6_prepare,
				wms7: wms7_prepare,
				wms8: wms8_prepare,
				wms9: wms9_prepare,
				wms10: wms10_prepare,
				kml_timestamp: $('#kml_timestamp').val(),
				address: $('#address').val(),
				gpx_url: $('#gpx_url').val(),
				gpx_panel: gpx_panel_prepare
			};

			$.post(ajaxurl, data, function (response) {
				var results = JSON.parse(response);

				$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').attr('class',results['status-class']);
				$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').html(results['status-text']);
				$('#lmm_ajax_loading_top, #lmm_ajax_loading_bottom').hide();
				$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').show();
				$('#submit_top, #duplicate_button_top, #delete_button_top, #submit_bottom, #duplicate_button_bottom, #delete_button_bottom').attr('disabled', false);

				//info: update direction links
				if ($('#defaults_directions_directions_provider').val() === 'googlemaps') {
					if ( $('#address').val() === '') {
						var google_from = $('#lat').val()+','+$('#lon').val();
					} else {
						var google_from = encodeURIComponent($('#address').val());
					}
					$('#popup-directions, #panel-link-directions').attr('href', 'https://'+$('#defaults_directions_gmaps_base_domain_directions').val()+'/maps?daddr='+google_from+'&t='+$('#defaults_directions_directions_googlemaps_map_type').val()+'&layer='+$('#defaults_directions_directions_googlemaps_traffic').val()+'&doflg='+$('#defaults_directions_directions_googlemaps_distance_units').val()+$('#defaults_directions_google_avoidhighways').val()+$('#defaults_directions_google_avoidtolls').val()+$('#defaults_directions_google_publictransport').val()+$('#defaults_directions_google_walking').val()+$('#defaults_directions_google_language').val()+'&om='+$('#defaults_directions_directions_directions_googlemaps_overview_map').val());
				} else if ($('#defaults_directions_directions_provider').val() === 'yours') {
					$('#popup-directions, #panel-link-directions').attr('href', 'http://www.yournavigation.org/?tlat='+$('#lat').val()+'&tlon='+$('#lon').val()+'&v='+$('#defaults_directions_directions_yours_type_of_transport').val()+'&fast='+$('#defaults_directions_directions_yours_route_type').val()+'&layer='+$('#defaults_directions_directions_yours_layer').val());
				} else if ($('#defaults_directions_directions_provider').val() === 'osrm') {
					$('#popup-directions, #panel-link-directions').attr('href', 'http://map.project-osrm.org/?hl='+$('#defaults_directions_directions_osrm_language').val()+'&loc='+$('#lat').val()+','+$('#lon').val()+'&df='+$('#defaults_directions_directions_osrm_units').val());
				} else if ($('#defaults_directions_directions_provider').val() === 'ors') {
					$('#popup-directions, #panel-link-directions').attr('href', 'http://openrouteservice.org/index.php?end='+$('#lon').val()+','+$('#lat').val()+'&pref='+$('#defaults_directions_directions_ors_route_preferences').val()+'&lang='+$('#defaults_directions_directions_ors_language').val()+'&noMotorways='+$('#defaults_directions_directions_ors_no_motorways').val()+'&noTollways='+$('#defaults_directions_directions_ors_no_tollways').val());
				} else if ($('#defaults_directions_directions_provider').val() === 'bingmaps') {
					if ( $('#address').val() === '') {
						var bing_to = ''; 
					} else { 
						var bing_to = '_'+encodeURIComponent($('#address').val()); 
					}
					$('#popup-directions, #panel-link-directions').attr('href', 'http://www.bing.com/maps/default.aspx?v=2&rtp=pos___e_~pos.'+$('#lat').val()+'_'+$('#lon').val()+bing_to);
				}
				
				if ($('#action-marker-add-edit').val() === 'add') {
					if (results['status-class'] === 'updated') {
						if (history.pushState) { //info: not supported in IE8+9 
							window.history.pushState(null, null, 'admin.php?page=leafletmapsmarker_marker&id='+results['newmarkerid']);
						}
						$('#lmm-header-button2').removeClass('button-primary lmm-nav-primary');
						$('#lmm-header-button2').addClass('button-secondary lmm-nav-secondary');
						$('#marker-heading').html(results['markername']+' (ID '+results['newmarkerid']+')');
						$('#duplicate_span_top, #delete_span_top, #duplicate_span_bottom, #delete_span_bottom').show();
						$('#id').val(results['newmarkerid']);
						$('#submit_top, #submit_bottom').val($('#defaults_texts_update').val());
						$('#action-marker-add-edit').val('edit');
						$('#tr-shortcode').show();
						$('#shortcode').val('['+shortcode+' marker="'+results['newmarkerid']+'"]');
						$('#shortcode-link-kml, #panel-link-kml').attr('href', leaflet_plugin_url+'leaflet-kml.php?marker='+results['newmarkerid']+'&name='+lmm_ajax_vars.lmm_ajax_misc_kml);
						$('#shortcode-link-fullscreen, #panel-link-fullscreen').attr('href', leaflet_plugin_url+'leaflet-fullscreen.php?marker='+results['newmarkerid']);
						$('#shortcode-link-qr, #panel-link-qr').attr('href', leaflet_plugin_url+'leaflet-qr.php?marker='+results['newmarkerid']);
						$('#shortcode-link-geojson, #panel-link-geojson').attr('href', leaflet_plugin_url+'leaflet-geojson.php?marker='+results['newmarkerid']+'&callback=jsonp&full=yes&full_icon_url=yes');
						$('#shortcode-link-georss, #panel-link-georss').attr('href', leaflet_plugin_url+'leaflet-georss.php?marker='+results['newmarkerid']);
						$('#shortcode-link-wikitude, #panel-link-wikitude').attr('href', leaflet_plugin_url+'leaflet-wikitude.php?marker='+results['newmarkerid']);
						if (results['layerid'] != '0') {
							$('#layereditlink').show();
							$('#layereditlink-href').hide();
							$('#multilayeredit').html('');

							var layers = results['layerid'].split(',');
							var layers_length = layers.length;
							if(layers_length > 0){
								$('.layereditlink_wrap').show();
							}
							$.each(layers,function(index, value) {
								$('#multilayeredit').append('<a id="layereditlink-href" href="' + admin_url + 'admin.php?page=leafletmapsmarker_layer&id=' + value + '">'  + ' <span id="layereditlink-id">' + value + '</span></a>');
								if (index != layers_length - 1) {
									$('#multilayeredit').append(', ');
								}
							}); 
						} else {
							$('#layereditlink').hide();
						}
						if ($('#markername').val() === '') {
							$('#lmm-panel-text').html('&nbsp;');
						}
					}
				} else if ($('#action-marker-add-edit').val() === 'edit') {
					if (results['status-class'] === 'updated') {
						$('#marker-heading').html(results['markername']+' (ID '+results['markerid']+')');
						if (results['layerid'] != '0') {
							$('#layereditlink').show();
							$('#layereditlink-href').hide();
							$('#multilayeredit').html('');

							var layers = results['layerid'].split(',');
							var layers_length = layers.length;
							if(layers_length > 0){
								$('.layereditlink_wrap').show();
							}
							$.each(layers,function(index, value) {
								$('#multilayeredit').append('<a id="layereditlink-href" href="' + admin_url + 'admin.php?page=leafletmapsmarker_layer&id=' + value + '">'  + ' <span id="layereditlink-id">' + value + '</span></a>');
								if (index != layers_length - 1) {
									$('#multilayeredit').append(', ');
								}
							}); 
						} else {
							$('#layereditlink').hide();
						}
						$('#updatedby').val(results['updatedby_saved']);
						$('#updatedon').val(results['updatedon_saved']);
						$('#audit_visibility').show();
						$('#updatedby_next').val(results['updatedby_next']);
						$('#updatedon_next').val(results['updatedon_next']);
					}
				}
			});
			return false;
		});

		//info: marker delete
		$('#delete_button_top, #delete_button_bottom').click(function(e) {
			if (confirm(lmm_ajax_vars.lmm_ajax_confirm_delete)) {
				$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').hide();
				$('#lmm_ajax_loading_top, #lmm_ajax_loading_bottom').show();
				$('#submit_top, #duplicate_button_top, #delete_button_top, #submit_bottom, #duplicate_button_bottom, #delete_button_bottom').attr('disabled', true);

				data = {
					action: 'mapsmarker_ajax_actions',
					lmm_ajax_subaction: 'marker-delete',
					lmm_ajax_nonce: lmm_ajax_vars.lmm_ajax_nonce,
					id: $('#id').val()
				};

				$.post(ajaxurl, data, function (response) {
					var results = JSON.parse(response);

					$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').attr('class',results['status-class']);
					$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').html(results['status-text']);
					$('#lmm_ajax_loading_top, #lmm_ajax_loading_bottom').hide();
					$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').show();
					$('#submit_top, #duplicate_button_top, #delete_button_top, #submit_bottom, #duplicate_button_bottom, #delete_button_bottom').attr('disabled', false);

					if (results['status-class'] === 'updated') {
						if (history.pushState) { //info: not supported in IE8+9 
							window.history.pushState(null, null, 'admin.php?page=leafletmapsmarker_marker');
						}
						$('#div-marker-editor-hide-on-ajax-delete').hide();
						$('#duplicate_span_top, #delete_span_top, #duplicate_span_bottom, #delete_span_bottom').hide();
					}
				});
				return false;
			}
			return false;
		});

		//info: marker duplicate
		$('#duplicate_button_top, #duplicate_button_bottom').click(function(e) {
			$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').hide();
			$('#lmm_ajax_loading_top, #lmm_ajax_loading_bottom').show();
			$('#submit_top, #duplicate_button_top, #delete_button_top, #submit_bottom, #duplicate_button_bottom, #delete_button_bottom').attr('disabled', true);

			data = {
				action: 'mapsmarker_ajax_actions',
				lmm_ajax_subaction: 'marker-duplicate',
				lmm_ajax_nonce: lmm_ajax_vars.lmm_ajax_nonce,
				id: $('#id').val()
			};

			$.post(ajaxurl, data, function (response) {
				var results = JSON.parse(response);

				$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').attr('class',results['status-class']);
				$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').html(results['status-text']);
				$('#lmm_ajax_loading_top, #lmm_ajax_loading_bottom').hide();
				$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').show();
				$('#submit_top, #duplicate_button_top, #delete_button_top, #submit_bottom, #duplicate_button_bottom, #delete_button_bottom').attr('disabled', false);

				if (results['status-class'] === 'updated') {
					if (history.pushState) { //info: not supported in IE8+9 
						window.history.pushState(null, null, 'admin.php?page=leafletmapsmarker_marker&id='+results['newmarkerid']);
					}
					$('#marker-heading').html(results['markername']+' (ID '+results['newmarkerid']+')');
					$('#id').val(results['newmarkerid']);
					$('#shortcode').val('['+shortcode+' marker="'+results['newmarkerid']+'"]');
					$('#shortcode-link-kml, #panel-link-kml').attr('href', leaflet_plugin_url+'leaflet-kml.php?marker='+results['newmarkerid']+'&name='+lmm_ajax_vars.lmm_ajax_misc_kml);
					$('#shortcode-link-fullscreen, #panel-link-fullscreen').attr('href', leaflet_plugin_url+'leaflet-fullscreen.php?marker='+results['newmarkerid']);
					$('#shortcode-link-qr, #panel-link-qr').attr('href', leaflet_plugin_url+'leaflet-qr.php?marker='+results['newmarkerid']);
					$('#shortcode-link-geojson, #panel-link-geojson').attr('href', leaflet_plugin_url+'leaflet-geojson.php?marker='+results['newmarkerid']+'&callback=jsonp&full=yes&full_icon_url=yes');
					$('#shortcode-link-georss, #panel-link-georss').attr('href', leaflet_plugin_url+'leaflet-georss.php?marker='+results['newmarkerid']);
					$('#shortcode-link-wikitude, #panel-link-wikitude').attr('href', leaflet_plugin_url+'leaflet-wikitude.php?marker='+results['newmarkerid']);
				}
			});
			return false;
		});

		//info: marker editor switch link 1/2
		$('#switch-link-visible').click(function(e) {
			$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').hide();
			$('#lmm_ajax_loading_top, #lmm_ajax_loading_bottom').show();
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
			$('#toggle-popup-directions-settings').toggle();
			$('#toggle-coordinates').toggle();
			$('#toogle-global-maximum-zoom-level').toggle();
			$('#toggle-controlbox-panel-kmltimestamp-backlinks-minimaps').toggle();
			$('#mapiconscollection').toggle();
			$('#popup-image-css-info').toggle();
			$('#toogle-icons-simplified').toggle();
			$('#toogle-icons-advanced').toggle();
			$('#toggle-advanced-settings').toggle();
			$('#toggle-audit').toggle();

			data = {
				action: 'mapsmarker_ajax_actions',
				lmm_ajax_subaction: 'editor-switchlink',
				lmm_ajax_nonce: lmm_ajax_vars.lmm_ajax_nonce,
				active_editor: $('#active_editor').val()
			};

			$.post(ajaxurl, data, function (response) {
				var results = JSON.parse(response);
				$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').attr('class',results['status-class']);
				$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').html(results['status-text']);
				$('#lmm_ajax_loading_top, #lmm_ajax_loading_bottom').hide();
				$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').show();
			});
			return false;
		});

		//info: marker editor switch link 2/2
		$('#switch-link-hidden').click(function(e) {
			$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').hide();
			$('#lmm_ajax_loading_top, #lmm_ajax_loading_bottom').show();
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
			$('#toggle-popup-directions-settings').toggle();
			$('#toggle-coordinates').toggle();
			$('#toogle-global-maximum-zoom-level').toggle();
			$('#toggle-controlbox-panel-kmltimestamp-backlinks-minimaps').toggle();
			$('#mapiconscollection').toggle();
			$('#popup-image-css-info').toggle();
			$('#toogle-icons-simplified').toggle();
			$('#toogle-icons-advanced').toggle();
			$('#toggle-advanced-settings').toggle();
			$('#toggle-audit').toggle();

			data = {
				action: 'mapsmarker_ajax_actions',
				lmm_ajax_subaction: 'editor-switchlink',
				lmm_ajax_nonce: lmm_ajax_vars.lmm_ajax_nonce,
				active_editor: $('#active_editor').val()
			};

			$.post(ajaxurl, data, function (response) {
				var results = JSON.parse(response);
				$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').attr('class',results['status-class']);
				$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').html(results['status-text']);
				$('#lmm_ajax_loading_top, #lmm_ajax_loading_bottom').hide();
				$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').show();
			});
			return false;
		});
		
		//info: add new marker actions
		$('.menu-top.toplevel_page_leafletmapsmarker_markers.menu-top-last ul.wp-submenu.wp-submenu-wrap li.current a.current, #lmm-header-button2, #wp-admin-bar-lmm-add-marker').click(function(e) {
			if (history.pushState) { //info: not supported in IE8+9 
				window.history.pushState(null, null, 'admin.php?page=leafletmapsmarker_marker');
			}
			if ($('#lmm-header-button2').hasClass('button-secondary')) {
				$('#lmm-header-button2').removeClass('button-secondary lmm-nav-secondary');
				$('#lmm-header-button2').addClass('button-primary lmm-nav-primary');
			}
			$('#lmm_ajax_results_top, #lmm_ajax_results_bottom').hide();
			$('#div-marker-editor-hide-on-ajax-delete').show();
			$('#marker-heading').html($('#defaults_texts_add_new_marker').val());
			$('#submit_top, #submit_bottom').val($('#defaults_texts_publish').val());
			$('#duplicate_span_top, #delete_span_top, #duplicate_span_bottom, #delete_span_bottom').hide();
			$('#action-marker-add-edit').val('add');
			$('#tr-shortcode').hide();
			//info: set form values
			$('#id').val('');
			$('#markername').val('');
			$('#lmm-panel-text').html($('#defaults_texts_panel_text').val());
			//info: unresolved $('#basemap').val($('#defaults_basemap').val());
			if($('#defaults_layer').val() == "0"){
				$('#layer').select2('val', '');	
			}else{
				var defaults_layers =  $('#defaults_layer').val();
				defaults_layers = defaults_layers.split(',');
				$('#layer').select2('val', defaults_layers);	
			}
			$('#layereditlink').hide();
			$('.layereditlink_wrap').hide();
			$('#layeraddlink').show();
			$('#lat').val($('#defaults_lat').val());
			$('#lon').val($('#defaults_lon').val());
			$('.div-marker-icon').css('background','none');
			$('.div-marker-icon').css('opacity','0.4');
			$('.div-marker-icon-default').css('opacity','1');
			$('.div-marker-icon-default').css('background','#5e5d5d');
			$('#icon_hidden').val($('#defaults_icon').val());
			if ($('#wp-popuptext-wrap').hasClass('tmce-active')) {
				tinymce.get('popuptext').setContent('');
			} else {
				$('#popuptext').val('');
			}
 			$('html, body').animate({ scrollTop: 0 }, 'fast'); //info: workaround for tinyMCE focus
			$('#selectlayer-popuptext-hidden').val('');
			$('#zoom').val($('#defaults_zoom').val());
			if ($('#defaults_openpopup').val() === '0') { $('input:checkbox[name=openpopup]').attr('checked',false); } else { $('input:checkbox[name=openpopup]').attr('checked',true); }
			$('#mapwidth').val($('#defaults_mapwidth').val());
			if ($('#defaults_mapwidthunit').val() === 'px') { $('input:radio[id=mapwidthunit_px]')[0].checked = true; } else { $('input:radio[id=mapwidthunit_percent]')[0].checked = true; }
			$('#mapheight').val($('#defaults_mapheight').val());
			if ($('#defaults_panel').val() === '0') { $('input:checkbox[name=panel]').attr('checked',false); } else { $('input:checkbox[name=panel]').attr('checked',true); }
			$('#createdby').val($('#updatedby_next').val());
			$('#createdon').val($('#updatedon_next').val());
			$('#audit_visibility').hide();
			$('#updatedby').val($('#updatedby_next').val());
			$('#updatedon').val($('#updatedon_next').val());
			if ($('#defaults_controlbox').val() === '0') { 
				$('input:radio[id=controlbox_hidden]')[0].checked = true;
			} else if ($('#defaults_controlbox').val() === '1') {
				$('input:radio[id=controlbox_collapsed]')[0].checked = true;
			} else if ($('#defaults_controlbox').val() === '2') { 
				$('input:radio[id=controlbox_expanded]')[0].checked = true; 
			}
			$('#overlays_custom').val($('#defaults_overlays_custom').val());
			$('#overlays_custom2').val($('#defaults_overlays_custom2').val());
			$('#overlays_custom3').val($('#defaults_overlays_custom3').val());
			$('#overlays_custom4').val($('#defaults_overlays_custom4').val());
			if ($('#defaults_wms').val() === '0') { $('input:checkbox[name=wms]').attr('checked',false); } else { $('input:checkbox[name=wms]').attr('checked',true); }
			if ($('#defaults_wms2').val() === '0') { $('input:checkbox[name=wms2]').attr('checked',false); } else { $('input:checkbox[name=wms2]').attr('checked',true); }
			if ($('#defaults_wms3').val() === '0') { $('input:checkbox[name=wms3]').attr('checked',false); } else { $('input:checkbox[name=wms3]').attr('checked',true); }
			if ($('#defaults_wms4').val() === '0') { $('input:checkbox[name=wms4]').attr('checked',false); } else { $('input:checkbox[name=wms4]').attr('checked',true); }
			if ($('#defaults_wms5').val() === '0') { $('input:checkbox[name=wms5]').attr('checked',false); } else { $('input:checkbox[name=wms5]').attr('checked',true); }
			if ($('#defaults_wms6').val() === '0') { $('input:checkbox[name=wms6]').attr('checked',false); } else { $('input:checkbox[name=wms6]').attr('checked',true); }
			if ($('#defaults_wms7').val() === '0') { $('input:checkbox[name=wms7]').attr('checked',false); } else { $('input:checkbox[name=wms7]').attr('checked',true); }
			if ($('#defaults_wms8').val() === '0') { $('input:checkbox[name=wms8]').attr('checked',false); } else { $('input:checkbox[name=wms8]').attr('checked',true); }
			if ($('#defaults_wms9').val() === '0') { $('input:checkbox[name=wms9]').attr('checked',false); } else { $('input:checkbox[name=wms9]').attr('checked',true); }
			if ($('#defaults_wms10').val() === '0') { $('input:checkbox[name=wms10]').attr('checked',false); } else { $('input:checkbox[name=wms10]').attr('checked',true); }
			$('#address').val('');
			$('#popup-address').html($('#defaults_texts_directions_link_new_marker').val());
			marker.setPopupContent($('#defaults_texts_directions_link_new_marker').val());
			if ($('#gpx_url').val() !== '') { 
				$('#gpx_url').val('');
				//info: workaround as removeLayer did not work
				$('.leaflet-overlay-pane').html('');
				$('.lmm_gpx_icons').hide();
			}
			$('#gpx_fitbounds_link').hide();
			$('#gpx-panel-selectlayer').hide();
			$('input:checkbox[name=gpx_panel]').attr('checked',false);
			//info: reset leaflet map; do not change to default basemap due to unresolved issues :-/
			$('#lmm').css('width',$('#defaults_mapwidth').val()+$('#defaults_mapwidthunit').val());
			$('#selectlayer').css('height',$('#defaults_mapheight').val());
			selectlayer.invalidateSize();
			selectlayer.setView(new L.LatLng($('#defaults_lat').val(), $('#defaults_lon').val()), $('#defaults_zoom').val());
			if ($('#defaults_controlbox').val() === '0') { 
				$('.leaflet-control-layers').hide();
			} else if ($('#defaults_controlbox').val() === '1') { 
				$('.leaflet-control-layers').show();
				layersControl._collapse();
			} else if ($('#defaults_controlbox').val() === '2') { 
				$('.leaflet-control-layers').show();
				layersControl._expand();
			}
			if ($('#defaults_panel').val() === '0') { $('#lmm-panel').css('display','none'); } else { $('#lmm-panel').css('display','block'); }
			if ($('#defaults_openpopup').val() === '0') { marker.closePopup(); } else { marker.openPopup(); }
			//info: reset wms
			if (selectlayer.hasLayer(wms)) { selectlayer.removeLayer(wms); }
			if (selectlayer.hasLayer(wms2)) { selectlayer.removeLayer(wms2); }
			if (selectlayer.hasLayer(wms3)) { selectlayer.removeLayer(wms3); }
			if (selectlayer.hasLayer(wms4)) { selectlayer.removeLayer(wms4); }
			if (selectlayer.hasLayer(wms5)) { selectlayer.removeLayer(wms5); }
			if (selectlayer.hasLayer(wms6)) { selectlayer.removeLayer(wms6); }
			if (selectlayer.hasLayer(wms7)) { selectlayer.removeLayer(wms7); }
			if (selectlayer.hasLayer(wms8)) { selectlayer.removeLayer(wms8); }
			if (selectlayer.hasLayer(wms9)) { selectlayer.removeLayer(wms9); }
			if (selectlayer.hasLayer(wms10)) { selectlayer.removeLayer(wms10); }
			$('#kml_timestamp').val('');
			//info: set default icon
			$('.div-marker-icon').css('background','none');
			$('.div-marker-icon').css('opacity','0.4');
			marker.setIcon(new L.Icon({iconUrl: $('#defaults_marker_icon_url').val(),iconSize: [$('#defaults_marker_icon_iconsize_x').val()+','+$('#defaults_marker_icon_iconsize_y').val()],iconAnchor: [$('#defaults_marker_icon_iconanchor_x').val()+','+$('#defaults_marker_icon_iconanchor_y').val()],popupAnchor: [$('#defaults_marker_icon_popupanchor_x').val()+','+$('#defaults_marker_icon_popupanchor_y').val()],shadowUrl: $('#defaults_marker_icon_shadow_url').val(),shadowSize: [$('#defaults_marker_icon_shadowsize_x').val()+','+$('#defaults_marker_icon_shadowsize_y').val()],shadowAnchor: [$('#defaults_marker_icon_shadowanchor_x').val()+','+$('#defaults_marker_icon_shadowanchor_y').val()],className: $('#defaults_icon_className').val()}))
			var icon_opacity_selector = $('#defaults_icon_opacity_selector').val();
			$(icon_opacity_selector).css('opacity','1');
			$(icon_opacity_selector).css('background','#5e5d5d');
			//info: reset panel api links
			$('#popup-directions, #panel-link-directions').attr('href', 'http://maps.google.com/maps?daddr='+$('#defaults_lat').val()+','+$('#defaults_lon').val()+'&t=m&layer=1&doflg=ptk&om=0');
			$('#panel-link-kml').attr('href', leaflet_plugin_url+'leaflet-kml.php?marker=&name='+lmm_ajax_vars.lmm_ajax_misc_kml);
			$('#panel-link-fullscreen').attr('href', leaflet_plugin_url+'leaflet-fullscreen.php?marker=');
			$('#panel-link-qr').attr('href', leaflet_plugin_url+'leaflet-qr.php?marker=');
			$('#panel-link-geojson').attr('href', leaflet_plugin_url+'leaflet-geojson.php?marker=&callback=jsonp&full=yes&full_icon_url=yes');
			$('#panel-link-georss').attr('href', leaflet_plugin_url+'leaflet-georss.php?marker=');
			$('#panel-link-wikitude').attr('href', leaflet_plugin_url+'leaflet-wikitude.php?marker=');
		});
	//info: js for layer edit page
	} /* else if (current_page === 'leafletmapsmarker_layer') {

	} */
});