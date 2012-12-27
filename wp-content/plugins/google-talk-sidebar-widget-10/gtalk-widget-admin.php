<?php
/*
Copyright (C) 2011 NetWebLogic LLC

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Class initialization
class gTalkWidgetAdmin{
	// action function for above hook
	function gTalkWidgetAdmin() {
		global $user_level;
		add_action ( 'admin_menu', array (&$this, 'menus') );
		if( !empty($_GET['gtw_dismiss_notice']) && $_GET['gtw_dismiss_notice'] == '1' ){
			update_option('gtw_notice', 1);
		}elseif( get_option('gtw_notice') != 1 && $user_level == 10 ){
			add_action('admin_notices', array(&$this, 'admin_notices') );			
		}
	}
	
	function menus(){
		$page = add_options_page('Google Talk Widget', 'Google Talk Widget', 'manage_options', 'gtalk-widget', array(&$this,'options'));
	}

	function admin_notices() {
		$dismiss = $_SERVER['REQUEST_URI'];
		$dismiss .= (strpos($dismiss, '?')) ? "&amp;":"?";
		$dismiss .= "gtw_dismiss_notice=1";
		?>
		<div id='gtw-warning' class='updated fade'>
			<p>
				This plugin has been rewritten to provide significantly more functionality, including customizeable online/offline images and a chat window that's an overlay, not an upgly popup. If you would like to use the old style of widget, <a href="http://www.google.com/talk/service/badge/New">generate a badge</a> with Google and paste the contents into a normal WP text widget.
				<a href="<?php echo bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=gtalk-widget">Settings</a> | 
				<a href="<?php echo $dismiss ?>"><?php _e('Dismiss','gtalk-widget') ?></a> 
			</p>
		</div>
		<?php
	}
	
	function options() {
		global $gTalkWidget;
		add_option('gtw_data');
		$gtw_data = array();	
		
		if( is_admin() && !empty($_POST['gtwsubmitted']) ){
			//Build the array of options here
			foreach ($_POST as $postKey => $postValue){
				if( substr($postKey, 0, 4) == 'gtw_' ){
					//For now, no validation, since this is in admin area.
					if($postValue != ''){
						$gtw_data[substr($postKey, 4)] = $postValue;
					}
				}
			}
			update_option('gtw_data', $gtw_data);
			if( !empty($_POST['gtw_notification_override']) ){
				update_option('gtw_notification_override',$_POST['gtw_notification_override']);
			}
			?>
			<div class="updated"><p><strong><?php _e('Changes saved.'); ?></strong></p></div>
			<?php
		}else{
			$gtw_data = get_option('gtw_data');	
		}
		?>
		<div class="wrap nwl-plugin">
			<?php if( !is_writable( dirname($gTalkWidget->cache) ) ): ?>
			<div class='error'>
				Your folder <code><?php echo dirname($gTalkWidget->cache); ?></code> is not writeable by the server, we need to create a file here 
				<code><?php echo $gTalkWidget->cache; ?></code> in order to reduce the load of status requests on your server.
			</div>
			<?php endif; ?>
			<h2>Google Talk Widget</h2>
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				<div id="side-info-column" class="inner-sidebar">
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Click to toggle"></div>
						<h3 class="hndle">Plugin Information</h3>
						<div class="inside">
							<p>This plugin was developed by <a href="http://twitter.com/marcussykes">Marcus Sykes</a> @ <a href="http://netweblogic.com">NetWebLogic</a></p>
							<p>Please visit <a href="http://wordpress.org/tags/google-talk-sidebar-widget-10">the forum</a> for plugin support.</p>
							<p>If you'd like to <a href="http://codex.wordpress.org/Translating_WordPress" target="_blank">translate this plugin</a>, the language files are in the langs folder. Please email a po and mo file to wp.plugins@netweblogic.com and we'll incorporate it into the plugin.</p>
						</div>
					</div>
				</div>
				<div id="post-body">
					<div id="post-body-content">
						<p>If you have any suggestions, come over to our plugin page and leave a comment. It may just happen!</p>
						<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
						<table class="form-table">
							<tbody id="gtw-body">
								<tr valign="top">
									<td colspan="2">
										<h3><?php _e("General Settings", 'gtalk-widget'); ?></h3>
									</td>
								</tr>
								<tr valign="top">
									<td scope="row" style="width:150px;">
										<label><?php _e("Account Hash", 'gtalk-widget'); ?></label>
									</td>
									<td>
										<?php for( $i=1; $i<=3; $i++ ): ?>
											<input type="text" name="gtw_account_hash[<?php echo $i; ?>]" style="width:80%; padding:2px" value='<?php echo (!empty($gtw_data['account_hash'][$i])) ? $gtw_data['account_hash'][$i]:''; ?>' class='wide' />
											<?php if( !empty($gtw_data['account_hash'][$i]) ){
												echo $gTalkWidget->is_online($gtw_data['account_hash'][$i]) ? "<span style='color:green;'>Online</span>":"<span style='color:red;'>Offline</span>";
											}?>
											<br />
										<?php endfor; ?>
										<i><?php echo sprintf(__("Account hash given to you by google. If the first account is offline, the plugin will check if the next one in line is online and select the first available account. You need to go to <a href='%s' target='_blank'>this page</a> whilst logged into the account you want to chat with, and in the generated code extract the alphanumeric code. See below as an example:", 'gtalk-widget'), 'http://www.google.com/talk/service/badge/New'); ?></i><br />
										<code>&lt;iframe src="http://www.google.com/talk/service/badge/Show?tk=<strong style="color:red">z01q6....cmq0omr3a0iqpf612</strong>&amp;w=200&amp;h=60" frameborder="0" allowtransparency="true" width="200" height="60"&gt;&lt;/iframe&gt;</code>
									</td>
								</tr>
								<?php  /*
								<tr valign="top">
									<td scope="row">
										<label><?php _e("Disable overlay for talk widget?", 'gtalk-widget'); ?></label>
									</td>
									<td>
										<input style="margin:0px; padding:0px; width:auto;" type="checkbox" name="gtw_overlay" value='1' class='wide' <?php echo ( !empty($gtw_data['overlay']) && $gtw_data['overlay'] == '1' ) ? 'checked="checked"':''; ?> />
										<br />
										<i><?php _e("If you disable the overlay, the chat client will appear in a popup.", 'gtalk-widget'); ?></i>
									</td>
								</tr>	
								*/ ?>
								<tr valign="top">
									<td scope="row">
										<label><?php _e("Custom Text", 'gtalk-widget'); ?></label>
									</td>
									<td>
										<textarea name="gtw_custom_text" style="width:90%; height:150px;"><?php echo (!empty($gtw_data['custom_text'])) ? $gtw_data['custom_text']:''; ?></textarea>
										<br /><i><?php _e("Displayed just above the chat window. HTML allowed", 'gtalk-widget'); ?></i> 
									</td>
								</tr>	
								<tr valign="top">
									<td colspan="2">
										<h3><?php _e("Status Images", 'gtalk-widget'); ?></h3>
									</td>
								</tr>
								<tr valign="top">
									<td scope="row">
										<label><?php _e("Online Status Image", 'gtalk-widget'); ?></label>
									</td>
									<td>
										<input type="text" name="gtw_online_image" style="width:90%; padding:2px" value='<?php echo (!empty($gtw_data['online_image'])) ? $gtw_data['online_image']:''; ?>' class='wide' />
										<br /><i><?php _e("Displayed when you're online, brings up the talk widget if you're online.", 'gtalk-widget'); ?></i> 
									</td>
								</tr>
								<tr valign="top">
									<td scope="row">
										<label><?php _e("Offline Status Image", 'gtalk-widget'); ?></label>
									</td>
									<td>
										<input type="text" name="gtw_offline_image"  style="width:90%; padding:2px" value='<?php echo (!empty($gtw_data['offline_image'])) ? $gtw_data['offline_image']:''; ?>' class='wide' />
										<br /><i><?php _e("Displayed when you're offline, will become a link if you fill the destination below.", 'gtalk-widget'); ?></i> 
									</td>
								</tr>					
								<tr valign="top">
									<td scope="row">
										<label><?php _e("Offline Status Link", 'gtalk-widget'); ?></label>
									</td>
									<td>
										<input type="text" name="gtw_offline_link" style="width:90%; padding:2px" value='<?php echo (!empty($gtw_data['offline_link'])) ? $gtw_data['offline_link']:''; ?>' class='wide' />
										<br /><i><?php _e("Displayed when you're offline, will become a link if you fill the destination below.", 'gtalk-widget'); ?></i> 
									</td>
								</tr>
								
								<tr valign="top">
									<td colspan="2">
										<h3><?php _e("Caching", 'gtalk-widget'); ?></h3>
										<p><?php _e("In order to avoid exessive calls to the google servers, and to speed up page loading times, a cache file is used to store online status information, and this is refreshed at defineable intervals below.", 'gtalk-widget'); ?>
									</td>
								</tr>
								<tr valign="top">
									<td scope="row">
										<label><?php _e("Enable Caching", 'gtalk-widget'); ?></label>
									</td>
									<td style="text-align:left;">
										<input style="text-align:left;" type="radio" name="gtw_cache_enabled"  style="width:90%; padding:2px" value='1' <?php echo ($gtw_data['cache_enabled'] == 1) ? 'checked="checked"':''; ?> /> Enabled<br />
										<input style="text-align:left;" type="radio" name="gtw_cache_enabled"  style="width:90%; padding:2px" value='0' <?php echo empty($gtw_data['cache_enabled']) ? 'checked="checked"':''; ?> />Disabled
										<br /><i><?php _e("This is recommended if you have many requests to your site. What happens here is every x seconds, the next request will reset a text file with online info for this plugin. That way, user browsers will check the text file for checking if you're online and circumvent any extra PHP being run.", 'gtalk-widget'); ?></i> 
									</td>
								</tr>
								<tr valign="top">
									<td scope="row">
										<label><?php _e("Cache Expiry", 'gtalk-widget'); ?></label>
									</td>
									<td>
										<input type="text" name="gtw_cache_time" style="width:90%; padding:2px" value='<?php echo (!empty($gtw_data['cache_time'])) ? $gtw_data['cache_time']:20; ?>' class='wide' />
										<br /><i><?php _e("How many seconds should the cache be held for (should be a low number, since online/offline status should be refreshed regularly).", 'gtalk-widget'); ?></i> 
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr valign="top">
									<td colspan="2">	
										<input type="hidden" name="gtwsubmitted" value="1" />
										<p class="submit">
											<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
										</p>							
									</td>
								</tr>
							</tfoot>
						</table>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

function gTalkWidgetAdminInit(){
	global $gTalkWidgetAdmin; 
	$gTalkWidgetAdmin = new gTalkWidgetAdmin();
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'gTalkWidgetAdminInit' );
?>