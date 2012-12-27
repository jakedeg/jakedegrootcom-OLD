<?php
/**
 Plugin Name: WP-SimpleViewer
 Plugin URI: http://simpleviewer.net/simpleviewer/support/wp-simpleviewer
 Description: Create SimpleViewer galleries with WordPress.
 Author: SimpleViewer Inc.
 Version: 2.1.3
 Author URI: http://simpleviewer.net
 Text Domain: simpleviewer
 */

/**
 * SimpleViewer Plugin Class
 */
class SimpleViewer {

    var $version = '2.1.3';
    /**
     * Initalize the plugin by registering the hooks
     */
    function __construct() {

        // Load localization domain
        load_plugin_textdomain( 'simpleviewer', false, dirname(plugin_basename(__FILE__)) . '/languages' );

        // Register hooks
        add_action( 'admin_menu', array(&$this, 'add_menus') );
        add_action( 'admin_init', array(&$this, 'add_settings') );
        add_action( 'admin_head', array(&$this, 'add_script_configs') );
        add_action('admin_print_scripts', array(&$this, 'add_script'));

        // include js file
        $this->include_scripts();

        // register short code
        add_shortcode('simpleviewer', array(&$this, 'shortcode_handler'));

        if (current_user_can('edit_posts') || current_user_can('edit_pages') ) {
            // Add button to write post screen
            $this->add_buttons();

            // Add Quicktag
            add_action( 'edit_form_advanced', array(&$this, 'add_quicktags') );
            add_action( 'edit_page_form', array(&$this, 'add_quicktags') );

            /* Use the save_post action to do something with the data entered */
            add_action('save_post', array(&$this, 'save_postdata'));
        }
    }

