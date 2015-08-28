<?php 

require_once 'class-wonderplugin-carousel-list-table.php';
require_once 'class-wonderplugin-carousel-creator.php';

class WonderPlugin_Carousel_View {

	private $controller;
	private $list_table;
	private $creator;
	
	function __construct($controller) {
		
		$this->controller = $controller;
	}
	
	function add_metaboxes() {
		add_meta_box('overview_features', __('WonderPlugin Carousel Features', 'wonderplugin_carousel'), array($this, 'show_features'), 'wonderplugin_carousel_overview', 'features', '');
		add_meta_box('overview_upgrade', __('Upgrade to Commercial Version', 'wonderplugin_carousel'), array($this, 'show_upgrade_to_commercial'), 'wonderplugin_carousel_overview', 'upgrade', '');
		add_meta_box('overview_news', __('WonderPlugin News', 'wonderplugin_carousel'), array($this, 'show_news'), 'wonderplugin_carousel_overview', 'news', '');
		add_meta_box('overview_contact', __('Contact Us', 'wonderplugin_carousel'), array($this, 'show_contact'), 'wonderplugin_carousel_overview', 'contact', '');
	}
	
	function show_upgrade_to_commercial() {
		?>
		<ul class="wonderplugin-feature-list">
			<li>Use on commercial websites</li>
			<li>Remove the wonderplugin.com watermark</li>
			<li>Priority techincal support</li>
			<li><a href="http://www.wonderplugin.com/order/?product=carousel" target="_blank">Upgrade to Commercial Version</a></li>
		</ul>
		<?php
	}
	
	function show_news() {
		
		include_once( ABSPATH . WPINC . '/feed.php' );
		
		$rss = fetch_feed( 'http://www.wonderplugin.com/feed/' );
		
		$maxitems = 0;
		if ( ! is_wp_error( $rss ) )
		{
			$maxitems = $rss->get_item_quantity( 5 );
			$rss_items = $rss->get_items( 0, $maxitems );
		}
		?>
		
		<ul class="wonderplugin-feature-list">
		    <?php if ( $maxitems > 0 ) {
		        foreach ( $rss_items as $item )
		        {
		        	?>
		        	<li>
		                <a href="<?php echo esc_url( $item->get_permalink() ); ?>" target="_blank" 
		                    title="<?php printf( __( 'Posted %s', 'wonderplugin_carousel' ), $item->get_date('j F Y | g:i a') ); ?>">
		                    <?php echo esc_html( $item->get_title() ); ?>
		                </a>
		                <p><?php echo esc_html( $item->get_description() ); ?></p>
		            </li>
		        	<?php 
		        }
		    } ?>
		</ul>
		<?php
	}
	
	function show_features() {
		?>
		<ul class="wonderplugin-feature-list">
			<li>Support images, YouTube, Vimeo and MP4/WebM videos</li>
			<li>Works on mobile, tablets and all major web browsers, including iPhone, iPad, Android, Firefox, Safari, Chrome, Internet Explorer 7/8/9/10/11 and Opera</li>
			<li>Built-in lightbox effect</li>
			<li>Pre-defined professional skins</li>
			<li>Fully responsive</li>
			<li>Easy-to-use wizard style user interface</li>
			<li>Instantly preview</li>
			<li>Provide shortcode and PHP code to insert the carousel to pages, posts or templates</li>
		</ul>
		<?php
	}
	
	function show_contact() {
		?>
		<p>Technical support is available for Commercial Version users at support@wonderplugin.com. Please include your license information, WordPress version, link to your carousel, all related error messages in your email.</p> 
		<?php
	}
	
