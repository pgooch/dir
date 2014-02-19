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

// Here are some definitions used throughout the script, all should be clear and straight forward.
define('default_resize','c');// Default resizing method if none is passed (d,c,p, or n)
define('images_directory',basename(__DIR__));// The directory that the images are stored in (this directory most likely)
define('cache_directory','./_cache');// All resized images will be cached here, if this is blank it will disable resize caching
define('enable_retina_support',true);// If set to true and the JavaScript is used it will multiply the image sizes by the pixelRation of the target device, and cache a second copy at that ratio

// Determine the mod string (if there is one) and the image
$image = ltrim(explode(images_directory,$_SERVER['REQUEST_URI'],2)[1],'/');
list($mod,$image)=explode('/',$image.'/',2);
if(preg_match('~([0-9]+)?x([0-9]+)?([dcpn])?~',$mod)===0){
	// That does not appear to be a valid mod string, we will assume it's an unmodified image and will not do anything.
	$image = $mod.'/'.$image;
	unset($mod);
}
$image = rtrim($image,'/');

// Get some info about the image
$details = getimagesize($image);
$img_h = $details[1];
$img_w = $details[0];
$img_mime = $details['mime'];
unset($details);// Cleanup

// Process mod string into it's various bits and do the modding
if(isset($mod)){
	// Mangle the $mod with a regular expression
	preg_match_all('~([0-9]+)?x([0-9]+)?([dcpn])?~',$mod,$size);
	$mod_w = $size[1][0];
	$mod_h = $size[2][0];
	$mod_type = $size[3][0];
	unset($size);// Cleaning up
	// Check if retina support is active, if so adjust the mod sizes accordingly
	if(enable_retina_support){
		if(isset($_COOKIE['dir'])){
			$dir_cookie = json_decode($_COOKIE['dir']);
			$mod_w *= $dir_cookie->pixelRatio;
			$mod_h *= $dir_cookie->pixelRatio;
		}
	}
	// Make sure we have a mod type, even if we have to use the default
	if($mod_type==''){
		$mod_type = default_resize;
	}

	// Determine the cached image path.
	$cached_path = cache_directory.'/';
	$path_building = explode('/',str_ireplace('.','/',$image));
	if($path_building[0]=='http:'||$path_building[0]=='https:'){
		unset($path_building[0]);
		unset($path_building[1]);// Because there are two /'s theres an extra blank one we can drop
		$path_building = array_values($path_building);
		// Get the MD5 for the remote file by simply MD5ing the curl'd data
		$ch=curl_init($image);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$md5 = md5(curl_exec($ch));
		curl_close($ch);

	}else{
		// Get the md5 of the file by simply looking at the file
		$md5 = md5_file($image);
	}
	foreach($path_building as $k => $v){
		$v = preg_replace('~[^a-z0-9]+~','',strtolower($v));
		if($k==0){
			$cached_path .= $v;
		}else if($k==count($path_building)-1){
			if(isset($mod)){
				$cached_path .= '@'.$mod_w.'x'.$mod_h.$mod_type.'.'.$md5;
			}
			$cached_path .= '.'.$v;
		}else if($v!=''){
			$cached_path .= '-'.$v;
		}
	}

	// Now we check to see if the file exists, if it's not local (either because it starts with http:/https: or we checked and the file does not exist locally) we check to see
	// if we can access it, if we can't et the image then we through an error and exit.
	if(substr($image,0,5)!='http:' && substr($image,0,6)!='https:'){
		if(!@is_file($image)){
			echo 'Unable to access "'.$image.'". (local file, 404)';
			//exit;
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
				}
			}
		}
	}

	// Check for the cached image and that the image hasn't been updated since the cache
	if(is_file($cached_path)){
		$image = $cached_path;
		unset($mod); // we already dealt with this, no need to now...
	}else{

		// Determin what were grabbing from the src image and wehre were placing it on the dest image
		switch($mod_type){
			case 'd': // Distort, mangles the image until it fits into the box.
				$dst_x = 0;
				$dst_y = 0;
				$dst_w = $mod_w;
				$dst_h = $mod_h;
				$src_x = 0;
				$src_y = 0;
				$src_w = $img_w;
				$src_h = $img_h;
			break;
			case 'p': // padding, image will be within size constraings, with transparent around it
				$scale = min($mod_w/$img_w,$mod_h/$img_h);
				$scaled_w = round($scale*$img_w);
				$scaled_h = round($scale*$img_h);
				$dst_x = abs($mod_w-$scaled_w)/2;
				$dst_y = abs($mod_h-$scaled_h)/2;
				$dst_w = $scaled_w;
				$dst_h = $scaled_h;
				$src_x = 0;
				$src_y = 0;
				$src_w = $img_w;
				$src_h = $img_h;
				unset($scale,$scaled_w,$scaled_w);// Cleaning up
			break; 
			case 'n': // Nerest, image will be within size constraings, but at whatever site is natural
				$scale = min($mod_w/$img_w,$mod_h/$img_h);
				$mod_w = round($scale*$img_w);
				$mod_h = round($scale*$img_h);
				$dst_x = 0;
				$dst_y = 0;
				$dst_w = $mod_w;
				$dst_h = $mod_h;
				$src_x = 0;
				$src_y = 0;
				$src_w = $img_w;
				$src_h = $img_h;
				unset($scale);// Cleaning up
			break; 
			case 'c': // Crop, resizes as much as possible, then crops it to fit the shape (default)
			default:
				$scale = max($mod_w/$img_w,$mod_h/$img_h);
				$crop_y = round((($scale*$img_h)-$mod_h)/2);
				$crop_x = round((($scale*$img_w)-$mod_w)/2);
				$dst_x = abs($crop_x)*-1;
				$dst_y = abs($crop_y)*-1;
				$dst_w = round($scale*$img_w);
				$dst_h = round($scale*$img_h);
				$src_x = 0;
				$src_y = 0;
				$src_w = $img_w;
				$src_h = $img_h;
				unset($scale,$crop_x,$crop_y);// Cleaning up
			break;
		}

		// Actually create the new image
		$new_image = imagecreatetruecolor($mod_w,$mod_h);
		$old_image = imagecreatefromstring(file_get_contents($image));
		$color = imagecolorallocatealpha($new_image,255,255,255,127);
		imagefill($new_image,1,1,$color);
		imagecopyresampled($new_image,$old_image,$dst_x,$dst_y,$src_x,$src_y,$dst_w,$dst_h,$src_w,$src_h);

		// Make sure we have the required directory structure to save
		if(!is_dir(cache_directory)){// check for and make cache dir.
			mkdir(cache_directory);
		}

		// Save that image! (if it's supposed to)
		imagesavealpha($new_image,true);
		if($img_mime=='image/png' || $img_mime=='image/gif'){
			imagepng($new_image,$cached_path);
			$img_mime='image/png';
		}else{
			imagejpeg($new_image,$cached_path,51);
			$img_mime='image/jpeg';
		}

		// Now that it's saved it can reference the cached copy.
		$image = $cached_path;
	}
}

// Output the image
header('Content-Type: '.$img_mime);
readfile($image);

echo '<br/>';
echo 'img_mime: '.$img_mime.'<br/>';
echo 'image: '.$image.'<br/>';
echo 'cached_path: '.$cached_path.'<br/>';

exit;
// Goodbye, and that you for using the Directory Image Resizer