    /**
     * Add button to the write post screen
     *
     * @return <type>
     */
    function add_buttons() {
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
            return;
        }
        if ( get_user_option('rich_editing') == 'true') {
            add_filter( 'tiny_mce_version', array(&$this, 'tiny_mce_version'), 0 );
            add_filter( 'mce_external_plugins', array(&$this, 'add_tinyMCE_plugin'), 0 );
            add_filter( 'mce_buttons', array(&$this, 'add_tinyMCE_button'), 0);
        }
    }

    // Break the browser cache of TinyMCE
    function tiny_mce_version( $version ) {
        return $version . '-sv' . $this->version;
    }

    // Load the custom TinyMCE plugin
    function add_tinyMCE_plugin( $plugins ) {
        $plugins['simpleviewer'] = plugin_dir_url(__FILE__) . 'lib/tinymce3/editor_plugin.js';
        return $plugins;
    }

    function add_tinyMCE_button( $buttons ) {
        array_push( $buttons, 'separator', 'simpleviewer' );
        return $buttons;
    }

    // Add a button to the quicktag view
    function add_quicktags() {
        $buttonshtml = '<input type="button" class="ed_button" onclick="SV.Flash.embed.apply(SV.Flash); return false;" title="Add SimpleViewer Gallery in your post" value="Add SimpleViewer Gallery" />';
        ?>
<script type="text/javascript" charset="utf-8">
    // <![CDATA[
    (function(){

        if (typeof jQuery === 'undefined') {
            return;
        }

        jQuery(document).ready(function(){
            // Add the buttons to the HTML view
            jQuery("#ed_toolbar").append('<?php echo $buttonshtml; ?>');
        });
    }());
    // ]]>
</script>
    <?php
    }

    function add_script_configs() {
        ?>
<script type="text/javascript" charset="utf-8">
    // <![CDATA[
    if (typeof SV !== 'undefined' && typeof SV.Flash !== 'undefined') {
        SV.Flash.configUrl = "<?php echo plugin_dir_url(__FILE__) . 'config.php'; ?>";
    }
    // ]]>
</script>
    <?php
    }

    /**
     * Enqueue the script
     */
    function add_script() {
    // Enqueue the script
        wp_enqueue_script( 'sv_embed', plugin_dir_url(__FILE__) . 'js/sv.js', array(), $this->version );
    }

    /**
     * Register the settings page
     */
    function add_menus() {
        add_menu_page(__('SimpleViewer', 'simpleviewer'), __('SimpleViewer', 'simpleviewer'), 8, 'sv_manage_gallery', array(&$this, 'manage_gallery_page'), plugin_dir_url(__FILE__) . 'img/icon_trans_21x16.png');
        add_submenu_page( 'sv_manage_gallery', __('Edit Galleries', 'simpleviewer'), __('Edit Galleries', 'simpleviewer'), 8, 'sv_manage_gallery', array(&$this, 'manage_gallery_page'));
        add_submenu_page( 'sv_manage_gallery', __('Help', 'simpleviewer'), __('Help', 'simpleviewer'), 8, 'sv_add_help', array(&$this, 'help_page'));
    }

    /**
     * add options
     */
    function add_settings() {
    // Register options
        register_setting( 'simpleviewer', 'simpleviwer_options');
    }

    /**
     * hook to add action links
     * @param <type> $links
     * @return <type>
     */
    function add_action_links( $links ) {
    // Add a link to this plugin's settings page
        $settings_link = '<a href="options-general.php?page=simpleviewer">' . __("Settings", 'simpleviewer') . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Adds Footer links. Based on http://striderweb.com/nerdaphernalia/2008/06/give-your-wordpress-plugin-credit/
     */
    function add_footer_links() {
        $plugin_data = get_plugin_data( __FILE__ );
        printf('%1$s ' . __("plugin", 'simpleviewer') .' | ' . __("Version", 'simpleviewer') . ' %2$s | '. __('by', 'simpleviewer') . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
    }

    /**
     * Dipslay the Settings page
     */
    function manage_gallery_page() {
?>
<style>
#icon-sv-logo {
    background:url("<?php echo plugin_dir_url(__FILE__) . 'img/icon_trans_35x26.png'; ?>") no-repeat scroll transparent;
}
</style>

<div class="wrap">
    <?php screen_icon('sv-logo'); ?>
    <h2><?php _e( 'Edit SimpleViewer Galleries', 'simpleviewer' ); ?></h2>

<?php

    if (isset ($_GET['sv_action']) && $_GET['sv_action'] != '') {
        switch ($_GET['sv_action']) {
            case 'edit-gallery':
                $gallery_id = $_GET['sv_gallery_id'];
                $this->display_gallery_edit_form($gallery_id);
                break;
            case 'gallery-edited':
                $this->edit_gallery();
                echo "<p>Gallery successfully edited.</p>";
                $this->display_gallery_table();
                break;
            case 'delete-gallery':
                $gallery_id = $_GET['sv_gallery_id'];
                $gallery_filename = $this->get_gallery_path() . $gallery_id . '.xml';
                if (file_exists($gallery_filename)) {
                    unlink($gallery_filename);
                    echo "<p>Gallery successfully Deleted.</p>";
                } else {
                    echo "<p>The selected gallery seems to have been already deleted</p>";
                }
                $this->display_gallery_table();
                break;
        }
    } else {
        $this->display_gallery_table();
    }
?>

</div>
<?php
        // Display credits in Footer
        add_action( 'in_admin_footer', array(&$this, 'add_footer_links'));
    }

    /**
     * Display help information
     */
    function help_page() {
?>
<style>
#icon-sv-logo {
    background:url("<?php echo plugin_dir_url(__FILE__) . 'img/icon_trans_35x26.png'; ?>") no-repeat scroll transparent;
}
</style>

<div class="wrap">
    <?php screen_icon('sv-logo'); ?>
    <h2><?php _e( 'WP-SimpleViewer Help', 'simpleviewer' ); ?></h2>

    <p>
        <a href = "http://www.simpleviewer.net/simpleviewer/support/wp-simpleviewer">Get support and view WP-SimpleViewer documentation</a>
    </p>
</div>
<?php
    }

    /**
     * Build the gallery table based on file names
     */
    function display_gallery_table() {
        $galleries = $this->get_all_galleries($this->get_gallery_path());
?>
        <table class="widefat">

            <thead>
                <tr>
                    <th>Gallery Id</th>
                    <th>Last Modified</th>
                    <th>Post title</th>
                    <th>Gallery title</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Gallery Id</th>
                    <th>Last Modified</th>
                    <th>Post title</th>
                    <th>Gallery title</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </tfoot>
            <tbody>
<?php
            foreach ($galleries as $gallery) {
                $gallery_filename = $this->get_gallery_path() . $gallery;
?>
               <tr>
                   <td><?php echo pathinfo($gallery, PATHINFO_FILENAME); ?></td>
                   <td><?php echo date ("F d Y H:i:s", filemtime($gallery_filename)); ?></td>
                   <td>
<?php
                        $post_id = $this->getPostID($gallery_filename);
                        echo get_the_title($post_id);
?>
                   </td>
                   <td><?php echo $this->getGalleryTitle($gallery_filename); ?></td>
                   <td><?php echo '<a href = "' . get_bloginfo("wpurl") . '/wp-admin/admin.php?page=sv_manage_gallery&sv_action=edit-gallery&sv_gallery_id=' . pathinfo($gallery, PATHINFO_FILENAME) .'">Edit gallery</a>'; ?></td>
                   <td><?php echo '<a class="deleteGallery" href = "' . get_bloginfo("wpurl") . '/wp-admin/admin.php?page=sv_manage_gallery&sv_action=delete-gallery&sv_gallery_id=' . pathinfo($gallery, PATHINFO_FILENAME) .'">Delete gallery</a>'; ?></td>
               </tr>
<?php
            }
?>
            </tbody>
        </table>
<?php
    }

    /**
     * Display gallery edit form
     */
    function display_gallery_edit_form($gallery_id) {
?>

<style>
.col1, .col2, .col3, .col4, .col5 {
	float: left;
	display: inline;
    padding:5px;
}

.col1 {
	white-space: nowrap;
	width: 160px;
}

.col2 {
	width: 450px;
}

.col3 {
	width: 160px;
}

.col4 {
	width: 160px;
}

.col5 {
	width: 350px;
}

.help, .clear {
	clear: left;
}

div.clear {
	height: 1px;
}
</style>
        <form id="build_form" action="<?php echo get_bloginfo("wpurl") . '/wp-admin/admin.php?page=sv_manage_gallery&sv_action=gallery-edited';?>" method="post">
            <input type="hidden" value="<?php echo $gallery_id; ?>" name="sv_gallery_id" />
<?php
            $gallery_file = $this->get_gallery_path() . $gallery_id . '.xml';

            $gallery_xml = simplexml_load_file($gallery_file);
            $attributes = $gallery_xml->attributes();
            $proOptions = $this->getProOptions($attributes);
?>
            <input type="hidden" value="<?php echo $attributes['useFlickr']; ?>" name="useFlickr" />

		<fieldset>
			<div id="toggleable2">
				<div class="col1">
					<label for="title" class="info"><?php _e("Gallery Title",'simpleviewer'); ?>:</label>
				</div>
				<div class="col2">
					<input type="text" id="title" name="title" value="<?php echo $attributes['title'] ?>" size="20" />
				</div>

				<div class="clear">&nbsp;</div>

				<div class="col1">
					<label class="info"><?php _e("Image Source",'simpleviewer'); ?>:</label>
				</div>
				<div class="col2">
					<select id="library" name="library">
						<option value="wordpress"><?php _e("WordPress Library", 'simpleviewer'); ?></option>
                        <option value="flickr" <?php echo selected('true', $attributes['useFlickr']); ?>><?php _e('Flickr', 'simpleviewer'); ?></option>
					</select>
				</div>

				<div class="clear">&nbsp;</div>
                <div class="col1"></div>
                <div class="clear">&nbsp;</div>

                <div id="toggle_flickr">
					<div class="col1">
						<label for="flickrUserName" class="info" >Flickr Username</label>
					</div>
					<div class="col4">
                        <input type="text" id="flickrUserName" name="flickrUserName" value="<?php echo $attributes['flickrUserName']; ?>" size="20" />
					</div>

					<div class="col1">
						<label for="flickr_tag" class="info" >Flickr Tag</label>
					</div>
					<div class="col4">
                        <input type="text" id="flickrTags" name="flickrTags" value="<?php echo $attributes['flickrTags']; ?>" size="20" />
                    </div>

					<div class="clear">&nbsp;</div>
                </div>

					<div class="col1">
						<label for="galleryStyle" class="info" >Gallery Style</label>
					</div>
					<div class="col4">
                        <select id="galleryStyle" name="galleryStyle">
                            <option <?php echo selected('MODERN', $attributes['galleryStyle']); ?> value="MODERN">Modern</option>
                            <option <?php echo selected('CLASSIC', $attributes['galleryStyle']); ?> value="CLASSIC">Classic</option>
                            <option <?php echo selected('COMPACT', $attributes['galleryStyle']); ?> value="COMPACT">Compact</option>
                        </select>
					</div>

					<div class="clear">&nbsp;</div>

					<div class="col1">
						<label for="thumbPosition" class="info" >Thumb Position</label>
					</div>
					<div class="col4">
                        <select id="thumbPosition" name="thumbPosition">
                            <option <?php echo selected('TOP', $attributes['thumbPosition']); ?> value="TOP">Top</option>
                            <option <?php echo selected('BOTTOM', $attributes['thumbPosition']); ?> value="BOTTOM">Bottom</option>
                            <option <?php echo selected('LEFT', $attributes['thumbPosition']); ?> value="LEFT">Left</option>
                            <option <?php echo selected('RIGHT', $attributes['thumbPosition']); ?> value="RIGHT">Right</option>
                            <option <?php echo selected('NONE', $attributes['thumbPosition']); ?> value="NONE">None</option>
                        </select>
					</div>

					<div class="col1">
						<label for="frameWidth" class="info" >Frame Width, px</label>
					</div>
					<div class="col4">
                        <input type="text" id="frameWidth" name="frameWidth" value="<?php echo $attributes['frameWidth']; ?>" size="20" />
					</div>

					<div class="clear">&nbsp;</div>

					<div class="col1">
						<label for="maxImageWidth" class="info" >Max Image Width, px</label>
					</div>
					<div class="col4">
                        <input type="text" id="maxImageWidth" name="maxImageWidth" value="<?php echo $attributes['maxImageWidth'] ?>" size="20" />
                    </div>

					<div class="col1">
						<label for="maxImageHeight" class="info" >Max Image Height, px</label>
					</div>
					<div class="col4">
                        <input type="text" id="maxImageHeight" name="maxImageHeight" value="<?php echo $attributes['maxImageHeight'] ?>" size="20" />
                    </div>

					<div class="clear">&nbsp;</div>

					<div class="col1">
						<label for="textColor" class="info" >Text Color</label>
					</div>
					<div class="col4">
                        <input type="text" id="textColor" name="textColor" value="<?php echo str_replace('0x', '', $attributes['textColor']); ?>" size="20" />
					</div>

					<div class="col1">
						<label for="frameColor" class="info" >Frame Color</label>
					</div>
					<div class="col4">
                        <input type="text" id="frameColor" name="frameColor" value="<?php echo str_replace('0x', '', $attributes['frameColor']); ?>" size="20" />
					</div>

					<div class="clear">&nbsp;</div>

					<div class="col1">
						<label for="showOpenButton" class="info" >Open Button</label>
					</div>
					<div class="col4">
                        <input type="checkbox" id="showOpenButton" name="showOpenButton" value="true" <?php checked('true', $attributes['showOpenButton']); ?> />
                    </div>

					<div class="col1">
						<label for="showFullscreenButton" class="info" >Fullscreen Button</label>
					</div>
					<div class="col4">
                        <input type="checkbox" id="showFullscreenButton" name="showFullscreenButton" value="true" <?php checked('true', $attributes['showFullscreenButton']); ?> />
                    </div>

					<div class="clear">&nbsp;</div>

					<div class="col1">
						<label for="thumbRows" class="info" >Thumbnail Rows</label>
					</div>
					<div class="col4">
                        <input type="text" id="thumbRows" name="thumbRows" value="<?php echo $attributes['thumbRows']; ?>" size="20" />
					</div>

					<div class="col1">
						<label for="thumbColumns" class="info" >Thumbnail Columns</label>
					</div>
					<div class="col4">
                        <input type="text" id="thumbColumns" name="thumbColumns" value="<?php echo $attributes['thumbColumns']; ?>" size="20" />
					</div>

					<div class="clear">&nbsp;</div>

					<div class="col1">
						<label for="gallery_width" class="info" >Gallery Width</label>
					</div>
					<div class="col4">
                        <input type="text" id="gallery_width" name="gallery_width" value="<?php echo $attributes['e_g_width']; ?>" size="20" />
					</div>

					<div class="col1">
						<label for="gallery_height" class="info" >Gallery Height</label>
					</div>
					<div class="col4">
                        <input type="text" id="gallery_height" name="gallery_height" value="<?php echo $attributes['e_g_height']; ?>" size="20" />
                    </div>

					<div class="clear">&nbsp;</div>

					<div class="col1">
						<label for="backgroundColor" class="info" >Background Color</label>
					</div>
					<div class="col4">
                        <input type="text" id="backgroundColor" name="backgroundColor" value="<?php echo ($attributes['e_bgColor'] == 'transparent') ? '' : $attributes['e_bgColor']; ?>" />
                    </div>

					<div class="col1">
						<label for="transparentBackground" class="info" >Background Transparent</label>
					</div>
					<div class="col4">
                        <input type="checkbox" id="transparentBackground" name="transparentBackground" value="true" <?php checked('transparent', $attributes['e_bgColor'], true); ?> />
                    </div>

					<div class="clear">&nbsp;</div>

					<div class="col1">
						<label for="useFlash" class="info" >Use Flash</label>
					</div>
					<div class="col4">
                        <input type="checkbox" id="useFlash" name="useFlash" value="true" <?php checked('true', $attributes['e_useFlash'], true); ?> />
                    </div>

					<div class="clear">&nbsp;</div>

					<div class="col1">
						<label for="prooptions" class="info" >Pro Options</label>
					</div>
					<div class="col4">
                        <textarea id="prooptions" name="prooptions" cols="50" rows="5" ><?php echo $proOptions;?></textarea>
                    </div>

					<div class="clear">&nbsp;</div>

				<div class="clear">&nbsp;</div>
			</div>


        </fieldset>

		<div class="col1">
            <input type="submit" class="button" id="generate" name="generate" value="<?php _e("Save",'simpleviewer'); ?>" />
            <input type="button" class="button" name="cancel" value="<?php _e("Cancel",'simpleviewer'); ?>" onclick="history.back();" />
		</div>
    </form>

<?php
    }

    /**
     * Get Pro Options
     * 
     * @param <type> $attributes 
     */
    function getProOptions($attributes) {
        $proOptions = "";
        foreach ($attributes as $key => $value) {
            switch ($key) {
                case 'thumbColumns':
                case 'thumbRows':
                case 'showFullscreenButton':
                case 'showOpenButton':
                case 'frameColor':
                case 'textColor':
                case 'maxImageHeight':
                case 'maxImageWidth':
                case 'frameWidth':
                case 'thumbPosition':
                case 'thumbPosition':
                case 'galleryStyle':
                case 'flickrUserName':
                case 'flickrTags':
                case 'useFlickr':
//                case 'languageCode':
                case 'languageList':
                case 'title':
                case 'e_bgColor':
                case 'e_g_width':
                case 'e_g_height':
                case 'e_useFlash':
                case 'postID':
                    break;

                default:
                    $proOptions .= $key . '="' . $value . '"' . "\n";
                    break;
            }
        }
        return $proOptions;
    }
    /**
     * Edit and save gallery
     */
    function edit_gallery() {

        /* Finding the path to the wp-admin folder */
        $iswin = preg_match('/:\\\/', dirname(__file__));
        $slash = ($iswin) ? "\\" : "/";

        $wp_path = preg_split('/(?=((\\\|\/)wp-content)).*/', dirname(__file__));
        $wp_path = (isset($wp_path[0]) && $wp_path[0] != "") ? $wp_path[0] : $_SERVER["DOCUMENT_ROOT"];

        require_once(WP_PLUGIN_DIR . $slash . dirname(plugin_basename(__FILE__)) . $slash . 'buildgallery.php');

        $svOptions = array();
        $bgOptions = array();

        // Set default options here
        $svOptions['maxImageWidth'] = '800';
        $svOptions['maxImageHeight'] = '600';
        $svOptions['textColor'] = '0xffffff';
        $svOptions['frameColor'] = '0xffffff';
        $svOptions['frameWidth'] = '20';
        $svOptions['thumbColumns'] = '3';
        $svOptions['thumbRows'] = '4';
        $svOptions['thumbPosition'] = 'LEFT';
        $svOptions['title'] = 'SimpleViewer Title';
        $svOptions['showOpenButton'] = 'true';
        $svOptions['showFullscreenButton'] = 'true';
//        $svOptions['languageCode'] = 'AUTO';
        $svOptions['languageList'] = '';
        $svOptions['galleryStyle'] = 'MODERN';
        $bgOptions['addLinks'] = 'true';

        // Flickr options
        if ($_POST['library'] == 'flickr') {
          $svOptions['useFlickr'] = 'true';
          $svOptions['flickrUserName'] = $_POST['flickrUserName'];
          $svOptions['flickrTags'] = $_POST['flickrTags'];
        } else {
          $svOptions['useFlickr'] = 'false';
          $svOptions['flickrUserName'] = '';
          $svOptions['flickrTags'] = '';
        }

        $gallery_filename = $this->get_gallery_path() . $_POST['sv_gallery_id'] . '.xml';

        $postID = $this->getPostID($gallery_filename);

        define('XML_PATH', $gallery_filename);

        // Constants
        define('RELATIVE_IMAGE_PATH', 'images/');
        define('RELATIVE_THUMB_PATH', 'thumbs/');
        define('THUMB_WIDTH', 75);
        define('THUMB_HEIGHT', 75);
        define('THUMB_QUALITY', 85);
        define('BG_BASE_URL', '');
        define('IMAGE_LINK_URL', '');
        define('IMAGE_CAPTION', '');
        define('IMAGE_LINK_TARGET', '');
        //  define('XML_PATH', 'gallery.xml');
        define('BG_VERSION', 'version 2.1.1 build 100113');
        define('MEMORY_LIMIT', 0);
        define('MEMORY_LIMIT_FALLBACK', '8M');
        define('MEMORY_SAFETY_FACTOR', 1.9);
        define('THUMB_DIR_MODE', 0775);
        define('SV_XML_SETTINGS_TAG', 'simpleviewergallery');
        define('OK_CAPTION_TAGS', '<a><b><i><u><font><br><br />');
        define('OK_TITLE_TAGS', '<a><b><i><u><font><br><br />');

        $buildGallery = new BuildGallery($svOptions, $bgOptions);
        $this->updateProOptions($gallery_filename, $_POST['prooptions']);
        $this->updatePostID($gallery_filename, $postID);
    }

    /**
     * Get Post id from XML File
     * @param <type> $gallery_filename
     * @return <type>
     */
    function getPostID($gallery_filename) {
        $post_id = FALSE;
        if (file_exists($gallery_filename)) {

            $domDoc = new DOMDocument();
            $domDoc->load($gallery_filename);

            $settings_tags = $domDoc->getElementsByTagName('simpleviewergallery');
            $settings_tag = $settings_tags->item(0);

            $post_id =  $settings_tag->getAttribute('postID');
        }
        return $post_id;
    }

    /**
     * Set postid to XML file
     * 
     * @param <type> $gallery_filename
     * @param <type> $post_id
     */
    function updatePostID($gallery_filename, $post_id) {
        if (file_exists($gallery_filename)) {

            $domDoc = new DOMDocument();
            $domDoc->load($gallery_filename);

            $settings_tags = $domDoc->getElementsByTagName('simpleviewergallery');
            $settings_tag = $settings_tags->item(0);

            $settings_tag->setAttribute('postID', $post_id);
            $domDoc->save($gallery_filename);
        }
    }

    /**
     * Get the gallery title from the xml file
     *
     * @param <type> $gallery_filename
     * @return <type>
     */
    function getGalleryTitle($gallery_filename) {
        $title = '';
        if (file_exists($gallery_filename)) {

            $domDoc = new DOMDocument();
            $domDoc->load($gallery_filename);

            $settings_tags = $domDoc->getElementsByTagName('simpleviewergallery');
            $settings_tag = $settings_tags->item(0);

            $title =  $settings_tag->getAttribute('title');
        }
        return $title;
    }

    /**
     * Get the directory path where xml files are stored
     *
     * @return <type>
     */
    function get_gallery_path() {
        /* Finding the path to the wp-admin folder */
        $iswin = preg_match('/:\\\/', dirname(__file__));
        $slash = ($iswin) ? "\\" : "/";

        $upload_dir = wp_upload_dir();

        return $upload_dir['basedir'] . $slash;
    }

    /**
     * Get's the list of xml files 
     *
     * @param <type> $directory
     * @return <type>
     */
    function get_all_galleries ($directory) {

        // create an array to hold directory list
        $galleries = array();

        // create a handler for the directory
        $handler = opendir($directory);

        // keep going until all files in directory have been read
        while ($file = readdir($handler)) {

            // if $file isn't this directory or its parent, and is an xml file
            // add it to the results array
            if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'xml')
                $galleries[] = $file;
        }

        // tidy up: close the handler
        closedir($handler);

        function sort_galleries($a, $b) {
            $a1 = intval(basename($a, '.xml'));
            $b1 = intval(basename($b, '.xml'));

            if ($a1 == $b1) {
                return 0;
            }
            return ($a1 > $b1) ? -1 : 1;
        }

        // done!
        usort($galleries, 'sort_galleries');
        return $galleries;

    }
    
    /**
     * Short code handler
     * @param <type> $attr
     * @param <type> $content
     */
    function shortcode_handler($attr, $content) {
        $gallery_id = $attr['gallery_id'];

        $gallery_filename = $this->get_gallery_path() . $gallery_id . '.xml';

        $domDoc = new DOMDocument();
        $domDoc->load($gallery_filename);

        $settings_tags = $domDoc->getElementsByTagName('simpleviewergallery');
        $settings_tag = $settings_tags->item(0);

        $bgcolor = $settings_tag->getAttribute('e_bgColor');
        $width   = $settings_tag->getAttribute('e_g_width');
        $height  = $settings_tag->getAttribute('e_g_height');
        $useFlash= $settings_tag->getAttribute('e_useFlash');

        if ($width == '') {
            $width = '100%';
        }

        if ($height == '') {
            $height = '600px';
        }

        if ($useFlash == '') {
            $useFlash = 'true';
        }

        $upload_dir = wp_upload_dir();
        $gallery_file_url = $upload_dir['baseurl'] . "/$gallery_id.xml";

return <<<EOF
<script type="text/javascript">
var flashvars$gallery_id = {};
flashvars$gallery_id.galleryURL = "$gallery_file_url";

simpleviewer.ready(function () {
    simpleviewer.load("flashContent$gallery_id", "$width", "$height", "$bgcolor", $useFlash, flashvars$gallery_id);
});

</script>
<div id="flashContent$gallery_id" >SimpleViewer requires JavaScript and the Flash Player.
<a href="http://www.adobe.com/go/getflashplayer/">Get Flash.</a></div>
EOF;
    }

    /**
     * Include the common script
     */
    function include_scripts() {
        if ( !is_admin() ) { // instruction to only load if it is not the admin area
           // register your script location, dependencies and version
           wp_register_script('sv_core',
               plugin_dir_url(__FILE__) . 'svcore/js/simpleviewer.js',
               array(),
               $this->version );
           // enqueue the script
           wp_enqueue_script('sv_core');
        }
    }

    /**
     * When the post is saved
     *
     * @param <type> $post_id
     * @return <type>
     */
    function save_postdata($post_id) {
        if ( 'page' == $_POST['post_type'] ) {
            if ( !current_user_can( 'edit_page', $post_id ))
                return $post_id;
        } else {
            if ( !current_user_can( 'edit_post', $post_id ))
                return $post_id;
        }

        if (wp_is_post_revision($post_id)) {
            $post_id = wp_is_post_revision($post_id);
        }
        
        // OK, we're authenticated: we need to find and save the data
        $this->update_gallery($post_id);
    }

    /**
     * Update the gallery
     * 
     * @param <type> $post_id 
     */
    function update_gallery($post_id) {

        $m = array();
        $post = get_postdata($post_id);
        $content = $post['Content'];

        $pattern = get_shortcode_regex();
        preg_match_all('/'.$pattern.'/s', $content, $m);

        $tags = $m[2];
        for ($i = 0 ; $i < count($tags) ; $i++) {
            $tag = $tags[$i];
            if ($tag == 'simpleviewer') {
                $attr = shortcode_parse_atts($m[3][$i]);
                $gallery_id = $attr['gallery_id'];

                $attachments = get_children( array( 'post_parent' => $post_id, 'post_type' => 'attachment', 'orderby' => 'menu_order ASC, ID', 'order' => 'DESC') );
                $gallery_filename = $this->get_gallery_path() . $gallery_id . '.xml';

                if ($attachments) {
                    $this->update_image_tags($gallery_filename, $attachments);
                }

                $this->updatePostID($gallery_filename, $post_id);
            }
        }
    }

    /**
     * Add the image tags to the gallery.xml file
     *
     * @param <type> $gallery_id
     * @param <type> $attachments 
     */
    function update_image_tags($gallery_filename, $attachments) {

        if (file_exists($gallery_filename)) {

            $domDoc = new DOMDocument();
            $domDoc->load($gallery_filename);

            $settings_tags = $domDoc->getElementsByTagName('simpleviewergallery');
            $settings_tag = $settings_tags->item(0);

            $images = $domDoc->getElementsByTagName('image');

            $nodesToBeRemoved = array();

            foreach ($images as $image) {
                $nodesToBeRemoved [] = $image;
            }

            foreach ($nodesToBeRemoved as $node) {
                $domDoc->documentElement->removeChild($node);
            }

            foreach ($attachments as $attachment) {

                $image = wp_get_attachment_image_src($attachment->ID);

                if ($image) {

                    $imageElement = $domDoc->createElement('image');

                    $imageElement->setAttribute('imageURL', $attachment->guid);
                    $imageElement->setAttribute('thumbURL',  $image[0]);
                    $imageElement->setAttribute('linkURL' , $attachment->guid);
                    $imageElement->setAttribute('linkTarget', '_blank');

                    $captionElement = $domDoc->createElement('caption');
                    $captionText = $domDoc->createCDATASection($attachment->post_excerpt);

                    $captionElement->appendChild($captionText);
                    $imageElement->appendChild($captionElement);
                    $settings_tag->appendChild($imageElement);
                }
            }

            $domDoc->save($gallery_filename);
        }
    }

    /**
     * Update Pro options
     *
     * @param <type> $galleryFile
     */
    function updateProOptions($galleryFile, $proOptions) {

        $proOptions = split("\n", $proOptions);

        if (count($proOptions) > 0) {

            $domDoc = new DOMDocument();
            $domDoc->load($galleryFile);

            $settings_tags = $domDoc->getElementsByTagName('simpleviewergallery');
            $settings_tag = $settings_tags->item(0);

            //Save external settings
            if ($_POST['transparentBackground'] == 'true') {
                $bgColor = "transparent";
            } else {
                $bgColor = $_POST['backgroundColor'];
            }

            if ($_POST['useFlash'] == 'true') {
                $useFlash = "true";
            } else {
                $useFlash = "false";
            }

            $settings_tag->setAttribute("e_bgColor", $bgColor);
            $settings_tag->setAttribute("e_g_width", $_POST['gallery_width']);
            $settings_tag->setAttribute("e_g_height", $_POST['gallery_height']);
            $settings_tag->setAttribute("e_useFlash", $useFlash);

            foreach ($proOptions as $proOption) {
                $attrs = split("=", trim($proOption));
                if (count($attrs) == 2) {
                    $value = trim($attrs[1]);
                    $value = str_replace ('\"', "", $value);
                    $value = str_replace ('"', "", $value);
                    $value = str_replace("\\'", "", $value);
                    $value = str_replace("'", "", $value);
                    $value = str_replace("“", "", $value);
                    $value = str_replace("”", "", $value);
                    $settings_tag->setAttribute(trim($attrs[0]), "$value");
                }
            }
            $domDoc->save($galleryFile);
        }
    }
    
    // PHP4 compatibility
    function SimpleViewer() {
        $this->__construct();
    }
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'SimpleViewer' ); function SimpleViewer() { global $SimpleViewer; $SimpleViewer = new SimpleViewer(); }

