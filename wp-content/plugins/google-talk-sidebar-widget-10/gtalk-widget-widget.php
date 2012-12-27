<?php
class gTalkWidgetWidget extends WP_Widget {
    /** constructor */
    function gTalkWidgetWidget() {
    	$widget_ops = array('description' => __( "Add a google talk badge to your sidebar.", 'gtalk-widget') );
        parent::WP_Widget(false, $name = 'Google Talk Widget', $widget_ops);	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
    	global $gTalkWidget;
    	$gTalkWidget->widget($args, $instance, true);  
    }

    /** @see WP_Widget::update */
    /*
    function update($new_instance, $old_instance) {
    	foreach($new_instance as $key => $value ){
    		if( empty($value) ){
    			unset($new_instance[$key]);
    		}
    	}				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    /*
    function form($instance) {
    	global $gTalkWidget;
        ?>
        	<p><?php _e('These values will supercede those in the options page under Settings','gtalk-widget'); ?></p>
            <p>
            	<label for="<?php echo $this->get_field_id('overlay'); ?>"><?php _e('Disable overlay?', 'gtalk-widget'); ?> </label>
                <input id="<?php echo $this->get_field_id('overlay'); ?>" name="<?php echo $this->get_field_name('overlay'); ?>" type="checkbox" value="1" <?php echo ($instance['overlay']) ? 'checked="checked"':""; ?> />
			</p>
            <p>
            	<label for="<?php echo $this->get_field_id('account_hash'); ?>"><?php _e('Account Hash', 'gtalk-widget'); ?> </label>
                <input id="<?php echo $this->get_field_id('account_hash'); ?>" name="<?php echo $this->get_field_name('account_hash'); ?>" type="text" value="<?php echo $instance['account_hash']; ?>" />
			</p>
            <p>
            	<label for="<?php echo $this->get_field_id('online_image'); ?>"><?php _e('Online Image', 'gtalk-widget'); ?> </label>
                <input id="<?php echo $this->get_field_id('online_image'); ?>" name="<?php echo $this->get_field_name('online_image'); ?>" type="text" value="<?php echo $instance['online_image']; ?>" />
			</p>
            <p>
            	<label for="<?php echo $this->get_field_id('offline_image'); ?>"><?php _e('Offline Image', 'gtalk-widget'); ?> </label>
                <input id="<?php echo $this->get_field_id('offline_image'); ?>" name="<?php echo $this->get_field_name('offline_image'); ?>" type="text" value="<?php echo $instance['offline_image']; ?>" />
			</p>
            <p>
            	<label for="<?php echo $this->get_field_id('offline_link'); ?>"><?php _e('Offline Link', 'gtalk-widget'); ?> </label>
                <input id="<?php echo $this->get_field_id('offline_link'); ?>" name="<?php echo $this->get_field_name('offline_link'); ?>" type="text" value="<?php echo $instance['offline_link']; ?>" />
			</p>
        <?php 
    }
    */

}
?>