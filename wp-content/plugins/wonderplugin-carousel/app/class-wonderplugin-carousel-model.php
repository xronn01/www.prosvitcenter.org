<?php 

require_once 'wonderplugin-carousel-functions.php';

class WonderPlugin_Carousel_Model {

	private $controller;
	
	function __construct($controller) {
		
		$this->controller = $controller;
	}
	
	function get_upload_path() {
		
		$uploads = wp_upload_dir();
		return $uploads['basedir'] . '/wonderplugin-carousel/';
	}
	
	function get_upload_url() {
	
		$uploads = wp_upload_dir();
		return $uploads['baseurl'] . '/wonderplugin-carousel/';
	}
	
	function generate_body_code($id, $has_wrapper) {
		
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_carousel";
		
		if ( !$this->is_db_table_exists() )
		{
			return '<p>The specified carousel does not exist.</p>';
		}
		
		$ret = "";
		$item_row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id) );
		if ($item_row != null)
		{
			$data = str_replace('\\\"', '"', $item_row->data);
			$data = str_replace("\\\'", "'", $data);
			$data = str_replace("\\\\", "\\", $data);
			
			$data = json_decode(trim($data));
			
			foreach($data as &$value)
			{
				if ( is_string($value) )
					$value = wp_kses_post($value);
			}
			
			if (isset($data->customcss) && strlen($data->customcss) > 0)
			{
				$customcss = str_replace("\r", " ", $data->customcss);
				$customcss = str_replace("\n", " ", $customcss);
				$customcss = str_replace("CAROUSELID", $id, $customcss);
				$ret .= '<style type="text/css">' . $customcss . '</style>';
			}
			
			if (isset($data->skincss) && strlen($data->skincss) > 0)
			{
				$skincss = str_replace("\r", " ", $data->skincss);
				$skincss = str_replace("\n", " ", $skincss);
				$skincss = str_replace('#amazingcarousel-CAROUSELID',  '#wonderplugincarousel-' . $id, $skincss);
				$ret .= '<style type="text/css">' . $skincss . '</style>';
			}
			
			if ($has_wrapper)
				$ret .= '<div style="max-width:' . $data->width * $data->visibleitems . 'px;margin:0 auto;padding:0 60px;">';
			
			// div data tag
			$ret .= '<div class="wonderplugincarousel" id="wonderplugincarousel-' . $id . '" data-carouselid="' . $id . '" data-width="' . $data->width . '" data-height="' . $data->height . '" data-skin="' . $data->skin . '"';
			
			if (isset($data->dataoptions) && strlen($data->dataoptions) > 0)
			{
				$ret .= ' ' . stripslashes($data->dataoptions);
			}
			
			$boolOptions = array('autoplay', 'random', 'autoplayvideo', 'circular', 'pauseonmouseover', 'continuous', 'responsive', 'showhoveroverlay', 'showhoveroverlayalways', 'lightboxresponsive', 'lightboxshownavigation', 'lightboxnogroup', 'lightboxshowtitle', 'lightboxshowdescription', 'usescreenquery', 'donotinit', 'addinitscript');
			foreach ( $boolOptions as $key )
			{
				if (isset($data->{$key}) )
					$ret .= ' data-' . $key . '="' . ((strtolower($data->{$key}) === 'true') ? 'true': 'false') .'"';
			}
			
			$valOptions = array('rownumber', 'visibleitems', 'arrowstyle', 'arrowimage', 'arrowwidth', 'arrowheight', 'navstyle', 'navimage', 'navwidth', 'navheight', 'navspacing', 'hoveroverlayimage', 'lightboxthumbwidth', 'lightboxthumbheight', 'lightboxthumbtopmargin', 'lightboxthumbbottommargin', 'lightboxbarheight', 'lightboxtitlebottomcss', 'lightboxdescriptionbottomcss');
			foreach ( $valOptions as $key )
			{
				if (isset($data->{$key}) )
					$ret .= ' data-' . $key . '="' . $data->{$key} . '"';
			}
				
			// screen query
			if (isset($data->screenquery))
				$ret .= " data-screenquery='" . preg_replace('/\s+/', ' ', trim($data->screenquery))   . "'";
			else
				$ret .= " data-screenquery='{ \"mobile\": { \"screenwidth\": 600, \"visibleitems\": 1 } }'";
			
			$ret .= ' data-jsfolder="' . WONDERPLUGIN_CAROUSEL_URL . 'engine/"'; 
			
			if ($data->direction == 'vertical')
				$totalwidth = $data->width;
			else
				$totalwidth = $data->width * $data->visibleitems;
				
			if (strtolower($data->responsive) === 'true')
				$ret .= ' style="display:none;position:relative;margin:0 auto;width:100%;max-width:' . $totalwidth . 'px;"';
			else 
				$ret .= ' style="display:none;position:relative;margin:0 auto;width:' . $totalwidth . 'px;"';
			
			$ret .= ' >';
			
			if ( !isset($data->rownumber) || !is_int($data->rownumber) || $data->rownumber < 1)
				$data->rownumber = 1;
			
			if (isset($data->slides) && count($data->slides) > 0)
			{
				$ret .= '<div class="amazingcarousel-list-container" style="overflow:hidden;">';
				$ret .= '<ul class="amazingcarousel-list">';
				
				foreach ($data->slides as $index => $slide)
				{		
					foreach($slide as &$value)
					{
						if ( is_string($value) )
							$value = wp_kses_post($value);
					}
					
					$boolOptions = array('lightbox', 'displaythumbnail', 'lightboxsize', 'weblinklightbox');
					foreach ( $boolOptions as $key )
					{
						if (isset($slide->{$key}) )
							$slide->{$key} = ((strtolower($slide->{$key}) === 'true') ? true: false);
					}
					
					if ($index == 0)
						$ret .= '<li class="amazingcarousel-item">';
					else if ($index % $data->rownumber == 0)
						$ret .= '</li><li class="amazingcarousel-item">';					
					
					$ret .= '<div class="amazingcarousel-item-container">';
					
					$image_code = '';
					if ( isset($slide->lightbox) && $slide->lightbox )
					{
						$image_code .= '<a href="';
						if ($slide->type == 0)
						{
							$image_code .= $slide->image;
						}
						else if ($slide->type == 1)
						{
							$image_code .= $slide->mp4;
							if ($slide->webm)
								$image_code .= '" data-webm="' . $slide->webm;
						}
						else if ($slide->type == 2 || $slide->type == 3)
						{
							$image_code .= $slide->video;
						}
					
						if ($slide->title && strlen($slide->title) > 0)
							$image_code .= '" title="' . str_replace("\"", "&quot;", $slide->title);
					
						if ($slide->description && strlen($slide->description) > 0)
							$image_code .= '" data-description="' . str_replace("\"", "&quot;", $slide->description); 
						
						if ($slide->lightboxsize)
							$image_code .= '" data-width="' .  $slide->lightboxwidth . '" data-height="' .  $slide->lightboxheight;
						
						$image_code .= '" data-thumbnail="' . $slide->thumbnail;
						
						$image_code .= '" class="wondercarousellightbox wondercarousellightbox-' . $id . '"';
						if ( !isset($data->lightboxnogroup) || strtolower($data->lightboxnogroup) !== 'true' )
							$image_code .= ' data-group="wondercarousellightbox-' . $id . '"';
						$image_code .= '>';
					}
					else if ($slide->weblink && strlen($slide->weblink) > 0)
					{
						$image_code .= '<a href="' . $slide->weblink . '"';
						if ($slide->linktarget && strlen($slide->linktarget) > 0)
							$image_code .= ' target="' . $slide->linktarget . '"';
						if ( isset($slide->weblinklightbox) && $slide->weblinklightbox )
						{
							$image_code .= '" class="wondercarousellightbox wondercarousellightbox-' . $id . '"';
							if ( !isset($data->lightboxnogroup) || strtolower($data->lightboxnogroup) !== 'true' )
								$image_code .= ' data-group="wondercarousellightbox-' . $id . '"';
							if ($slide->lightboxsize)
								$image_code .= ' data-width="' .  $slide->lightboxwidth . '" data-height="' .  $slide->lightboxheight . '"';
						}
						$image_code .= '>';
					}
						
					if ( isset($slide->displaythumbnail) && $slide->displaythumbnail )
						$image_code .= '<img src="' . $slide->thumbnail . '"';
					else
						$image_code .= '<img src="' . $slide->image . '"';
					$image_code .= ' alt="' . str_replace("\"", "&quot;", $slide->title) . '"';
					$image_code .= ' data-description="' . str_replace("\"", "&quot;", $slide->description) . '"';
					if (!$slide->lightbox)
					{
						if ($slide->type == 1)
						{
							$image_code .= ' data-video="' . $slide->mp4 . '"';
							if ($slide->webm)
								$image_code .= ' data-videowebm="' . $slide->webm . '"';
						}
						else if ($slide->type == 2 || $slide->type == 3)
						{
							$image_code .= ' data-video="' . $slide->video . '"';
						}
					}
					$image_code .= ' />';
					
					if ($slide->lightbox || (!$slide->lightbox && $slide->weblink && strlen($slide->weblink) > 0))
					{
						$image_code .= '</a>';
					}
					
					$title_code = '';
					if ($slide->title && strlen($slide->title) > 0)
						$title_code = $slide->title;
					 
					$description_code = '';
					if ($slide->description && strlen($slide->description) > 0)
						$description_code = $slide->description;
					
					$skin_template = str_replace('&amp;',  '&', $data->skintemplate);
					$skin_template = str_replace('&lt;',  '<', $skin_template);
					$skin_template = str_replace('&gt;',  '>', $skin_template);
					
					$skin_template = str_replace('__IMAGE__',  $image_code, $skin_template);
					$skin_template = str_replace('__TITLE__',  $title_code, $skin_template);
					$skin_template = str_replace('__DESCRIPTION__',  $description_code, $skin_template);
					
					if ($slide->weblink && strlen($slide->weblink) > 0)
					{
						$skin_template = str_replace('__HREF__',  $slide->weblink, $skin_template);
						if ($slide->linktarget)
							$skin_template = str_replace('__TARGET__',  $slide->linktarget, $skin_template);
					}
					
					$ret .= $skin_template;	
				
					$ret .= '</div>';
				}
				
				$ret .= '</li>';
				
				$ret .= '</ul>';
				$ret .= '<div class="amazingcarousel-prev"></div><div class="amazingcarousel-next"></div>';
				$ret .= '</div>';
				$ret .= '<div class="amazingcarousel-nav"></div>';
				
			}
			if ('F' == 'F')
				$ret .= '<div class="wonderplugin-engine"><a href="http://www.wonderplugin.com/wordpress-carousel/" title="'. get_option('wonderplugin-carousel-engine')  .'">' . get_option('wonderplugin-carousel-engine') . '</a></div>';
			$ret .= '</div>';
			
			if ($has_wrapper)
				$ret .= '</div>';
			
			if (isset($data->addinitscript) && strtolower($data->addinitscript) === 'true')
			{
				$ret .= '<script>jQuery(document).ready(function(){jQuery(".wonderplugincarousel").wonderplugincarouselslider({forceinit:true});});</script>';
			}
		}
		else
		{
			$ret = '<p>The specified carousel id does not exist.</p>';
		}
		return $ret;
	}
	
	function delete_item($id) {
		
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_carousel";
		
		$ret = $wpdb->query( $wpdb->prepare(
				"
				DELETE FROM $table_name WHERE id=%s
				",
				$id
		) );
		
		return $ret;
	}
	
	function clone_item($id) {
	
		global $wpdb, $user_ID;
		$table_name = $wpdb->prefix . "wonderplugin_carousel";
		
		$cloned_id = -1;
		
		$item_row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id) );
		if ($item_row != null)
		{
			$time = current_time('mysql');
			$authorid = $user_ID;
			
			$ret = $wpdb->query( $wpdb->prepare(
					"
					INSERT INTO $table_name (name, data, time, authorid)
					VALUES (%s, %s, %s, %s)
					",
					$item_row->name . " Copy",
					$item_row->data,
					$time,
					$authorid
			) );
				
			if ($ret)
				$cloned_id = $wpdb->insert_id;
		}
	
		return $cloned_id;
	}
	
	function is_db_table_exists() {
	
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_carousel";
	
		return ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name );
	}
	
	function is_id_exist($id)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_carousel";
	
		$carousel_row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id) );
		return ($carousel_row != null);
	}
	
	function create_db_table() {
	
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_carousel";
		
		$charset = '';
		if ( !empty($wpdb -> charset) )
			$charset = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( !empty($wpdb -> collate) )
			$charset .= " COLLATE $wpdb->collate";
	
		$sql = "CREATE TABLE $table_name (
		id INT(11) NOT NULL AUTO_INCREMENT,
		name tinytext DEFAULT '' NOT NULL,
		data MEDIUMTEXT DEFAULT '' NOT NULL,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		authorid tinytext NOT NULL,
		PRIMARY KEY  (id)
		) $charset;";
			
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	function save_item($item) {
		
		global $wpdb, $user_ID;
		
		if ( !$this->is_db_table_exists() )
		{
			$this->create_db_table();
				
			$create_error = $wpdb->last_error;
			if ( !$this->is_db_table_exists() )
			{				
				return array(
						"success" => false,
						"id" => -1,
						"message" => $create_error
				);
			}
		}	
		
		$table_name = $wpdb->prefix . "wonderplugin_carousel";
		
		$id = $item["id"];
		$name = $item["name"];
		
		unset($item["id"]);
		$data = json_encode($item);
		
		if ( empty($data) )
		{
			$json_error = "json_encode error";
			if ( function_exists('json_last_error_msg') )
				$json_error .= ' - ' . json_last_error_msg();
			else if ( function_exists('json_last_error') )
				$json_error .= 'code - ' . json_last_error();
		
			return array(
					"success" => false,
					"id" => -1,
					"message" => $json_error
			);
		}
		
		$time = current_time('mysql');
		$authorid = $user_ID;
		
		if ( ($id > 0) && $this->is_id_exist($id) )
		{
			$ret = $wpdb->query( $wpdb->prepare(
					"
					UPDATE $table_name
					SET name=%s, data=%s, time=%s, authorid=%s
					WHERE id=%d
					",
					$name,
					$data,
					$time,
					$authorid,
					$id
			) );
			
			if (!$ret)
			{
				return array(
						"success" => false,
						"id" => $id, 
						"message" => "UPDATE - ". $wpdb->last_error
					);
			}
		}
		else
		{
			$ret = $wpdb->query( $wpdb->prepare(
					"
					INSERT INTO $table_name (name, data, time, authorid)
					VALUES (%s, %s, %s, %s)
					",
					$name,
					$data,
					$time,
					$authorid
			) );
			
			if (!$ret)
			{
				return array(
						"success" => false,
						"id" => -1,
						"message" => "INSERT - " . $wpdb->last_error
				);
			}
			
			$id = $wpdb->insert_id;
		}
		
		return array(
				"success" => true,
				"id" => intval($id),
				"message" => "Carousel published!"
		);
	}
	
	function get_list_data() {
		
		if ( !$this->is_db_table_exists() )
			$this->create_db_table();
		
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_carousel";
		
		$rows = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A);
		
		$ret = array();
		
		if ( $rows )
		{
			foreach ( $rows as $row )
			{
				$ret[] = array(
							"id" => $row['id'],
							'name' => $row['name'],
							'data' => $row['data'],
							'time' => $row['time'],
							'author' => $row['authorid']
						);
			}
		}
	
		return $ret;
	}
	
	function get_item_data($id)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "wonderplugin_carousel";
	
		$ret = "";
		$item_row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id) );
		if ($item_row != null)
		{
			$ret = $item_row->data;
		}

		return $ret;
	}
	
	
	function get_settings() {
	
		$userrole = get_option( 'wonderplugin_carousel_userrole' );
		if ( $userrole == false )
		{
			update_option( 'wonderplugin_carousel_userrole', 'manage_options' );
			$userrole = 'manage_options';
		}
		
		$thumbnailsize = get_option( 'wonderplugin_carousel_thumbnailsize' );
		if ( $thumbnailsize == false )
		{
			update_option( 'wonderplugin_carousel_thumbnailsize', 'medium' );
			$thumbnailsize = 'medium';
		}
		
		$keepdata = get_option( 'wonderplugin_carousel_keepdata', 1 );
		
		$disableupdate = get_option( 'wonderplugin_carousel_disableupdate', 0 );
		
		$supportwidget = get_option( 'wonderplugin_carousel_supportwidget', 1 );
		
		$addjstofooter = get_option( 'wonderplugin_carousel_addjstofooter', 0 );
		
		$settings = array(
			"userrole" => $userrole,
			"thumbnailsize" => $thumbnailsize,
			"keepdata" => $keepdata,
			"disableupdate" => $disableupdate,
			"supportwidget" => $supportwidget,
			"addjstofooter" => $addjstofooter
		);
		
		return $settings;
		
	}
	
	function save_settings($options) {
	
		if (!isset($options) || !isset($options['userrole']))
			$userrole = 'manage_options';
		else if ( $options['userrole'] == "Editor")
			$userrole = 'moderate_comments';
		else if ( $options['userrole'] == "Author")
			$userrole = 'upload_files';
		else
			$userrole = 'manage_options';
		update_option( 'wonderplugin_carousel_userrole', $userrole );
		
		if (isset($options) && isset($options['thumbnailsize']))
			$thumbnailsize = $options['thumbnailsize'];
		else
			$thumbnailsize = 'medium';
		update_option( 'wonderplugin_carousel_thumbnailsize', $thumbnailsize );
		
		if (!isset($options) || !isset($options['keepdata']))
			$keepdata = 0;
		else
			$keepdata = 1;
		update_option( 'wonderplugin_carousel_keepdata', $keepdata );
		
		if (!isset($options) || !isset($options['disableupdate']))
			$disableupdate = 0;
		else
			$disableupdate = 1;
		update_option( 'wonderplugin_carousel_disableupdate', $disableupdate );
		
		if (!isset($options) || !isset($options['supportwidget']))
			$supportwidget = 0;
		else
			$supportwidget = 1;
		update_option( 'wonderplugin_carousel_supportwidget', $supportwidget );
		
		if (!isset($options) || !isset($options['addjstofooter']))
			$addjstofooter = 0;
		else
			$addjstofooter = 1;
		update_option( 'wonderplugin_carousel_addjstofooter', $addjstofooter );
	}
	
	function get_plugin_info() {
	
		$info = get_option('wonderplugin_carousel_information');
		if ($info === false)
			return false;
	
		return unserialize($info);
	}
	
	function save_plugin_info($info) {
	
		update_option( 'wonderplugin_carousel_information', serialize($info) );
	}
	
	function check_license($options) {
	
		$ret = array(
				"status" => "empty"
		);
	
		if ( !isset($options) || empty($options['wonderplugin-carousel-key']) )
		{
			return $ret;
		}
	
		$key = sanitize_text_field( $options['wonderplugin-carousel-key'] );
		if ( empty($key) )
			return $ret;
	
		$update_data = $this->controller->get_update_data('register', $key);
		if( $update_data === false )
		{
			$ret['status'] = 'timeout';
			return $ret;
		}
	
		if ( isset($update_data->key_status) )
			$ret['status'] = $update_data->key_status;
	
		return $ret;
	}
	
	function deregister_license($options) {
	
		$ret = array(
				"status" => "empty"
		);
	
		if ( !isset($options) || empty($options['wonderplugin-carousel-key']) )
			return $ret;
	
		$key = sanitize_text_field( $options['wonderplugin-carousel-key'] );
		if ( empty($key) )
			return $ret;
	
		$info = $this->get_plugin_info();
		$info->key = '';
		$info->key_status = 'empty';
		$info->key_expire = 0;
		$this->save_plugin_info($info);
	
		$update_data = $this->controller->get_update_data('deregister', $key);
		if ($update_data === false)
		{
			$ret['status'] = 'timeout';
			return $ret;
		}
	
		$ret['status'] = 'success';
	
		return $ret;
	}
}
