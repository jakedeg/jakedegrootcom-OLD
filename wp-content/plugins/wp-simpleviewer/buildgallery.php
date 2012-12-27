<?php
 /**
  * Airtight Interactive BuildGallery package.
  *
  * SimpleViewer 2 is the free, customizable Flash image viewing application from {@link http://www.airtightinteractive.com/simpleviewer/  Airtight Interactive}
  * Use BuildGallery to create the xml file for SimpleViewer 2
  *
  * @package buildgallery
  * @author Jack Hardie {@link http://www.jhardie.com}
  * @version 2.1.1 build 100113
  * @copyright Copyright (c) 2007 - 2009, Airtight Interactive
  */
//  header('Content-Type: text/html; charset=utf-8');
//  error_reporting(E_ALL);
  
//  $errorHandler = new ErrorHandler();
//  if( version_compare(phpversion(), '5.0', '>=' ) && version_compare(phpversion(), '6.0', '<=' )) @ini_set('zend.ze1_compatibility_mode', '0');
//  $page = new Page();
//  print $page->getHtmlHead();
//  print $page->getPageHeader();
//  if (version_compare(phpversion(), '5.0', '<'))
//  {
//    print '<p style="color: #990000; font-weight: bold">Error: This version of BuildGallery requires PHP 5. Try changing the name of this file from buildgallery.php to buildgallery.php5. If that does not work then contact your server admin/helpdesk and ask how to set-up your account so it defaults to PHP 5.</p>';
//    print $page->getFooter();
//    exit();
//  }
//  ob_start();
  
 /**
  * Main buildgallery class
  *
  */
  Class BuildGallery
  {
   /**
    * @var array SimpleViewer options for xml file.
    */
    var $svOptions = array();
   
   /**
    * @var array BuildGallery options
    */
    var $bgOptions = array();
      
   /**
    * @var array of image objects
    */
    var $imageObjects = array();
    
   /**
    * @var string image directory path rel gallery
    */
    var $imageDirPathRelGallery = RELATIVE_IMAGE_PATH;
    
   /**
    * @var string thumb directory path rel gallery
    */
    var $thumbDirPathRelGallery = RELATIVE_THUMB_PATH;
    
   /**
    * @var integer thumb width in px. Can be overwritten by value from xml
    */
    var $thumbWidth = THUMB_WIDTH;
    
   /**
    * @var integer thumb height in px. Can be overwritten by value from xml
    */
    var $thumbHeight = THUMB_HEIGHT;
    
   /**
    * @var object instance of Xml
    */
    var $xml;


   /**
    * Constructs BuildGallery
    * 
    * @param array preset values held in svOptions
    * @param array preset values held in bgOptions
    */
    function __construct($svOptions, $bgOptions)
    {
      $this->svOptions = $svOptions;
      $this->bgOptions = $bgOptions;
      $this->imageDirPathRelGallery = str_replace('\\/', DIRECTORY_SEPARATOR, RELATIVE_IMAGE_PATH);
      $this->imageDirPathRelGallery = rtrim($this->imageDirPathRelGallery, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
      $this->thumbDirPathRelGallery = str_replace('\\/', DIRECTORY_SEPARATOR, RELATIVE_THUMB_PATH);
      $this->thumbDirPathRelGallery = rtrim($this->thumbDirPathRelGallery, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
      $this->xml = new Xml(XML_PATH, $this->imageDirPathRelGallery, $this->thumbDirPathRelGallery);
      $_POST = $this->rStripSlashes($_POST);
      $_GET = $this->rStripSlashes($_GET);
//      if (isset($_GET['defaults'])) return;
      if (!file_exists(XML_PATH))
      {
        $this->update();
        return;
      }
      if ($this->xml->loadXml())
      {
        $this->imageObjects = $this->xml->parseImageTags();
        $this->update();
        return;
      }
      $att = $this->xml->parseAttributes();
      if (!is_array($att))
      {
        trigger_error('cannot read gallery attributes from xml file, defaults will be used.', E_USER_WARNING);
      }
      else
      {
        if (isset($att['textColor']))
        {
          $att['textColor'] = $this->cleanHex($att['textColor']);
        }
        if (isset($att['frameColor']))
        {
          $att['frameColor'] = $this->cleanHex($att['frameColor']);
        }
        if (isset($att['thumbWidth']))
        {
          $this->thumbWidth = $att['thumbWidth'];
        }
        if (isset($att['thumbHeight']))
        {
          $this->thumbHeight = $att['thumbHeight'];
        }
        // name changes in SV2
        if (!isset($att['thumbPosition']) && isset ($att['navPosition']))
        {
          $att['thumbPosition'] = $att['navPosition'];
        }
        if (!isset($att['thumbColumns']) && isset ($att['thumbNailColumns']))
        {
          $att['thumbColumns'] = $att['thumbNailColumns']; 
        }
        if (!isset($att['thumbRows']) && isset ($att['thumbNailRows']))
        {
          $att['thumbRows'] = $att['thumbNailRows'];
        }
        if (!isset($att['showOpenButton']) && isset($att['enableRightClickOpen']))
        {
          $att['showOpenButton'] = $att['enableRightClickOpen'];
        }
        unset ($att['navPosition'], $att['thumbnailColumns'], $att['thumbnailRows'], $att['enableRightClickOpen'], $att['hAlign'], $att['vAlign'], $att['imagePath'], $att['thumbPath']);
        $this->svOptions = array_merge($this->svOptions, $att);
      }
      $this->imageObjects = $this->xml->parseImageTags();
      $this->update();  
    }
    
   /**
    * Update the properties of BuildGallery from user input
    *
    * @access private
    * @return void
    */
    function update()
    {
//      if (!isset($_POST['customizesubmitted'])) return;
      $this->customize($_POST);
//      $scannedImageObjects = $this->scanImageData();
//      if ($scannedImageObjects === false) return;
//      if ( strtolower(trim($this->bgOptions['addCaptions'])) == 'true' )
//      {
//        $scannedImageObjects = $this->addCaptions($scannedImageObjects);
//      }
//      $newImageObjects = $this->extractNewImageData($scannedImageObjects);
//      $this->imageObjects = array_merge($this->imageObjects, $newImageObjects);
//      $this->imageObjects = $this->removeDeletedImages($this->imageObjects);
//      $this->imageObjects = $this->sortImages($this->imageObjects, $this->bgOptions['sortOrder']);
//      $thumbNails = new ThumbNails($this->thumbWidth, $this->thumbHeight);
//      $thumbCount = $thumbNails->makeThumbs($this->thumbDirPathRelGallery, $this->imageObjects, (strtolower(trim($this->bgOptions['overwriteThumbnails'])) == 'true'));
//      trigger_error('Created '.$thumbCount.' thumbnails.', E_USER_NOTICE);
      $this->xml->writeXml($this->svOptions, $this->bgOptions, $this->imageObjects);
    }
    

   /**
    * Clean form data and update class properties
    *
    * @return array
    * @param array as in preferences file
    */
    function customize($newSettings)
    {
      $newSettings = array_map('trim', $newSettings);
      $this->svOptions['title'] = strip_tags($newSettings['title'], '<a><b><i><u><font><br><br />');
      $this->svOptions['galleryStyle'] = $newSettings['galleryStyle'];
      $this->svOptions['frameWidth'] = max(0, $newSettings['frameWidth']);
      $this->svOptions['thumbColumns'] = max(0, $newSettings['thumbColumns']);
      $this->svOptions['thumbRows'] = max(0, $newSettings['thumbRows']);
      $this->svOptions['thumbPosition'] = $newSettings['thumbPosition'];
      $this->svOptions['maxImageWidth'] = max(0, $newSettings['maxImageWidth']);
      $this->svOptions['maxImageHeight'] = max(0, $newSettings['maxImageHeight']);
      $this->svOptions['showOpenButton'] = isset($newSettings['showOpenButton']) ? 'true' : 'false';
      $this->svOptions['showFullscreenButton'] = isset($newSettings['showFullscreenButton']) ? 'true' : 'false';
      $this->svOptions['textColor'] = $this->cleanHex($newSettings['textColor'], 6);
      $this->svOptions['frameColor'] = $this->cleanHex($newSettings['frameColor'], 6);
      $this->bgOptions['addLinks'] = isset($newSettings['addLinks']) ? 'true' : 'false';

      if ($newSettings['library'] == 'wordpress') {
          $this->svOptions['useFlickr'] = 'false';
          $this->svOptions['flickrUserName'] = '';
          $this->svOptions['flickrTags'] = '';
      } else {
          $this->svOptions['useFlickr'] = 'true';
          $this->svOptions['flickrUserName'] = $newSettings['flickrUserName'];
          $this->svOptions['flickrTags'] = $newSettings['flickrTags'];
      }
      return true;
    }
    
   /**
   * return a properly formatted hex color string
   *
   * @access private
   * @return string
   * @param string containing hex color
   * @param integer required length of hex number in characters
   */
   function cleanHex($hex, $length = 6)
   {
     $hex = strtolower($hex);
     $hex = ltrim($hex, '#');
     $hex = str_replace('0x', '', $hex);
     return '0x'.str_pad(dechex(hexdec(  substr(trim($hex), 0, $length)  )), $length, '0', STR_PAD_LEFT);
   }
  
   /**
    * recursive function to strip slashes
    * see www.php.net/stripslashes strip_slashes_deep function
    *
    * @access public
    * @return array
    * @parameter array
    */
    function rStripSlashes($value)
    {
      if (!get_magic_quotes_gpc()) return $value;
      $value = is_array($value) ? array_map(array($this, 'rStripSlashes'), $value) : stripslashes($value);
      return $value;
    }
     
   /**
    * returns array of image file paths and names
    *
    * @access private
    * @return array
    */
    function scanImageData()
    {
      $imageObjects = array();
      $sortOrder = $this->bgOptions['sortOrder'];
      if (@!is_dir($this->imageDirPathRelGallery))
      {
        trigger_error('the image directory <span class="filename">'.$this->imageDirPathRelGallery.'</span> cannot be found', E_USER_ERROR);
        return false;
      }
      $handle = @opendir($this->imageDirPathRelGallery);
      if ($handle === false)
      {
        trigger_error('cannot open the <span class="filename">'.$this->imageDirPathRelGallery.'</span> directory &ndash; check the file permissions', E_USER_ERROR);
        return false;
      }
      while(false !== ($fileName = readdir($handle)))
      {
      	if (!$this->isImage($fileName)) {continue;}
      	$imageUrl = BG_BASE_URL.str_replace('\\', '/', $this->imageDirPathRelGallery).$fileName;
      	$imageFileInfo = pathinfo($this->imageDirPathRelGallery.$fileName);
        $thumbFileName =  basename($this->imageDirPathRelGallery.$fileName,'.'.$imageFileInfo['extension']).'.jpg';
      	$thumbUrl = BG_BASE_URL.str_replace('\\', '/', $this->thumbDirPathRelGallery).$thumbFileName;
    	  $imageObjects[] = new Image($imageUrl, $thumbUrl, $this->imageDirPathRelGallery);
      }
      closedir($handle);
      $imageObjects = $this->sortImages($imageObjects, $sortOrder);
      return $imageObjects;
    }
    
   /**
    * Extract new image data from scan containing all current image data
    *
    * @access private
    * @return array new image objects
    * @param array old image objects
    * @param array scanned image objects
    */
    function extractNewImageData($scannedImageObjects)
    {
      $oldImageObjects = $this->imageObjects;
      if (count($oldImageObjects) == 0) return $scannedImageObjects;
      $newImageObjects = array();
      foreach ($scannedImageObjects as $key=>$imageObject)
      {
        if($this->fileInImageData($imageObject->getImageFileName(), $oldImageObjects) === false)
        {
          $newImageObjects[] = $imageObject;
        }
      }
      return $newImageObjects;
    }
       
  /** Test if fileName already present in the imageObjects array
    *
    * @access private
    * @return integer array key
    * @param string needle
    * @param array haystack
    */
    function fileInImageData($fileName, $imageObjects)
    {
      foreach ($imageObjects as $key=>$imageObject)
      {
        if ($imageObject->getImageFileName() === $fileName) return $key;
      }
      return false;
    }
    
   /**
    * Remove deleted images
    * Note that thumbs are not deleted. User can delete all thumbs and run buildgallery again.
    *
    * @access private
    * @return array image objects
    * @param string relative path to image directory
    * @param array image objects
    */
    function removeDeletedImages($imageObjects)
    {
      if (count($imageObjects) == 0) return $imageObjects;
      $newImageObjects = array();
      foreach ($imageObjects as $key=>$imageObject)
      {
        if (file_exists($this->imageDirPathRelGallery.$imageObject->getImageFileName()))
        {
          $newImageObjects[] = $imageObject;
        }
      }
      return $newImageObjects;
    }

   
   /**
    * test for jpg
    *
    * Note that preg_match_all returns a number and false for badly formed utf-8
    * version including swf is (0 == preg_match_all('(.*\.((jpe?g)|(swf))$)ui', $fileName, $matches))
    *
    * @return boolean true if filename ends in jpg or jpeg (case insensitive)
    * @parameter string file name
    * @access private
    */
    function isImage($fileName)
    {
      return (0 != preg_match_all('(.*\.((jpe?g)|(png)|(gif))$)ui', $fileName, $matches));
    }
    
    
   /**
    * Sort images
    *
    * @access private
    * @return array sorted image data;
    * @param array to sort
    * @param string sort order ['alpha' | 'ralpha' | 'date' | 'rdate']
    */
    function sortImages($imageObjects, $sortOrder = 'rdate')
    {
      $fileName = array();
      $caption = array();
      $fileMTime = array();
      foreach ($imageObjects as $key => $row)
      {
        $fileName[$key]  = $row->getImageFileName();
        $caption[$key] = $row->getCaption();
        $fileMTime[$key] = @filemtime($this->imageDirPathRelGallery.$fileName[$key]);
        if ($fileMTime[$key] === false)
        trigger_error('cannot read time last modified for <span class="filename">'.$fileName[$key].'</span>', E_USER_WARNING);
      }
      switch($sortOrder)
      {
        case 'alpha':
          array_multisort($fileName, SORT_ASC, $imageObjects);
        break;
        case 'ralpha':
          array_multisort($fileName, SORT_DESC, $imageObjects);
        break;
        case 'date':
          array_multisort($fileMTime, SORT_ASC, SORT_NUMERIC, $imageObjects);
        break;
        case 'rdate':
          array_multisort($fileMTime, SORT_DESC, SORT_NUMERIC, $imageObjects);
        break;
      }
      return $imageObjects;
     }
    
     
    /**
     * Adds captions
     *
     * @access private
     * @return array image data
     * @param array image data
     */
     function addCaptions($imageObjects)
     {
       foreach ($imageObjects as $key => $imageObject)
       {
         $url = $imageObject->getImageUrl();
         $caption = preg_replace('(\.((jpe?g)|(png)|(gif))$)ui', '', $imageObject->getImageFileName());
         $underline = ( strtolower(trim($this->bgOptions['underlineLinks'])) == 'true' );
         $addLinks = ( strtolower(trim($this->bgOptions['addLinks'])) == 'true' );
         if ($addLinks && $underline)
         {
           $caption = '<u>'.$caption.'</u>';
         }
         if ($addLinks)
         {
           $caption = '<a href="'.$url.'">'.$caption.'</a>';
         }
         $imageObjects[$key]->setCaption($caption, OK_CAPTION_TAGS);
       }
       return $imageObjects;
     }
     
    /**
     * get svOptions
     *
     * @access public
     * @return array svOptions
     */
     function getSvOptions()
     {
       return $this->svOptions;
     }
     
    /**
     * get bgOptions
     *
     * @access public
     * @return array bgOptions
     */
     function getBgOptions()
     {
       return $this->bgOptions;
     }

  }
  
 /**
  * Image class
  *
  * @package BuildGallery
  */
  class Image
  {
   /**
    * @var string image url
    */
    var $imageUrl = '';
    
   /**
    * @var string image directory path rel gallery
    */
    var $imageDirPathRelGallery;
    
   /**
    * @var string thumb url
    */
    var $thumbUrl = '';

   /**
    * @var string image caption (without CDATA tags?)
    */
    var $caption = IMAGE_CAPTION;
    
   /**
    * @var string image link url
    */
    var $imageLinkUrl = IMAGE_LINK_URL;
    
   /**
    * @var string image link target
    */
    var $imageLinkTarget = IMAGE_LINK_TARGET;
    
   /**
    * constructs Image class
    * 
    * @param string image url according to path scheme
    * @param string thumb url according to path scheme
    * @param array as generated by getimagesize()
    * @param string image caption
    */
    function __construct($imageUrl, $thumbUrl, $imageDirPathRelGallery, $caption='', $linkUrl='', $linkTarget='')
    {
      $this->imageUrl = $imageUrl;
      $this->thumbUrl = $thumbUrl;
      $this->imageDirPathRelGallery = $imageDirPathRelGallery;
      if ($caption != '') $this->caption = $caption;
      if ($linkUrl != '') $this->imageLinkUrl = $linkUrl;
      if ($linkTarget != '') $this->ImageLinkTarget = $linkTarget;
    }
    
   /**
    * get image path relative to gallery
    *
    * @access public
    * @return string image path
    */
    function getImagePathRelGallery()
    {
      return $this->imageDirPathRelGallery.basename($this->imageUrl);
    }
    
   /**
    * get file name
    *
    * @access public
    * @return string file name
    */
    function getImageFileName()
    {
      return basename($this->imageUrl);
    }

    
   /**
    * get caption
    *
    * @access public
    * @return string
    */
    function getCaption()
    {
      return $this->caption;
    }
    
   /**
    * get image url
    *
    * @access publicc
    * @return string
    */
    function getImageUrl()
    {
      return $this->imageUrl;
    }
    
   /**
    * get image link url
    *
    * @access public
    * @return string
    */
    function getImageLinkUrl()
    {
      return $this->imageLinkUrl;
    }
        
   /**
    * get image attributes
    *
    * @access public
    * @return array image attributes as name=>value
    */
    function getImageAttributes()
    {
      return array(
      'imageURL'=>$this->imageUrl,
      'thumbURL'=>$this->thumbUrl,
      'imageLinkURL'=>$this->imageLinkUrl,
      'imageLinkTarget' => $this->imageLinkTarget
      );
    }
    
   /**
    * Set image caption
    *
    * @access public
    * @return void
    * @param string caption text
    * @param string acceptable html tags
    */
    function setCaption($caption, $okTags)
    {
      $this->caption = strip_tags($caption, $okTags);
    }
  }
  
 /**
  * Creates thumbnails
  *
  * @package BuildGallery
  */
  class ThumbNails
  {
   /**
    * Constructs ThumbNails
    *
    * @param string gallery path rel cwd
    * @param integer thumb width
    * @param integer thumb height
    * @param integer thumb quality
    */
    function __construct($thumbWidth, $thumbHeight)
    {
      $this->thumbWidth = $thumbWidth;
      $this->thumbHeight = $thumbHeight;
      $this->thumbQuality = THUMB_QUALITY;
      // galleryPathRelSvm needed since makeThumbs is an svManager class
      $this->galleryPathRelSvm = '';
    }
    
   /**
    * Loops around the image objects calling createThumb
    * @access public
    * @return integer number of thumbs created
    * @param string thumb path relative to cwd
    * @param array image objects
    * @param boolean overwrite thumbs
    */
    function makeThumbs($thumbPath, $imageObjects, $overwrite = true)
    {
      $thumbCount = 0;
      $memoryLimit = (ini_get('memory_limit') == '') ? MEMORY_LIMIT_FALLBACK : ini_get('memory_limit');
      $maxImageBytes = (MEMORY_LIMIT == 0) ? $this->getBytes($memoryLimit) : MEMORY_LIMIT * pow(2,20);
      $thumbDir = rtrim($thumbPath, '\\/');
      if (!is_dir($thumbDir))
      {
      // Note: mkdir($this->thumbDir, 0777) will not work reliably because of umask (www.php.net/umask)
        if(@!mkdir($thumbDir))
        {
          trigger_error('the thumbnail directory '.$thumbDir.' cannot be created', E_USER_WARNING);
          return 0;
        }
        if (@!chmod($thumbDir, THUMB_DIR_MODE))
        {
          trigger_error('the thumbnail directory '.$thumbDir.' permission may be incorrect &ndash; please see user manual', E_USER_NOTICE);
          return 0;
        }
      }
      $gdVersion = $this->getGDversion();
      if (version_compare($gdVersion, '2.0', '<'))
      {
        trigger_error('the GD imaging library was not found on this server or it is an old version that does not support jpeg images. Thumbnails will not be created. Either upgrade to a later version of GD or create the thumbnails yourself in a graphics application such as Photoshop.', E_USER_NOTICE);
        return 0;
      }
      foreach ($imageObjects as $key=>$imageObject)
      {
        $fileName = $imageObject->getImageFileName();
        $image = $this->galleryPathRelSvm.$imageObject->getImagePathRelGallery();
        $thumb = $thumbPath.$fileName;
        if (@file_exists($thumb) && !$overwrite) {continue;}
        if (@!file_exists($image))
        {
          trigger_error('image '.$image.' cannot be found', E_USER_NOTICE);
          continue;
        }
        if ($this->createThumb($image, $thumb, $this->thumbWidth, $this->thumbHeight, $this->thumbQuality, $maxImageBytes))
        {
          $thumbCount ++;
        }
        else
        {
          trigger_error('Thumbnail for '.$fileName.' could not be created', E_USER_NOTICE);
        }
      }
      return $thumbCount;
    }
    
   /**
    * function createThumb creates and saves one thumbnail image.
    *
    * @access private
    * @return boolean success
    * @param string $filePath path to source image
    * @param string $thumbPath path to new thumbnail
    */
    function createThumb($filePath, $thumbPath, $thumbWidth, $thumbHeight, $thumbQuality, $maxImageBytes)
    {
      if ($thumbWidth <= 0)
      {
        trigger_error('cannot create thumb of zero width', E_USER_WARNING);
        return false;
      }
      if ($thumbHeight <= 0)
      {
        trigger_error('cannot create thumb of zero height', E_USER_WARNING);
        return false;
      }
      $success = false;
      $dimensions = @getimagesize($filePath);
      if ($dimensions === false)
      {
        trigger_error('cannot calculate size of image '.$filePath, E_USER_NOTICE);
      	return false;
      }
      // $imageInfo['channels'] is not set for png images so just guess at 3
      $channels = 3;
      $memoryNeeded = Round(($dimensions[0] * $dimensions[1] * $dimensions['bits'] * $channels / 8 + Pow(2, 16)) * MEMORY_SAFETY_FACTOR);
      if ($memoryNeeded > $maxImageBytes)
      {
      	trigger_error('image '.$filePath.' is too large to create a thumbnail', E_USER_NOTICE);
      	return false;
      }
    	$imageWidth		= $dimensions[0];
    	$imageHeight		= $dimensions[1];
    	if ($dimensions[0] == 0 || $dimensions[1] == 0)
      {
        trigger_error('zero width or height for '.$filePath, E_USER_NOTICE);
        return false;
      }
      $imageAspect = $dimensions[1]/$dimensions[0];
      $thumbAspect = ($thumbWidth == 0) ? 1 : $thumbHeight/$thumbWidth;
      if ($imageAspect >= $thumbAspect)
      {
        // thumbnail is full-width
        $cropWidth = $imageWidth;
        $cropHeight = $imageWidth * $thumbAspect;
        $deltaX = 0;
        $deltaY = ($imageHeight - $cropHeight)/2;
      }
      else
      {
        // thumbnail is full-height
        $cropWidth = $imageHeight / $thumbAspect;
        $cropHeight = $imageHeight;
        $deltaX = ($imageWidth - $cropWidth)/2;
        $deltaY = 0;
      }
      // get image identifier for source image
      switch($dimensions[2])
      {
        case IMAGETYPE_GIF :
          $imageSrc  = @imagecreatefromgif($filePath);
          break;
        case IMAGETYPE_JPEG :
          $imageSrc = @imagecreatefromjpeg($filePath);
          break;
        case IMAGETYPE_PNG :
          $imageSrc = @imagecreatefrompng($filePath);
          break;
        default :
          trigger_error('unidentified image type '.$filePath, E_USER_NOTICE);
          return false;
      }
      if ($imageSrc === false)
      {
        trigger_error('could not get image identifier for '.$filePath, E_USER_NOTICE);
        return false;
      }
      // Create an empty thumbnail image. 
      $imageDest = @imagecreatetruecolor($thumbWidth, $thumbHeight);
      if ($imageDest === false)
      {
        trigger_error('could not create true color image', E_USER_NOTICE);
        return false;
      }
      if(!$success = @imagecopyresampled($imageDest, $imageSrc, 0, 0, $deltaX, $deltaY, $thumbWidth, $thumbHeight, $cropWidth, $cropHeight))
      {
        trigger_error('could not create thumbnail using imagecopyresampled', E_USER_NOTICE);
        @imagedestroy($imageSrc);
  		  @imagedestroy($imageDest);
        return false;
      }
      // save the thumbnail image into a file.
  		if (!$success = @imagejpeg($imageDest, $thumbPath, $thumbQuality))
      {
        trigger_error('could not save thumbnail', E_USER_NOTICE);
      }
  		// Delete both image resources.
  		@imagedestroy($imageSrc);
  		@imagedestroy($imageDest);
      unset ($imageSrc, $imageDest);
    	return $success;
    }

    
   /**
    * Convert ini-style G, M, kbytes to bytes
    * Note that switch statement drops through without breaks
    *
    * @access private
    * @return integer bytes
    * @param string
    */
    function getBytes($val)
    {
      $val = trim($val);
      $last = strtolower($val{strlen($val)-1});
      switch($last)
      {
        case 'g':
          $val *= 1024;
        case 'm':
          $val *= 1024;
        case 'k':
          $val *= 1024;
      }
      return $val;
    }

  
   /**
    * Get which version of GD is installed, if any.
    *
    * @access private
    * @return string version vector or '0' if GD not installed
    */
    function getGdVersion()
    {
      if (! extension_loaded('gd')) { return '0'; }
      // Use the gd_info() function if possible.
      if (function_exists('gd_info'))
      {
        $versionInfo = gd_info();
        preg_match("/[\d\.]+/", $versionInfo['GD Version'], $matches);
        return $matches[0];
      }
      // If phpinfo() is disabled return false...
      if (preg_match('/phpinfo/', ini_get('disable_functions')))
      {
        return '0';
      }
      // ...otherwise use phpinfo().
      ob_start();
      @phpinfo(8);
      $moduleInfo = ob_get_contents();
      ob_end_clean();
      if (preg_match("/\bgd\s+version\b[^\d\n\r]+?([\d\.]+)/i", $moduleInfo,$matches))
      {
        $gdVersion = $matches[1];
      }
      else
      {
        $gdVersion = '0';
      }
      return $gdVersion;
    }
  }
  
 /**
  * Reads and writes xml file
  *
  * @package BuildGallery
  */
  class Xml
  {
   /**
    * Read xml file and parse into structure
    *
    * @access private
    * @returns array of $vals and $index created by xml_parse_into_struct. False if no xml file.
    * @param string path to xml file
    */
    
   /**
    * @var string path to xml file
    */
    var $xmlPath;
            
   /**
    * Constructs Xml
    *
    * @param string path to xml file
    */
    function __construct($xmlPath, $imageDirPathRelGallery, $thumbDirPathRelGallery)
    {
      $this->xmlPath = $xmlPath;
      $this->imageDirPathRelGallery = $imageDirPathRelGallery;
      $this->thumbDirPathRelGallery = $thumbDirPathRelGallery;
      libxml_use_internal_errors(true);
    }
    
   /**
    * Load xml from file and create dom object
    *
    * @access private
    * @return boolean success
    */
    
    function loadXml()
    {
      $xmlSource = @file_get_contents($this->xmlPath);
      if ($xmlSource === false)
      {
        trigger_error('cannot read <span class="filename">'.$this->xmlPath.'</span>, default settings will be used.', E_USER_WARNING);
        return false;
      }
      if ($xmlSource == '')
      {
        trigger_error('xml file is empty, default settings will be used.', E_USER_WARNING);
        return false;
      }
      $this->domDoc = new DOMDocument();
      $loaded = $this->domDoc->loadXML($xmlSource);
      if ($loaded === false)
      {
        $errors = libxml_get_errors();
        foreach ($errors as $error)
        {
          trigger_error('error in xml file, line '.$error->line.'. '.$error->message, E_USER_WARNING);
        }
        libxml_clear_errors();
        return false;
      }
      return true;   
    }
    
   /**
    * Extract attributes of <simpleviewergallery> tag from xml structured array.
    *
    * @access private
    * @return array
    */
    function parseAttributes()
    { 
      $galleryAttributes = array();  
      $galleryTag = $this->domDoc->documentElement;
      if ($galleryTag->hasAttributes())
      {
        foreach ($galleryTag->attributes as $attr)
        {
          $galleryAttributes[$attr->name] = $attr->value;
        }
        return $galleryAttributes;
      }
      return false;
    }
  
   /**
    * Parse xml and create image objects
    *
    * Any empty image tags are silently ignored
    * @access private
    * @return array of image objects
    */
    function parseImageTags()
    {
      $imageObjects = array();
      $galleryTag = $this->domDoc->documentElement;
      $tags = $galleryTag->getElementsByTagName('*');
      $imageTagAttributes = array();
      foreach ($tags as $tag)
      {
        if(strtolower($tag->nodeName) == 'image')
        {
          $imageUrl = '';
          $thumbUrl = '';
          $imageFileName = '';
          $imagePathRelGallery = '';
          $thumbPathRelGallery = '';
          $caption = '';
          $linkUrl ='';
          $linkTarget = '';
          $imageTagAttributes = array();
          if ($tag->hasAttributes())
          {
            foreach ($tag->attributes as $attr)
            {
              $imageTagAttributes[$attr->name] = $attr->value;
            }
            unset($attr);
          }
          if ($tag->hasChildNodes())
          {
            $imageChildren = $tag->getElementsByTagName('*');
            foreach ($imageChildren as $imageChild)
            {
              switch (strtolower($imageChild->nodeName))
              {
                case 'filename' :
                 $imageFileName = $imageChild->nodeValue;
                 break;
                case 'caption' :
                 $caption = $imageChild->nodeValue;
                 break;
              } 
            }
            unset ($imageChild);
          }
          if (isset($imageTagAttributes['imageURL']))
          {
            $baseName = basename($imageTagAttributes['imageURL']);
            if ($baseName != '')
            {
              $imageUrl = $imageTagAttributes['imageURL'];
              $imageFileName = $baseName;
            }
          }
//          if ($imageFileName == '') continue;
//          if ($imageUrl == '') $imageUrl = BG_BASE_URL.str_replace('\\', '/', $this->imageDirPathRelGallery).$imageFileName;
//          $imagePathRelGallery = $this->imageDirPathRelGallery.$imageFileName;
//          $imageSize = @getimagesize($imagePathRelGallery);
//          if ($imageSize == false) continue;
//          $thumbPathRelGallery = $this->thumbDirPathRelGallery.$imageFileName;
//          if (isset($imageTagAttributes['thumbURL']))
//          {
//            $baseName = basename($imageTagAttributes['thumbURL']);
//            if ($baseName != '')
//            {
//              $thumbUrl = $imageTagAttributes['thumbURL'];
//              $thumbFileName = $baseName;
//            }
//            $thumbPathRelGallery = $this->thumbDirPathRelGallery.$thumbFileName;
//          }
//          $imageFileInfo = pathinfo($this->imageDirPathRelGallery.$imageFileName);
//          $thumbFileName =  basename($this->imageDirPathRelGallery.$imageFileName,'.'.$imageFileInfo['extension']).'.jpg';
//          if ($thumbUrl == '') $thumbUrl = BG_BASE_URL.str_replace('\\', '/', $this->thumbDirPathRelGallery).$thumbFileName;
          $linkUrl = isset($imageTagAttributes['linkURL']) ? $imageTagAttributes['linkURL'] : IMAGE_LINK_URL;
          $linkTarget = isset($imageTagAttributes['linkTarget']) ? $imageTagAttributes['linkTarget'] : IMAGE_LINK_TARGET;
          $imageObjects[] = new Image($imageUrl, $thumbUrl, $this->imageDirPathRelGallery, $caption, $linkUrl, $linkTarget);
        }
      }
      return $imageObjects;
    }
  
   /**
    * Construct xml string and write to file
    *
    * @access private
    * @param string file path
    * @param array attributes of simpleviewerGallery tag as they will be written
    * @param string image path relative to gallery
    * @param array image file names and captions
    * @return boolean
    */
    function writeXml($svOptions, $bgOptions, $imageObjects)
    {
      $doc = new DOMDocument();
      $doc->formatOutput = true;
      $root = $doc->createElement(SV_XML_SETTINGS_TAG);
      foreach ($svOptions as $optName => $optValue)
      {
        $root->setAttribute($optName, $optValue);
      }
      $doc->appendChild($root);
      foreach($imageObjects as $imageObject)
      {
        $fileName = $imageObject->getImageFileName();
        $imageElement = $doc->createElement('image');
        $imageAttributes = $imageObject->getImageAttributes();
        foreach ($imageAttributes as $attName=>$attValue)
        {
          $imageElement->setAttribute($attName, $attValue);
        }
        $captionElement = $doc->createElement('caption');
        $caption = $imageObject->getCaption();
        $captionCDATA = $doc->createCDATASection($caption);
        $captionElement->appendChild($captionCDATA);
        $imageElement->appendChild($captionElement);
        $root->appendChild($imageElement);
      }
      if(!@$doc->save($this->xmlPath))
      {
        trigger_error('could not save gallery.xml file.', E_USER_WARNING);
        return false;
      }
      return true;
    }
  }
 
 /**
  * Error handler class
  *
  * @package BuildGallery
  */
  class ErrorHandler
  {
   /**
    * @var array Warning messages that do not stop execution are stored here
    * One warning message per array element
    */
    var $messages=array();
     
   /**
    * Constructs ErrorHandler
    *
    * $this must be passed by reference as below
    * Also $errorhandler = &new ErrorHandler() when the class is instantiated
    */
    function ErrorHandler()
    {
      set_error_handler(array(&$this, 'handleError'));
    }
     
    /**
     * Custom error handler
     *
     * Errors suppressed with @ are not reported error_reporting() == 0
     * @return boolean true will suppress normal error messages
     * @param int error level
     * @param string error message
     * @param string php script where error occurred
     * @param int line number in php script
     * @param array global variables
     */
    function handleError($errLevel, $errorMessage, $errFile, $errLine, $errContext)
    {
      switch($errLevel)
      {
        case E_USER_NOTICE :
          $this->messages[] = array('notice', $errorMessage);
          break;
        case E_NOTICE :
          if (error_reporting() != 0)
          {
            $this->messages[] = array('notice', 'Notice: '.$errorMessage.' (line '.$errLine.')');
          }
          break;
        case E_USER_WARNING :
          $this->messages[] = array('warning', 'Warning: '.$errorMessage.' (line '.$errLine.')');
          break;
        case E_WARNING :
          if (error_reporting() != 0)
          {
            $this->messages[] = array('warning', 'Warning: '.$errorMessage.' (line '.$errLine.')');
          }
          break;
        default :
          $this->messages[] = array('error', 'Error: '.$errorMessage.' (line '.$errLine.')');
      }
      return true;
    }
  
   /**
    * returns user messages
    *
    * @access public
    * @returns string
    */
    function getMessages()
    {
      if (count ($this->messages) == 0) return '';
      $messageHtml = '<ol class="messages">';
      foreach ($this->messages as $message)
      {
        $messageHtml .= '<li class="'.$message[0].'">'.$message[1].'</li>';
      }
      $messageHtml .= '</ol>';    
      return $messageHtml;
    }
  }

 /**
  * Creates html page
  *
  * @package BuildGallery
  */
  class Page
  {
    
   /**
    * Formats html for select form element
    *
    * @access private
    * @param string form element name
    * @param string form element id
    * @param array form element options as value=>option
    * @param string value for selected element
    */
    function htmlSelect($name, $id, $tabindex, $options, $selected)
    {
      $selected = strtolower($selected);
      $html = '';
      $html .= '<select name="'.$name.'" id="'.$id.'" tabindex="'.$tabindex.'">';
      foreach ($options as $value=>$option)
      {
        if (strtolower($value) == $selected)
        {
          $selectString = 'selected="selected"';
        }
        else
        {
          $selectString = '';
        }
        $html .= '<option value="'.$value.'" '.$selectString.'>'.$option.'</option>';
      }
      $html .= '</select>';
      return $html;
    }
    
   /**
    * Returns html header, css styles and page heading
    *
    * @return string
    */
    function getHtmlHead()
    {
      $header = <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>BuildGallery</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <style type="text/css">
      * {
        margin: 0;
        padding: 0;
        outline: none; /* hide dotted outline in Firefox */
      }
      body {
        background-color: #EEEEEE;
        color: #333333;
        font-family: arial, helvetica, sans-serif;
        font-size: 75%;
      }
      #wrapper {
      	width: 100%;
      	color: #333333;
      	background: #EEEEEE;
      }
      #header {
        float: left;
        width: 100%;
        background: #999999;
      }    
      #content {
      	margin: 16px 0 0 20px;
        padding: 24px 0 0 20px;
        min-width: 660px;
      	width: 55em;
      	min-height: 395px;
        border: 1px solid;
        border-color: #EEEEEE #999999 #666666 #999999;
        clear: both;
        float: left;
        display: inline;
        color: #333333;
        background-color: #FFFFFF;
      }
      #footer {
        width: 100%;
        color: #333333;
        background: #EEEEEE;
        height: 46px;
        clear: both;
      }
      #footer p {
        font-size: .8em;
        margin: 0 0 0 41px;
        padding: 1em 0 0 0; 
      }
      #externalnav {
        float: left;
        display: inline;
        margin: 9px 0 0 20px;
        width: 680px;
        height: 50px;
      }
      #externalnav ul {
        float: right;
        list-style: none;
      }
      #externalnav ul li {
        float: left;
        color: #EEEEEE;
      }
      #externalnav ul li a {
        color: #EEEEEE;
        float: left;
        font-weight: normal;
        font-size: 0.9em;
      }
      .clearboth { /* see http://www.pixelsurge.com/experiment/clearers.htm */
      	clear: both;
      	height: 0;
      	margin: 0;
      	font-size: 1px;
      	line-height: 0;
      }
      img {
        border: none;
      }
      h1 {
        font-size: 1.8em;
        font-weight: bold;
        color: #EEEEEE;
      }
      h2 {
        font-size: 1.5em;
        font-weight: bold;
        padding: 13px 0 2px 0;
        color: #666666;
        width: 554px;
      }
      h3 {
        color: #666666;
        margin: 0 0 0.5em 0;
        font-size: 1.2em;
        padding-bottom: 1em;
      }
      h3 a {
        font-weight: normal;
      }
      p, ol li, ul li {
        font-size: 1.2em;
      }
      p {
        margin: 0 0 1em 0;
      }
      ol {
        margin-left: 20px;
      }
      ol.messages {
        margin: 0 0 12px 0;
        width: 618px;
      }
      ol.messages li {
        padding-top: 5px;
        list-style-type: none;
        background-color: #FFFFFF;
      }
      ol.messages li.notice {
        color: #0000AA;
      }
      ol.messages li.warning {
        color: #990000;
      }
      ol.messages li.error {
        color: #990000;
      }

      pre {
        font-size: 12px;
      }
      em {
        font-style: italic;
        color: #BB0000;
      }
      .filename {
        font-style: italic;
      }
      a:link, a:visited {
        color: #3c5c7c;
      }
      a:hover {
        color: #6699CC;
      }
      table {
        font-size: 1.2em;
        padding-bottom: 1em;
      }
      th {
        font-weight: bold;
        text-align: left;
        vertical-align: top;
        white-space: nowrap;
        padding: 0 0 0.5em 0;
      }
      #settings1 {
        width: 634px;
      }
      #settings2 {
        width: 303px;
        float: left;
      }
      #settings3 {
        width: 303px;
        float: right;
        display: inline;
        margin-right: 30px;
      }
      td {
        padding: 0 0 5px 0;
        line-height: 20px;
        height: 32px;
        vertical-align: top;
      }
      td.label {
        width: 142px;
      }
      select {
        width: 148px;
        height: 22px;
      }
      form, form input, form checkbox, form select {
        font-size: 1em;
      }
      input.text, input.colorpicker, select {
        border: 1px solid;
        border-color: #666666 #CCCCCC #EEEEEE #CCCCCC;
      }
      input {
        padding: 3px;
      }
      input.formbutton {
        width: 5em;
        height: 2em;
      }
      input.checkbox {
        width: 20px;
        min-height: 20px;
      }
      label {
        cursor: pointer;
      }
      input.text, input.colorpicker {
        width: 140px;
      }
      #title {
        width: 467px;
      }
    </style>
    <!--[if lte IE 6]>
      <style type="text/css">
        #content {
          width: 660px;
          height: 395px;
        }
      </style>
    <![endif]-->
    <script type="text/javascript">
      /* <![CDATA[ */
        window.onload = function ()
        {
          resetButton = document.getElementById("reset");
          resetButton.onclick = function()
          {
            window.location = self.location.protocol + '//' + self.location.host + self.location.pathname;
          };
          if (!document.getElementsByTagName) return;
          var anchors = document.getElementsByTagName("a");
          for (var i=0; i<anchors.length; i++)
          {
            var anchor = anchors[i];
            if (anchor.getAttribute("href") && anchor.getAttribute("rel") == "external") anchor.target = "_blank";
            if (anchor.getAttribute("href") && anchor.getAttribute("rel") == "gallery") anchor.target = "gallery";
          }
        };	
      /* ]]> */
    </script>
  </head>

