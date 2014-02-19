# DIV - The Directory Image Resizer

### Requirements
The server must support .htaccess rewrites and have image GD installed. This has only been tested with PHP 5.4.X, but is likely to work with older versions.

### Setup
To install DIV you will need to move both `_resizer.php` and `.htaccess` to your intended image directory. Give the directory write permissions or create the intended caching directory and give that write permissions. Before running the script you will also want to look through the options in `_resizer.php` to adjust them to your liking. 

-`default_reize` sets the default resizing method if one is not passed in the URL, these methods are described in the user section below. 
-`images_directory` The directory your using for images, which should be the same directory the `_resizer.php` is in.
-`cache_directory` The location the script will store cached images. If the directory can not be found it will attempt to create it. This directory will need write permissions.
-`enable_retina_support` Sets whether or not to check for the cookie to change images resizing based on pixel ratio.

If you want to support HiDpi devices you will need to include the div.js script on the page, preferably at the top where it will be able to run before loading any images. This is mere 189bytes and can be concatenated into other scrips. **Note:** if you leave `enable_retina_support` enabled you will need to remember to give all images set widths and/or heights, otherwise when a retina user comes along and loads the images thats twice as big the browser will attempt to display it at that size, potentially breaking layouts (and generally making things look a mess).

### Usage
After setup you can continue to use the image directory as normal. When you want to adjust the size of the image you can place the modification pseudo directory before the image path. For example:

http://www.myawesomesite.com/images/cool-stuffs/my-slightly-to-large-image.png  
would become  
http://www.myawesomesite.com/images/_200x200n_/cool-stuffs/my-slightly-to-large-image.png

or, if using a remote image

http://www.myawesomesite.com/images/http://www.someoneelsesalmostasawesomesite.com/their-images/something-cool.jpg  
would become  
http://www.myawesomesite.com/images/*200x200n*/http://www.someoneelsesalmostasawesomesite.com/their-images/something-cool.jpg

The formatting for the pseudo directory is `desired_width` x `desired_height` `resize_method`. Either `desired_width` or `desired_height` can be changed out to _auto_, in which case it will only use the remaining dimension when calculating size. The `resizing_method` can be one of the following:

- d : Distort - Distorts the image so it fits exactly in the desired dimensions.
- p : Padding - Adds a transparent padding around the image so that it is constrained within the desired dimensions and has the exact same final dimensions as desired.
- n : Nearest - Scales the image so that it fits within the desired dimensions, but does not add any padding to the image, so only the width or height will be exactly the desired dimensions (unless the ratio is the same).
- c : Crop - The default method, this will resize the image to fit as much as possible in the desired dimensions, keeping the center intact and trimming excess if needed.

### Outputs
The reizer will save a copy of the file locally and output one to the browser. The local file will be used for subsequent calls to that image unless the image has been changed (checked with a simple md5 comparison). This local file will be located in the specified cache directory and can be cleared at any time.

Image formats for resized images default to JPEG unless the source image was a PNG or a GIF, or the Padding resize method was selected, in which case the output will be PNG. A limitation of the resizer is that animated only resize the first frame.

If an image fails to load opening just the image in a new tab may give an error message explaining why. If you still receive a broken image commenting out the output lines (lines 210 and 211 at time of writing) will display some additional information that may help debug the problem. If the problem still isn't clear you can contact me at [phillip.gooch@gmail.com](phillip.gooch@gmail.com) and I'll try to help you the best I can.

### Version History
##### 1.0.0
- Initial Release