	function print_overview() {
		
		?>
		<div class="wrap">
		<div id="icon-wonderplugin-carousel" class="icon32"><br /></div>
		<div id="wondercarousellightbox_options" data-skinsfoldername=""  data-jsfolder="<?php echo WONDERPLUGIN_CAROUSEL_URL . 'engine/'; ?>" style="display:none;"></div>
			
		<h2><?php echo __( 'WonderPlugin Carousel', 'wonderplugin_carousel' ) . ( (WONDERPLUGIN_CAROUSEL_VERSION_TYPE == "C") ? " Commercial Version" : " Free Version") . " " . WONDERPLUGIN_CAROUSEL_VERSION; ?> </h2>
		 
		<div id="welcome-panel" class="welcome-panel">
			<div class="welcome-panel-content">
				<h3>WordPress Image and Video Carousel Plugin</h3>
				<div class="welcome-panel-column-container">
					<div class="welcome-panel-column">
						<h4>Get Started</h4>
						<a class="button button-primary button-hero" href="<?php echo admin_url('admin.php?page=wonderplugin_carousel_add_new'); ?>">Create A New Carousel</a>
					</div>
					<div class="welcome-panel-column welcome-panel-last">
						<h4>More Actions</h4>
						<ul>
							<li><a href="<?php echo admin_url('admin.php?page=wonderplugin_carousel_show_items'); ?>" class="welcome-icon welcome-widgets-menus">Manage Existing Carousels</a></li>
							<li><a href="http://www.wonderplugin.com/wordpress-carousel/help/" target="_blank" class="welcome-icon welcome-learn-more">Help Document</a></li>
							<?php  if (WONDERPLUGIN_CAROUSEL_VERSION_TYPE !== "C") { ?>
							<li><a href="http://www.wonderplugin.com/order/?product=carousel" target="_blank" class="welcome-icon welcome-view-site">Upgrade to Commercial Version</a></li>
							<?php } ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		
		<div id="dashboard-widgets-wrap">
			<div id="dashboard-widgets" class="metabox-holder columns-2">
	 
	                 <div class="postbox-container">
	                    <?php 
	                    do_meta_boxes( 'wonderplugin_carousel_overview', 'features', '' ); 
	                    do_meta_boxes( 'wonderplugin_carousel_overview', 'contact', '' ); 
	                    ?>
	                </div>
	 
	                <div class="postbox-container">
	                    <?php 
	                    if (WONDERPLUGIN_CAROUSEL_VERSION_TYPE != "C")
	                    	do_meta_boxes( 'wonderplugin_carousel_overview', 'upgrade', ''); 
	                    do_meta_boxes( 'wonderplugin_carousel_overview', 'news', ''); 
	                    ?>
	                </div>
	 
	        </div>
        </div>
            
		<?php
	}
	
	function print_edit_settings() {
		
		?>
		<div class="wrap">
		<div id="icon-wonderplugin-carousel" class="icon32"><br /></div>
			
		<h2><?php _e( 'Settings', 'wonderplugin_carousel' ); ?> </h2>
		<?php

		if ( isset($_POST['save-carousel-options']) && check_admin_referer('wonderplugin-carousel', 'wonderplugin-carousel-settings') )
		{		
			unset($_POST['save-carousel-options']);
			
			$this->controller->save_settings($_POST);
			
			echo '<div class="updated"><p>Settings saved.</p></div>';
		}
		
		$settings = $this->controller->get_settings();		
		$userrole = $settings['userrole'];
		$thumbnailsize = $settings['thumbnailsize'];
		$keepdata = $settings['keepdata'];
		$disableupdate = $settings['disableupdate'];
		$supportwidget = $settings['supportwidget'];
		$addjstofooter = $settings['addjstofooter'];
		
		?>
		
		<h3>This page is only available for users of Administrator role.</h3>
		
        <form method="post">
        
        <?php wp_nonce_field('wonderplugin-carousel', 'wonderplugin-carousel-settings'); ?>
        
        <table class="form-table">
        
        <tr valign="top">
			<th scope="row">Set minimum user role</th>
			<td>
				<select name="userrole">
				  <option value="Administrator" <?php echo ($userrole == 'manage_options') ? 'selected="selected"' : ''; ?>>Administrator</option>
				  <option value="Editor" <?php echo ($userrole == 'moderate_comments') ? 'selected="selected"' : ''; ?>>Editor</option>
				  <option value="Author" <?php echo ($userrole == 'upload_files') ? 'selected="selected"' : ''; ?>>Author</option>
				</select>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row">Select the default image size from Media Library for carousel</th>
			<td>
				<select name="thumbnailsize">
				  <option value="thumbnail" <?php echo ($thumbnailsize == 'thumbnail') ? 'selected="selected"' : ''; ?>>Thumbnail size</option>
				  <option value="medium" <?php echo ($thumbnailsize == 'medium') ? 'selected="selected"' : ''; ?>>Medium size</option>
				  <option value="large" <?php echo ($thumbnailsize == 'large') ? 'selected="selected"' : ''; ?>>Large size</option>
				  <option value="full" <?php echo ($thumbnailsize == 'full') ? 'selected="selected"' : ''; ?>>Full size</option>
				</select>
			</td>
		</tr>
		
		<tr>
			<th>Data option</th>
			<td><label><input name='keepdata' type='checkbox' id='keepdata' <?php echo ($keepdata == 1) ? 'checked' : ''; ?> /> Keep data when deleting the plugin</label>
			</td>
		</tr>
		
		<tr>
			<th>Update option</th>
			<td><label><input name='disableupdate' type='checkbox' id='disableupdate' <?php echo ($disableupdate == 1) ? 'checked' : ''; ?> /> Disable plugin version check and update</label>
			</td>
		</tr>
		
		<tr>
			<th>Display carousel in widget</th>
			<td><label><input name='supportwidget' type='checkbox' id='supportwidget' <?php echo ($supportwidget == 1) ? 'checked' : ''; ?> /> Support shortcode in text widget</label>
			</td>
		</tr>
		
		<tr>
			<th>Scripts position</th>
			<td><label><input name='addjstofooter' type='checkbox' id='addjstofooter' <?php echo ($addjstofooter == 1) ? 'checked' : ''; ?> /> Add carousel js scripts to the footer</label>
			</td>
		</tr>
					
        </table>
        
        <p class="submit"><input type="submit" name="save-carousel-options" id="save-carousel-options" class="button button-primary" value="Save Changes"  /></p>
        
        </form>
        
		</div>
		<?php
	}
		