/**
 * After all plugins are loaded
 */
if (!function_exists('wp_sv_plugins_loaded')) {
    function wp_sv_plugins_loaded() {
        // hook the admin notices action
        add_action( 'admin_notices', 'wp_sv_check_dependency' );
    }
}

/**
 * Check Plugin dependency
 */
if (!function_exists("wp_sv_check_dependency")) {
    function wp_sv_check_dependency() {
        $deactivate = FALSE;

        // check PHP version
        if (version_compare(phpversion(), '5.2', '<')) {
            echo "<div class = 'updated'><p>ERROR! <strong>WP Simple Viewer</strong> Plugin requires at least PHP 5.2. Plugin Deactivated </p></div>";
            $deactivate = TRUE;
        }

        // check if DOM extention is enabled
        if (!class_exists('DOMDocument')) {
            echo "<div class = 'updated'><p>ERROR! <strong>WP Simple Viewer</strong> Plugin requires the DOM extention to be installed. Plugin Deactivated</p></div>";
            $deactivate = TRUE;
        }

        if ($deactivate) {
            deactivate_plugins(__FILE__); // Deactivate ourself

            // add deactivated Plugin to the recently activated list
            $deactivated = array();
            $deactivated[__FILE__] = time();
            update_option('recently_activated', $deactivated + (array)get_option('recently_activated'));
        }
    }
}
// Start this plugin once all other files and plugins are fully loaded
add_action( 'plugins_loaded', 'wp_sv_plugins_loaded');

?>