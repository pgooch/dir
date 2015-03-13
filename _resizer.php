<?php
/*
	Directory Image Resizer

	Your pal for simple image resizing and caching. Taking control of an entire directory DIR will resize anything you want with a specially formatted URL request. Features
	include support for remotely hosted images (in some cases), caching of resized images, and (with a touch of JavaScript) HiDPI display support (for both mobile and 
	desktop applications).

	Requirements:
		PHP5 or later
		Image GD
		The appropriate .htaccess file in the same directory as this script

	Feb 14 2013 - Phillip Gooch
*/

// Details on the following settings can be found in the readme.md file.
define('default_resize','c'); 
define('images_directory',basename(__DIR__));
define('cache_directory','./_cache');// If the directory is not found it will attempt to create it
define('enable_retina_support',true);// Requires the included javascript to be added to the page.
define('resize_fuzziness_factor',0.11);// The amount it of distortion allowed when resizing, a percentage between 0 and 1
define('thumbnail_file_extension',true);
define('force_jpeg_thumbs',false);// Will force all thumbails to be JPEGs, regardless of transparency.
define('padding_color','255,255,255');// The color of the padding, this will only be used if you are forcing jpegs, otherwise padding is clear. bust be in r,g,b format
define('show_debug',false);// This will prevent the image from loading 

// Determine the mod string (if there is one) and the image
$image = explode(images_directory,$_SERVER['REQUEST_URI'],2);
$image = ltrim($image[1],'/');
$image = urldecode($image);
list($mod,$image)=explode('/',$image.'/',2);

if(preg_match('~([0-9]+)x([0-9]+)([dcpn])?~',$mod)===0){
	// That does not appear to be a valid mod string, we will assume it's an unmodified image and will not do anything.
	$image = $mod.'/'.$image;
	unset($mod); //Cleanup, and it's used later via isset check
}
$image = rtrim($image,'/');

// Check if it is a remote file by looking at the URL structure
if(substr($image,0,5)!='http:' && substr($image,0,6)!='https:'){
	if(!is_file($image)){
		echo 'Unable to access "'.$image.'" from the request of "'.$_SERVER['REQUEST_URI'].'". (local file, 404)';
		exit;
	}else{
		$img['location'] = 'local';
	}
}else{ // File is remote, were going to check with CURL (for greater compatibility)
	$ch=curl_init($image);
	curl_setopt($ch,CURLOPT_NOBODY,true);
	curl_exec($ch);
	$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
	$type=curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
	curl_close($ch);
	if($code!=0){// Some servers don't return a code, in that case we have to assume they sent the image.
		if($code!=200){
			echo 'Unable to access "'.$image.'". (remote file, '.$code.')';
			exit;
		}else{
			if(substr($type,0,5)!='image'){
				echo 'Unable to access "'.$image.'". (remote file, redirected to non image, probable 404)';
				exit;
			}else{
				$img['location'] = 'remote';
			}
		}
	}
}

// Get some info about the image
$details = getimagesize($image);
$img['h'] = $details[1];
$img['w'] = $details[0];
$img['mime'] = $details['mime'];
unset($details);// Cleanup
// These are done it completely different ways for local and remote files
if($img['location']=='local'){
	$img['modified'] = filemtime($image);
	$img['md5'] = md5_file($image);
}else{
	$img['md5'] = md5(file_get_contents($image));
	// And now were going to fake a timestamp
	$img['modified'] = substr(preg_replace('~[^0-9]~','',$img['md5']).'0123456789',0,10); // 0-10 is a safety in case the md5 is somehow all letters.
}