	function print_register() {
		?>
		<div class="wrap">
		<div id="icon-wonderplugin-carousel" class="icon32"><br /></div>
			
		<h2><?php _e( 'Register', 'wonderplugin_carousel' ); ?></h2>
		<?php
				
		if (isset($_POST['save-carousel-license']) && check_admin_referer('wonderplugin-carousel', 'wonderplugin-carousel-register') )
		{		
			unset($_POST['save-carousel-license']);

			$ret = $this->controller->check_license($_POST);
			
			if ($ret['status'] == 'valid')
				echo '<div class="updated"><p>The key has been saved.</p></div>';
			else if ($ret['status'] == 'expired')
				echo '<div class="error"><p>Your free upgrade period has expired, please renew your license.</p></div>';
			else if ($ret['status'] == 'invalid')
				echo '<div class="error"><p>The key is invalid.</p></div>';
			else if ($ret['status'] == 'abnormal')
				echo '<div class="error"><p>There is abnormal usage with your license key, please contact support@wonderplugin.com for more information.</p></div>';
			else if ($ret['status'] == 'timeout')
				echo '<div class="error"><p>The license server can not be reached, please try again later.</p></div>';
			else if ($ret['status'] == 'empty')
				echo '<div class="error"><p>Please enter your license key.</p></div>';
		}
		else if (isset($_POST['deregister-carousel-license']) && check_admin_referer('wonderplugin-carousel', 'wonderplugin-carousel-register') )
		{	
			$ret = $this->controller->deregister_license($_POST);
			
			if ($ret['status'] == 'success')
				echo '<div class="updated"><p>The key has been deregistered.</p></div>';
			else if ($ret['status'] == 'timeout')
				echo '<div class="error"><p>The license server can not be reached, please try again later.</p></div>';
			else if ($ret['status'] == 'empty')
				echo '<div class="error"><p>The license key is empty.</p></div>';
		}
		
		$settings = $this->controller->get_settings();
		$disableupdate = $settings['disableupdate'];
		
		$key = '';
		$info = $this->controller->get_plugin_info();
		if (!empty($info->key) && ($info->key_status == 'valid' || $info->key_status == 'expired'))
			$key = $info->key;
		
		?>
		
		<?php 
		if ($disableupdate == 1)
		{
			echo "<h3 style='padding-left:10px;'>The plugin version check and update is currently disabled. You can enable it in the Settings menu.</h3>";
		}
		else
		{
			if (empty($key)) { ?>
				<form method="post">
				<?php wp_nonce_field('wonderplugin-carousel', 'wonderplugin-carousel-register'); ?>
				<table class="form-table">
				<tr>
					<th>Enter Your License Key:</th>
					<td><input name="wonderplugin-carousel-key" type="text" id="wonderplugin-carousel-key" value="" class="regular-text" /> <input type="submit" name="save-carousel-license" id="save-carousel-license" class="button button-primary" value="Register License Key"  />
					</td>
				</tr>
				</table>
				</form>
			<?php } else { ?>
				<form method="post">
				<?php wp_nonce_field('wonderplugin-carousel', 'wonderplugin-carousel-register'); ?>
				<table class="form-table">
				<tr>
					<th>Your License Key is:</th>
					<td><?php echo esc_html($key); ?> &nbsp;&nbsp;&nbsp;&nbsp;<input name="wonderplugin-carousel-key" type="hidden" id="wonderplugin-carousel-key" value="<?php echo esc_html($key); ?>" class="regular-text" /><input type="submit" name="deregister-carousel-license" id="deregister-carousel-license" class="button button-primary" value="Deregister the License Key"  /></label></td>
				</tr>
				</table>
				</form>
				
				<form method="post">
				<?php wp_nonce_field('wonderplugin-carousel', 'wonderplugin-carousel-register'); ?>
				<table class="form-table">
				<?php if ($info->key_status == 'valid') { ?>
				<tr>
					<th></th>
					<td>
					<p><strong>Your license key is valid.</strong> The key has been successfully registered with this domain.</p></td>
				</tr>
				<?php } else if ($info->key_status == 'expired') { ?>
				<tr>
					<th></th>
					<td>
					<p><strong>Your free upgrade period has expired.</strong> To get upgrades, please <a href="https://www.wonderplugin.com/renew/" target="_blank">renew your license</a>.</p>
				</tr>
				<?php } ?>
				</table>
				</form>
			<?php } ?>

		<?php } ?>
		
		<div style="padding-left:10px;padding-top:30px;">
		<a href="<?php echo admin_url('update-core.php?force-check=1'); ?>"><button class="button-primary">Force WordPress To Check For Plugin Updates</button></a>
		</div>
					
		<div style="padding-left:10px;padding-top:20px;">
        <h3><a href="https://www.wonderplugin.com/register-faq/" target="_blank">Where can I find my license key and other frequently asked questions</a></h3>
        </div>
        
		</div>
		
		<?php
	}
		
