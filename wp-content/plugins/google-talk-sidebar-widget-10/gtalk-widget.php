<?php
/*
Plugin Name: Google Talk Widget
Plugin URI: http://netweblogic.com/wordpress/plugins/gtalk-widget/
Description: Ajax driven login widget. Customisable from within your template folder, and advanced settings from the admin area. 
Author: NetWebLogic
Version: 2.0
Author URI: http://netweblogic.com/
Tags: widget, google, chat, talk, instant message, jabber, sidebar, web chat, widget, xmpp

Copyright (C) 2009 NetWebLogic LLC

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
class gTalkWidget {
	
	var $data;
	var $cache;
	
	// Class initialization
	function gTalkWidget() {
		//Set when to run the plugin
		include('gtalkStatus.class.php');
		$this->data = get_option('gtw_data');
		add_action( 'widgets_init', array(&$this,'init') );
		add_action('wp_ajax_nopriv_gtalk_status',array(&$this,'ajax') );
		add_action('wp_ajax_gtalk_status',array(&$this,'ajax') );
		$this->cache = ABSPATH.'wp-content/uploads/gtalk-status.txt';
		$this->cache_url = get_bloginfo('wpurl').'/wp-content/uploads/gtalk-status.txt';
	}
	
	function ajax(){
		//check_admin_referer( 'gtalk_status', '_wpnonce_gtalk_status' );
		echo ($this->is_online()) ? $this->online_account:'0';
		die();
	}
	
	// Actions to take upon initial action hook
	function init(){
		//Load gtw options
					
		//Register widget
		register_widget("gTalkWidgetWidget");
		
		//Add logout/in redirection
		add_action('wp_footer', array(&$this, 'footer'));
		add_shortcode('gtalk-widget', array(&$this, 'shortcode'));
		add_shortcode('gtw', array(&$this, 'shortcode'));
		
		//enqueue overlay script
		wp_enqueue_script('gtalk-widget',WP_PLUGIN_URL . '/google-talk-sidebar-widget-10/overlay.js', array('jquery'));
		wp_enqueue_style('gtalk-widget',WP_PLUGIN_URL . '/google-talk-sidebar-widget-10/gtalk.css');
		$ajaxurl = ($this->data['cache_enabled']) ? $this->cache_url:admin_url( 'admin-ajax.php' );
		wp_localize_script('gtalk-widget', 'gTalk', array(
			'ajaxurl' => $ajaxurl,
			'online_account' => $this->is_online(),
			'online_image' => $this->data['online_image'],
			'offline_image' => $this->data['offline_image'],
			'online_alt' => __('Online - click to chat'),
			'offline_alt' => __('Offline - click to leave a message'),
			'offline_link' => $this->data['offline_link']
		));
	}
	
	/*
	 * WIDGET OPERATIONS
	 */	
	function widget($args, $instance = array(), $is_widget=false ){
		//Extract widget arguments
		extract($args);
		//Merge instance options with global default options
		$gtw_data = wp_parse_args($instance, $this->data);
		//Add template logic
		echo ( $is_widget && !empty($instance['title']) ) ? $before_widget . $before_title . $instance['title'] . $after_title : '';
		//offline/online?
		$random_id = rand(10,1000);
		$link = ( $this->online_account || empty($gtw_data['offline_link']) ) ? "#":$gtw_data['offline_link'];
		$image = ( $this->online_account ) ? $gtw_data['online_image']:$gtw_data['offline_image'];
		$alt = ( $this->online_account ) ? __('Online - click to chat','gtalk-widget'):__('Offline - click to leave a message','gtalk-widget');
		?>
		<a href="<?php echo $link; ?>" rel="#gtalk-overlay" id="gtalk-widget-<?php echo $random_id; ?>" class="gtalk-widget <?php echo ($this->online_account) ? 'online':''; ?>"><img src="<?php echo $image ?>" alt="<?php _e('Online - click to chat'); ?>" /></a>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('#gtalk-widget-<?php echo $random_id; ?>').data('gtalk',{
					'online_image':'<?php echo $gtw_data['online_image'] ?>',
					'offline_image':'<?php echo $gtw_data['offline_image'] ?>',
					'offline_link':'<?php echo $gtw_data['offline_link'] ?>'
				});
			});			
		</script>
		<?php		
		echo ( $is_widget && !empty($instance['title']) ) ? $after_widget:'';		
	}
	
	function footer(){
		?>
		<div id="gtalk-overlay" class="gtalk-overlay">
			<?php if( !empty($this->data['custom_text']) ) echo "<div>{$this->data['custom_text']}</div>"; ?>
			<div id="gtalk-client"></div>
			<div class="gtalk-credit">Powered by <a href="http://wordpress.org/extend/plugins/google-talk-sidebar-widget-10/" target="_blank">Google Talk Widget</a></div>
		</div>
		<a href="#" rel="#gtalk-overlay" id="gtalk-trigger" style="display:none;"></a>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				var gTalkTrigger = $("#gtalk-trigger").overlay({
					mask: { 
						color: '#ebecff',
						loadSpeed: 200,
						opacity: 0.9
					},
					closeOnClick: true,
					onLoad: function(){
						$('#gtalk-client').empty();
						var badgeno = gTalk.online_account;
						$('#gtalk-client').append('<iframe src="http://www.google.com/talk/service/badge/Start?tk='+gTalk.online_account+'"></iframe>');
					},
					onClose: function(){
						$('#gtalk-client').empty();
					}
				});	
				$('.gtalk-overlay .close').live('click', function(){ $('#gtalk-client').empty(); })
				var gTalkPoll = function(){
					$.post( gTalk.ajaxurl, { 'action':'gtalk_status' }, function(data){
						gTalk.online_account = data;
						if( data.length > 10 ) { $('.gtalk-widget').addClass('online'); } else { $('.gtalk-widget').removeClass('online'); } 
						$('.gtalk-widget').each( function(i, el){
							el = $(el);
							var status = el.data('gtalk');
							if( el.hasClass('online') ){
								el.attr('href','#');
								el.children('img').attr({
									'src':status.online_image,
									'alt':status.online_alt
								});
							}else{
								el.attr('href',status.offline_link);
								el.children('img').attr({
									'src':status.offline_image,
									'alt':status.offline_alt
								});
							}
						});
					}, 'text');
				}
				setInterval(gTalkPoll, 10000);
				$('.gtalk-widget').click( function(){
					el = $(this);
					if( el.hasClass('online') ){
						$('#gtalk-trigger').trigger('click');
					}else{
						window.location = el.data('gtalk').offline_link;
					}
				});
			});			
		</script>		
		<?php
	}
	
	function shortcode($atts){
		$atts = shortcode_atts($this->data, $atts);
		ob_start();
		$this->widget(array(), $atts );
		return ob_get_clean();
	}
	
	function is_online($account_hash = false){
		if($account_hash){
			$gtalkStatus = new gTalkStatus($account_hash);
			if($gtalkStatus->isOnline()){
				return $account_hash;
			}
		}
		if( file_exists($this->cache) && filemtime($this->cache) + $this->data['cache_time'] > time() ){
			$this->online_account = file_get_contents($this->cache);
		}else{
			$is_online = array();
			$this->online_account = false;
			foreach($this->data['account_hash'] as $account_hash){
				//Include the current gtalk online finder mechanism (ideally we could use xmpp somehow but more complicated)
				if( !empty($account_hash) ){
					$gtalkStatus = new gTalkStatus($account_hash);
					if($gtalkStatus->isOnline()){
						$this->online_account = $account_hash;
						break;
					}
				}
			}
			if( is_writable( dirname($this->cache) ) ){
				$result = ($this->online_account) ? $this->online_account:0;
				$file = fopen($this->cache, 'w');
				fwrite($file, $result);
				fclose($file);
			}
		}
		return $this->online_account;
	}
}
//Add translation
load_plugin_textdomain('gtalk-widget', false, "gtalk-widget/langs");  

//Include admin file if needed
if(is_admin()){
	include_once('gtalk-widget-admin.php');
}
//Include widget
include_once('gtalk-widget-widget.php');

//Include pluggable functions file if user specifies in settings
$gtw_data = get_option('gtw_data');
if( !empty($gtw_data['notification_override']) && $gtw_data['notification_override'] == '1' ){
	include_once('pluggable.php');
}

//Template Tag
function gtalk_widget($atts = ''){
	global $gTalkWidget;
	echo $gTalkWidget->shortcode($atts);
}


/* Creating the wp_events table to store event data*/
function gtalk_widget_activate() {
	add_option( 'gtw_data', array(
		'account_hash' => array(1=>'',2=>'',3=>''),
		'online_image' => WP_PLUGIN_URL . '/google-talk-sidebar-widget-10/images/online.png',
		'offline_image' => WP_PLUGIN_URL . '/google-talk-sidebar-widget-10/images/offline.png',
		'custom_text' => '',
		'cache_time' => 20,
		'cache_enabled' => false
	));
}
register_activation_hook( __FILE__,'gtalk_widget_activate');

// Start plugin
global $gTalkWidget; 
$gTalkWidget = new gTalkWidget();

?>