EOD;
    return $header;
  }
 
   /**
    * Returns opening html for page body
    *
    * @return string
    */
    function getPageHeader()
    {
      $pageHeader = <<<EOD
  <body>
    <div id="wrapper">
      <div id="header">
        <div id="externalnav">
          <ul>
            <li id="view"><a href="index.html" rel="gallery" title="View gallery in new window/tab">View gallery</a>&nbsp;&nbsp;|&nbsp;&nbsp;</li>
            <li id="help"><a href="http://www.simpleviewer.net/simpleviewer/support/buildgallery.html" rel="external" title="View Buildgallery help page in new window/tab">Help</a>&nbsp;&nbsp;|&nbsp;&nbsp;</li>
            <li id="defaults"><a href="{$_SERVER['PHP_SELF']}?defaults=true" title="Load default settings">Default settings</a>&nbsp;&nbsp;|&nbsp;&nbsp;</li>
            
            <li id="upgrade"><a href="http://www.simpleviewer.net/svmanager/" rel="external" title="svManager offers many more features">Upgrade to svManager</a></li>
          </ul>
          <h1>BuildGallery for SV2</h1>
        </div>       
      </div>
      <br class="clearboth" />
      <div id="content">
EOD;
      return $pageHeader;
    }
  
   /** Returns page content
    *
    * @return string
    */
    function getPageContent($attributes, $bgOptions)
    {
      $addLinksChecked = (trim(strtolower($bgOptions['addLinks'])) == 'true') ? 'checked="checked"' : '';
      $showOpenButtonChecked = (trim(strtolower($attributes['showOpenButton'])) == 'true') ? 'checked="checked"' : '';
      $showFullscreenButtonChecked = (trim(strtolower($attributes['showFullscreenButton'])) == 'true') ? 'checked="checked"' : '';
      $addCaptionsChecked = (trim(strtolower($bgOptions['addCaptions'])) == 'true') ? 'checked="checked"' : '';
      $overwriteThumbnailsChecked = (trim(strtolower($bgOptions['overwriteThumbnails'])) == 'true') ? 'checked="checked"' :'';
      $underlineLinksChecked = (trim(strtolower($bgOptions['underlineLinks'])) == 'true') ? 'checked="checked"' :'';
      $textColor = str_replace('0x', '', $attributes['textColor']);
      $frameColor = str_replace('0x', '', $attributes['frameColor']);
      $options = array("MODERN"=>'Modern', "CLASSIC"=>'Classic',"COMPACT"=>'Compact');
      $galleryStyleHtml = $this->htmlSelect('galleryStyle', 'gallerystyle', 1, $options, $attributes['galleryStyle']);
      $options = array("TOP"=>'Top', "BOTTOM"=>'Bottom', "LEFT"=>'Left',"RIGHT"=>'Right', "NONE"=>'None');
      $thumbPositionHtml = $this->htmlSelect('thumbPosition', 'thumbposition', '8', $options, $attributes['thumbPosition']);
      $options = array('alpha'=>'file name A&hellip;z', 'ralpha'=>'file name Z&hellip;a', 'date'=>'oldest first', 'rdate'=>'newest first');
        $sortOrderHtml = $this->htmlSelect('sortOrder', 'sortorder', '11', $options, $bgOptions['sortOrder']);
  
      $html = <<<EOD
  
        <form class="public" action = "{$_SERVER['PHP_SELF']}" id="customizeform" method="post">
          <table id="settings1" cellspacing="0">
            <tr id="titleentry">
              <td class="label"><label for="title">Gallery title</label></td><td><input type="text" id="title" tabindex="1" class="text" name="title" value="{$attributes['title']}" /></td>
            </tr>
          </table>
        
          <table id="settings2" cellspacing="0">
            <tr id="gallerystyleentry">
              <td class="label"><label for="gallerystyle">Gallery style</label></td><td>{$galleryStyleHtml}</td>
            </tr>
            <tr id="thumbpositionentry">
              <td class="label"><label for="thumbposition">Thumb position</label></td><td>{$thumbPositionHtml}</td>
            </tr>
            <tr id="sortorderentry">
              <td class="label"><label for="sortorder">Sort order</label></td><td>{$sortOrderHtml}</td>
            </tr>
            <tr id="textcolorentry">
              <td class="label"><label for="textcolor">Text color</label></td><td><input type="text" id="textcolor" tabindex="5" class="colorpicker"  name="textColor" value="{$textColor}" /></td>
            </tr>
            <tr id="framecolorentry">
              <td class="label"><label for="framecolor">Frame color</label></td><td><input type="text" id="framecolor" tabindex="6" class="colorpicker" name="frameColor" value="{$frameColor}" /></td>
            </tr>
            <tr id="framewidthentry">
              <td class="label"><label for="framewidth">Frame width, px</label></td><td><input type="text" id="framewidth" tabindex="12" class="text" name="frameWidth" value="{$attributes['frameWidth']}" /></td>
            </tr>
            <tr id="thumbrowsentry">
              <td class="label"><label for="thumbrows">Thumbnail rows</label></td><td><input type="text" id="thumbrows" tabindex="1" class="text" name="thumbRows" value="{$attributes['thumbRows']}" /></td>
            </tr>
            <tr id="thumbcolumnsentry">
              <td class="label"><label for="thumbcolumns">Thumbnail columns</label></td><td><input type="text" id="thumbcolumns" tabindex="1" class="text" name="thumbColumns" value="{$attributes['thumbColumns']}" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
            </tr>
          </table>
        
          <table id="settings3" cellspacing="0">
            <tr id="maximagewidthentry">
              <td class="label"><label for="maximagewidth">Max image width, px</label></td><td><input type="text" id="maximagewidth" tabindex="15" class="text" name="maxImageWidth" value="{$attributes['maxImageWidth']}" /></td>
            </tr>
            <tr id="maximageheightentry">
              <td class="label"><label for="maximageheight">Max image height, px</label></td><td><input type="text" id="maximageheight" tabindex="16" class="text" name="maxImageHeight" value="{$attributes['maxImageHeight']}" /></td>
            </tr>
            <tr id="addcaptionssentry">
              <td class="label"><label for="addcaptions">Add captions</label></td><td><input type="checkbox" class="checkbox" id="addcaptions" tabindex="17" {$addCaptionsChecked} name="addCaptions" /></td>
            </tr>
            <tr id="addlinksentry">
              <td class="label"><label for="addlinks">Add caption links</label></td><td><input type="checkbox" class="checkbox" id="addlinks" tabindex="18" {$addLinksChecked} name="addLinks" /></td>
            </tr>
            <tr id="underlinelinkssentry">
              <td class="label"><label for="underlinelinks">Underline links</label></td><td><input type="checkbox" class="checkbox" id="underlinelinks" tabindex="19" {$underlineLinksChecked} name="underlineLinks" /></td>
            </tr>
            <tr id="showopenbuttonentry">
              <td class="label"><label for="showopenbutton">Open button</label></td><td><input type="checkbox" class="checkbox" id="showopenbutton" tabindex="20" {$showOpenButtonChecked} name="showOpenButton" /></td>
            </tr>
            <tr id="showfullscreenbuttonentry">
              <td class="label"><label for="showfullscreenbutton">Fullscreen button</label></td>
              <td colspan="2"><input type="checkbox" class="checkbox" id="showfullscreenbutton" tabindex="17" {$showFullscreenButtonChecked} name="showFullscreenButton" /></td>
            </tr>
            <tr id="overwritethumbnailsentry">
              <td class="label"><label for="overwritethumbnails">Overwrite thumbnails</label></td><td><input type="checkbox" class="checkbox" id="overwritethumbnails" tabindex="21" {$overwriteThumbnailsChecked} name="overwriteThumbnails" /></td>
            </tr>
            <tr id="submitreset">
              <td colspan="2"><input type="hidden" name="customizesubmitted" value="true" /><input type="submit" name="submit" class="formbutton" value="Update" />&nbsp;<input type="reset" id="reset" value="Reset" class="formbutton" /></td>
            </tr>
          </table>
        </form>
EOD;
      return $html;
    }

    
   /**
    * Returns closing html tags
    *
    * @return string
    */
    function getFooter()
    {
      $bgVersionString = BG_VERSION;
      $phpVersionString = phpversion();
      $safeMode = (@ini_get("safe_mode") == 'On') || (@ini_get("safe_mode") == 1) ? 'on' : 'off';
      $footer = <<<EOD

      </div>
      <div id="footer">
        <p>&copy; 2007&ndash;2009 Airtight Interactive. BuildGallery, {$bgVersionString}. PHP {$phpVersionString}, safe mode {$safeMode}.</p> 
      </div>
    </div>
  </body>
</html>
EOD;
    return $footer;
    }
  }
?>