	function print_items() {
		
		?>
		<div class="wrap">
		<div id="icon-wonderplugin-carousel" class="icon32"><br /></div>
		<div id="wondercarousellightbox_options" data-skinsfoldername=""  data-jsfolder="<?php echo WONDERPLUGIN_CAROUSEL_URL . 'engine/'; ?>" style="display:none;"></div>
			
		<h2><?php _e( 'Manage Carousels', 'wonderplugin_carousel' ); ?> <a href="<?php echo admin_url('admin.php?page=wonderplugin_carousel_add_new'); ?>" class="add-new-h2"> <?php _e( 'New Carousel', 'wonderplugin_carousel' ); ?></a> </h2>
		
		<?php $this->process_actions(); ?>
		
		<form id="carousel-list-table" method="post">
		<input type="hidden" name="page" value="<?php echo esc_html($_REQUEST['page']); ?>" />
		<?php 
		
		if ( !is_object($this->list_table) )
			$this->list_table = new WonderPlugin_Carousel_List_Table($this);
		
		$this->list_table->list_data = $this->controller->get_list_data();
		$this->list_table->prepare_items();
		$this->list_table->display();		
		?>								
        </form>
        
		</div>
		<?php
	}
	
	function print_item()
	{
		if ( !isset( $_REQUEST['itemid'] ) || !is_numeric( $_REQUEST['itemid'] ) )
			return;
		
		?>
		<div class="wrap">
		<div id="icon-wonderplugin-carousel" class="icon32"><br /></div>
		<div id="wondercarousellightbox_options" data-skinsfoldername=""  data-jsfolder="<?php echo WONDERPLUGIN_CAROUSEL_URL . 'engine/'; ?>" style="display:none;"></div>
					
		<h2><?php _e( 'View Carousel', 'wonderplugin_carousel' ); ?> <a href="<?php echo admin_url('admin.php?page=wonderplugin_carousel_edit_item') . '&itemid=' . $_REQUEST['itemid']; ?>" class="add-new-h2"> <?php _e( 'Edit Carousel', 'wonderplugin_carousel' ); ?>  </a> </h2>
		
		<div class="updated"><p style="text-align:center;">  <?php _e( 'To embed the carousel into your page, use shortcode: ', 'wonderplugin_carousel' ); ?> <?php echo esc_attr('[wonderplugin_carousel id="' . $_REQUEST['itemid'] . '"]'); ?></p></div>

		<div class="updated"><p style="text-align:center;">  <?php _e( 'To embed the carousel into your template, use php code: ', 'wonderplugin_carousel' ); ?> <?php echo esc_attr('<?php echo do_shortcode(\'[wonderplugin_carousel id="' . $_REQUEST['itemid'] . '"]\'); ?>'); ?></p></div>
		
		<?php
		echo $this->controller->generate_body_code( $_REQUEST['itemid'], true ); 
		?>	 
		
		</div>
		<?php
	}
	