// If we have a mod string there is a lot to do, otherwise we can simply just use the image as is.
if(isset($mod)){

	// Mangle the mod into it's usable parts
	preg_match_all('~([0-9]+)?x([0-9]+)?([dcpn])?~',$mod,$size);
	$mod = array(
		'w' => $size[1][0], // Width
		'h' => $size[2][0], // Height
		't' => $size[3][0]  // Resize Type
	);
	unset($size); // Cleanup

	// Check if retina support is enabled and if the cookie is set, if so pretend we asked a larger image
	if(enable_retina_support){
		if(isset($_COOKIE['dir'])){
			$cookie = json_decode($_COOKIE['dir']);
			$mod['w'] *= round($cookie->pixelRatio);
			$mod['h'] *= round($cookie->pixelRatio);
			unset($cookie); // Cleanup
		}
	}

	// Make sure the mod resize type is there, if not them make it the default
	if(!isset($mod['t']) || trim($mod['t'])==''){
		$mod['t'] = default_resize;
	}

	// Now we can check where the cached file would be
	$cached_path = cache_directory.'/'.substr(preg_replace('~[^A-z0-9]+~','_',$image),0,100).'-'.implode('-',$mod).'-'.$img['modified'].'-'.$img['md5'];
	if(is_string(thumbnail_file_extension)){
		$cached_path .= '.'.thumbnail_file_extension;
	}else if(thumbnail_file_extension){
		$ext = explode('/','/'.$img['mime']);
		$ext = $ext[count($ext)-1];
		if($ext=='jpeg'){$ext='jpg';}
		$cached_path .= '.'.$ext;
		unset($ext); //Cleanup
	}

	// Check the file and continue on as needed.
	if(@is_file($cached_path)){

		// Great news, we have a copy ready to go, we can skip all the resizing
		$image = $cached_path;

	}else{

		// Bad news, we gotta make that image
		// First, lets check the ratios, see if it's within the distortion fuzziness factor.
		$image_ratio = $img['w']/$img['h'];
		$ideal_ratio = $mod['w']/$mod['h'];
		$ratio_diff = abs($image_ratio-$ideal_ratio);
		if($ratio_diff<=resize_fuzziness_factor){
			// So it is getting distorted, we can change the bot type to "d";
			$mod['t'] = 'd';
		}

		// Lets do all the math needed to make the resize
		switch($mod['t']){
			case 'd': // Distort, mangles the image until it fits into the box.
				$resize = array(
					'dst_x' => 0,
					'dst_y' => 0,
					'dst_w' => $mod['w'],
					'dst_h' => $mod['h'],
					'src_x' => 0,
					'src_y' => 0,
					'src_w' => $img['w'],
					'src_h' => $img['h']
				);
			break;
			case 'p': // padding, image will be within size constraints, with transparent around it
				$scale = min($mod['w']/$img['w'],$mod['h']/$img['h']);
				$scaled_w = round($scale*$img['w']);
				$scaled_h = round($scale*$img['h']);
				$resize = array(
					'dst_x' => abs($mod['w']-$scaled_w)/2,
					'dst_y' => abs($mod['h']-$scaled_h)/2,
					'dst_w' => $scaled_w,
					'dst_h' => $scaled_h,
					'src_x' => 0,
					'src_y' => 0,
					'src_w' => $img['w'],
					'src_h' => $img['h']
				);
				unset($scale,$scaled_w,$scaled_w);// Cleaning up
			break; 
			case 'n': // Nerest, image will be within size constraings, but at whatever site is natural
				$scale = min($mod['w']/$img['w'],$mod['h']/$img['h']);
				$mod['w'] = round($scale*$img['w']);
				$mod['h'] = round($scale*$img['h']);
				$resize = array(
					'dst_x' => 0,
					'dst_y' => 0,
					'dst_w' => $mod['w'],
					'dst_h' => $mod['h'],
					'src_x' => 0,
					'src_y' => 0,
					'src_w' => $img['w'],
					'src_h' => $img['h']
				);
				unset($scale);// Cleaning up
			break; 
			case 'c': // Crop, resizes as much as possible, then crops it to fit the shape (default)
			default:
				$scale = max($mod['w']/$img['w'],$mod['h']/$img['h']);
				$crop_y = round((($scale*$img['h'])-$mod['h'])/2);
				$crop_x = round((($scale*$img['w'])-$mod['w'])/2);
				$resize = array(
					'dst_x' => abs($crop_x)*-1,
					'dst_y' => abs($crop_y)*-1,
					'dst_w' => round($scale*$img['w']),
					'dst_h' => round($scale*$img['h']),
					'src_x' => 0,
					'src_y' => 0,
					'src_w' => $img['w'],
					'src_h' => $img['h']
				);
				unset($scale,$crop_x,$crop_y);// Cleaning up
			break;
		}

		// Now we can actually try create the new image
		$new_image = imagecreatetruecolor($mod['w'],$mod['h']);
		$old_image = imagecreatefromstring(file_get_contents($image));
		list($r,$g,$b) = explode(',',padding_color);
		$color = imagecolorallocatealpha($new_image,$r,$g,$b,127);
		imagefill($new_image,1,1,$color);
		imagecopyresampled($new_image,$old_image,$resize['dst_x'],$resize['dst_y'],$resize['src_x'],$resize['src_y'],$resize['dst_w'],$resize['dst_h'],$resize['src_w'],$resize['src_h']);

		// Make sure we have the required directory structure to save
		if(!is_dir(cache_directory)){// check for and make cache dir.
			mkdir(cache_directory);
		}

		// Save that image! (if it's supposed to)
		imagesavealpha($new_image,true);
		if( ($img['mime']=='image/png' || $img['mime']=='image/gif'|| $mod['t']=='p') && force_jpeg_thumbs==false){
			imagepng($new_image,$cached_path);
			$img['mime']='image/png';
		}else{
			imagejpeg($new_image,$cached_path,75);
			$img['mime']='image/jpeg';
		}

		// Now that it's saved it can reference the cached copy.
		$image = $cached_path;
	}
}

// Check if the image is there, if not then it failed to save it, have it error and exit out.
if(!is_file($image)){
	echo 'Unable to find the cached image copy, does the cache directory have write access?.';
	exit;
}

// Output all the headers and image data
header("Last-Modified: ".gmdate("D, d M Y H:i:s",$img['modified'])." GMT");
header("Etag: ".$img['mime']);
header('Cache-Control: public');

// Show the debug information if definition set, this prevents the image form loading.
if(show_debug){
	// Debug information
	echo '<pre>';
	echo 'image: '.$image.'<br/>';
	echo 'img: '.print_r($img,true);
	echo 'mod: '.print_r($mod,true);
	echo 'cached_path: '.$cached_path.'<br/>';
	echo 'cached_found: '.(is_file($cached_path)?'yes':'no').'<br/>';
	echo 'resize: '.@print_r($resize,true);
	exit;
}
// Output the 304 header or the actual image.
if(@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])==$img['modified'] || @trim($_SERVER['HTTP_IF_NONE_MATCH'])==$img['mime']){
   header("HTTP/1.1 304 Not Modified");
   exit;
}
header('Content-Type: '.$img['mime']);
readfile($image);
// Goodbye, and that you for using the Directory Image Resizer