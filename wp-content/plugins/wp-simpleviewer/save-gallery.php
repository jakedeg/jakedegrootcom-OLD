<?php
/* Finding the path to the wp-admin folder */
$iswin = preg_match('/:\\\/', dirname(__file__));
$slash = ($iswin) ? "\\" : "/";

$wp_path = preg_split('/(?=((\\\|\/)wp-content)).*/', dirname(__file__));
$wp_path = (isset($wp_path[0]) && $wp_path[0] != "") ? $wp_path[0] : $_SERVER["DOCUMENT_ROOT"];

/** Load WordPress Administration Bootstrap */
require_once($wp_path . $slash . 'wp-load.php');
require_once($wp_path . $slash . 'wp-admin' . $slash . 'admin.php');

load_plugin_textdomain( 'simpleviewer', false, dirname(plugin_basename(__FILE__)) . '/languages' );

require_once(WP_PLUGIN_DIR . $slash . dirname(plugin_basename(__FILE__)) . '/buildgallery.php');

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
//$svOptions['languageCode'] = 'AUTO';
$svOptions['languageList'] = '';
$svOptions['galleryStyle'] = 'MODERN';
$bgOptions['addLinks'] = 'true';

// Flickr options
if ($_POST['library'] == 'wordpress') {
    $svOptions['useFlickr'] = 'false';
} else {
    $svOptions['useFlickr'] = 'true';
    $svOptions['flickrUserName'] = $_POST['flickr_username'];
    $svOptions['flickrTags'] = $_POST['flickr_tag'];
}

$options = get_option('simpleviwer_options');
$options['last_id'] = ($options['last_id'] == "")? "0" : $options['last_id'];
$options['last_id'] = $options['last_id'] + 1;

$upload_dir = wp_upload_dir();
define('XML_PATH', $upload_dir['basedir'] . $slash . $options['last_id'] . '.xml');

update_option('simpleviwer_options', $options);

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
define('BG_VERSION', 'version 2.1.1 build 100113');
define('MEMORY_LIMIT', 0);
define('MEMORY_LIMIT_FALLBACK', '8M');
define('MEMORY_SAFETY_FACTOR', 1.9);
define('THUMB_DIR_MODE', 0775);
define('SV_XML_SETTINGS_TAG', 'simpleviewergallery');
define('OK_CAPTION_TAGS', '<a><b><i><u><font><br><br />');
define('OK_TITLE_TAGS', '<a><b><i><u><font><br><br />');

$buildGallery = new BuildGallery($svOptions, $bgOptions);
updateProOptions(XML_PATH, $_POST['prooptions']);

echo 'gallery_id="' . $options['last_id'] . '"' ;

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
                $value = str_replace ("\\'", "", $value);
                $value = str_replace ('"', "", $value);
                $settings_tag->setAttribute(trim($attrs[0]), $value);
            }
        }
        $domDoc->save($galleryFile);
    }
}
?>