	function process_actions()
	{
		
		if ( isset($_REQUEST['action']) && ($_REQUEST['action'] == 'delete') && isset( $_REQUEST['itemid'] ) )
		{
			$deleted = 0;
			
			if ( is_array( $_REQUEST['itemid'] ) )
			{
				foreach( $_REQUEST['itemid'] as $id)
				{
					if ( is_numeric($id) )
					{
						$ret = $this->controller->delete_item($id);
						if ($ret > 0)
							$deleted += $ret;
					}
				}
			}
			else if ( is_numeric($_REQUEST['itemid']) )
			{
				$deleted = $this->controller->delete_item( $_REQUEST['itemid'] );
			}
			
			if ($deleted > 0)
			{
				echo '<div class="updated"><p>';
				printf( _n('%d carousel deleted.', '%d carousels deleted.', $deleted), $deleted );
				echo '</p></div>';
			}
		}
		
		if ( isset($_REQUEST['action']) && ($_REQUEST['action'] == 'clone') && isset( $_REQUEST['itemid'] ) && is_numeric( $_REQUEST['itemid'] ))
		{
			$cloned_id = $this->controller->clone_item( $_REQUEST['itemid'] );
			if ($cloned_id > 0)
			{
				echo '<div class="updated"><p>';
				printf( 'New carousel created with ID: %d', $cloned_id );
				echo '</p></div>';
			}
			else
			{
				echo '<div class="error"><p>';
				printf( 'The carousel cannot be cloned.' );
				echo '</p></div>';
			}
		}
	}

	function print_add_new() {
		
		if ( !empty($_POST['wonderplugin-carousel-save-item-post-value']) && !empty($_POST['wonderplugin-carousel-save-item-post']) && check_admin_referer('wonderplugin-carousel', 'wonderplugin-carousel-saveform'))
		{
			$this->save_item_post($_POST['wonderplugin-carousel-save-item-post-value']);
			return;
		}
		
		?>
		<div class="wrap">
		<div id="icon-wonderplugin-carousel" class="icon32"><br /></div>
		<div id="wondercarousellightbox_options" data-skinsfoldername=""  data-jsfolder="<?php echo WONDERPLUGIN_CAROUSEL_URL . 'engine/'; ?>" style="display:none;"></div>
		
		<h2><?php _e( 'New Carousel', 'wonderplugin_carousel' ); ?> <a href="<?php echo admin_url('admin.php?page=wonderplugin_carousel_show_items'); ?>" class="add-new-h2"> <?php _e( 'Manage Carousels', 'wonderplugin_carousel' ); ?>  </a> </h2>
		
		<?php 
		$this->creator = new WonderPlugin_Carousel_Creator($this);		
		
		$settings = $this->controller->get_settings();
		echo $this->creator->render( -1, null, $settings['thumbnailsize'] );
	}
	
	function print_edit_item()
	{

		if ( !empty($_POST['wonderplugin-carousel-save-item-post-value']) && !empty($_POST['wonderplugin-carousel-save-item-post']) && check_admin_referer('wonderplugin-carousel', 'wonderplugin-carousel-saveform'))
		{
			$this->save_item_post($_POST['wonderplugin-carousel-save-item-post-value']);
			return;
		}
		
		if ( !isset( $_REQUEST['itemid'] ) || !is_numeric( $_REQUEST['itemid'] ) )
			return;
	
		?>
		<div class="wrap">
		<div id="icon-wonderplugin-carousel" class="icon32"><br /></div>
		<div id="wondercarousellightbox_options" data-skinsfoldername=""  data-jsfolder="<?php echo WONDERPLUGIN_CAROUSEL_URL . 'engine/'; ?>" style="display:none;"></div>
			
		<h2><?php _e( 'Edit Carousel', 'wonderplugin_carousel' ); ?> <a href="<?php echo admin_url('admin.php?page=wonderplugin_carousel_show_item') . '&itemid=' . $_REQUEST['itemid']; ?>" class="add-new-h2"> <?php _e( 'View Carousel', 'wonderplugin_carousel' ); ?>  </a> </h2>
		
		<?php 
		$this->creator = new WonderPlugin_Carousel_Creator($this);
		$settings = $this->controller->get_settings();
		echo $this->creator->render( $_REQUEST['itemid'], $this->controller->get_item_data( $_REQUEST['itemid'] ), $settings['thumbnailsize'] );
	}
	
	function save_item_post($item_post) {
	
		$item_text = str_replace("\\\\", "\\\\\\\\", stripcslashes($item_post));
		$items = json_decode(trim($item_text), true);
		
		if ( empty($items) )
		{
			$json_error = "json_decode error";
			if ( function_exists('json_last_error_msg') )
				$json_error .= ' - ' . json_last_error_msg();
			else if ( function_exists('json_last_error') )
				$json_error .= 'code - ' . json_last_error();
				
			$ret = array(
					"success" => false,
					"id" => -1,
					"message" => $json_error,
					"errorcontent"	=> $item_post
			);
		}
		else
		{
			foreach ($items as $key => &$value)
			{
				if ($value === true)
					$value = "true";
				else if ($value === false)
					$value = "false";
				else if ( is_string($value) )
					$value = wp_kses_post($value);
			}
		
			if (isset($items["slides"]) && count($items["slides"]) > 0)
			{
				foreach ($items["slides"] as $key => &$slide)
				{
					foreach ($slide as $key => &$value)
					{
						if ($value === true)
							$value = "true";
						else if ($value === false)
							$value = "false";
						else if ( is_string($value) )
							$value = wp_kses_post($value);
					}
				}
			}
		
			$ret = $this->controller->save_item($items);
		}
		?>
				
		<div class="wrap">
		<div id="icon-wonderplugin-carousel" class="icon32"><br /></div>
		
		<?php 
		if (isset($ret['success']) && $ret['success'] && isset($ret['id']) && $ret['id'] >= 0) 
		{
			echo "<h2>Carousel Saved.";
			echo "<a href='" . admin_url('admin.php?page=wonderplugin_carousel_edit_item') . '&itemid=' . $ret['id'] . "' class='add-new-h2'>Edit Carousel</a>";
			echo "<a href='" . admin_url('admin.php?page=wonderplugin_carousel_show_item') . '&itemid=' . $ret['id'] . "' class='add-new-h2'>View Carousel</a>";
			echo "</h2>";
					
			echo "<div class='updated'><p>The carousel has been saved and published.</p></div>";
			echo "<div class='updated'><p>To embed the carousel into your page or post, use shortcode:  [wonderplugin_carousel id=\"" . $ret['id'] . "\"]</p></div>";
			echo "<div class='updated'><p>To embed the carousel into your template, use php code:  &lt;?php echo do_shortcode('[wonderplugin_carousel id=\"" . $ret['id'] . "\"]'); ?&gt;</p></div>"; 
		}
		else
		{
			echo "<h2>WonderPlugin Carousel</h2>";
			echo "<div class='error'><p>The carousel can not be saved.</p></div>";
			echo "<div class='error'><p>Error Message: " . ((isset($ret['message'])) ? $ret['message'] : "") . "</p></div>";
			echo "<div class='error'><p>Error Content: " . ((isset($ret['errorcontent'])) ? $ret['errorcontent'] : "") . "</p></div>";
		}	
	}
		
}