# DIV - The Directory Image Resizer

### Requirements
The server must support .htaccess rewrites and have image GD installed. This has only been tested with PHP 5.4.X, but is likely to work with older versions.

### Example
You can copy the `example` directory to the server to create a simple example of the script (although you may need to adjust permissions of the caching directory). An example is also available online at [http://phillipgooch.com/github-examples/dir/](http://phillipgooch.com/github-examples/dir/). _Please note the online example may not be completely up-to-date._

### Setup
To install DIV you will need to move both `_resizer.php` and `.htaccess` to your intended image directory. Give the directory write permissions or create the intended caching directory and give that write permissions. Before running the script you may also want to look through the options in `_resizer.php` to adjust them to your liking. 

- **`default_reize`** Sets the default resizing method if one is not passed in the URL, these methods are described in the usage section below. 
- **`images_directory`** The directory your using for images, which most likely will be the same directory the `_resizer.php` is in.
- **`cache_directory`** The location the script will cache images. If the directory cannot be found it will attempt to create it. This directory will need write permissions.
- **`thumbnail_file_extension`** The file extension for cached files. If set to `true` the proper extension for the images mime type will be used. If a string is passed that will be used. Settings this to `false` omit the file extension completely.
- **`enable_retina_support`** Sets whether or not to check for the cookie to change images resizing based on pixel ratio. The included `div.js` will need to be included on the page for this feature to work.
- **`show_debug`** Will stop normal functionality and instead display debug information when loading an image directly. This can be useful if you are unable to determine why an image is not resizing properly.

If you want to support HiDPI devices you will need to include the `div.js` script on the page, preferably at the top where it will be able to run before loading any images. This is mere 189bytes and can be concatenated into other scrips. _**Note:** if you leave `enable_retina_support` enabled and include this script you will need to remember to give all images a set width and/or height, otherwise HiDPI users will load larger images that could break site layout (or at the very least make things look bad)._

### Usage
After setup you can continue to use the image directory as normal. When you want to adjust the size of the image you can place the modification pseudo directory before the image path. For example:

mysite.com/images/my-slightly-to-large-image.png  
would become  
mysite.com/images/200x200n/my-slightly-to-large-image.png

or, if using a remote image

mysite.com/images/http://www.theirsite.com/their-images/something-big.jpg  
would become  
mysite.com/images/200x200n/http://www.theirsite.com/their-images/something-big.jpg

The formatting for the pseudo directory is `desired_width` x `desired_height``resize_method`. The `resizing_method` can be one of the following:

- **`d`** : Distort - Distorts the image so it fit exactly in the desired dimensions.
- **`p`** : Padding - Adds a padding around the image so that it is constrained within the desired dimensions and has the exact same final dimensions as desired.
- **`n`** : Nearest - Scales the image so that it fits within the desired dimensions, but does not add any padding to the image, so only the width or height will be exactly the desired dimensions (unless the ratio is the same).
- **`c`** : Crop - The default method, this will resize the image to fit as much as possible in the desired dimensions, keeping the center intact and trimming excess as needed.

### Output
The `_resizer.php` script will save a copy of the resized image locally before outputting it to the browser. The local file will be used for subsequent calls to that image unless the image has been changed (checked with mfiletime and/or md5 depending on whether the image is remote or local). This local file will be located in the specified `cache_directory` and can be cleared at any time.

Image formats for resized images default to JPEG unless the source image was a PNG, a GIF, or the padding resizing method was used, in which case the output will be a PNG. A limitation of the script is that animated images only resize the first frame.

If an image fails to load opening just the image in a new tab may give an error message explaining why. If the message is of no help, or you are not receiving any, you can use `show_debug` information to get more details on the error. This feature should remain off when not in use as it does completely disable normal functionality.

For further support leave a GitHub issue or contact me directly at [phillip.gooch@gmail.com](mailto:phillip.gooch@gmail.com).

### Version History
##### 1.1.1
- Added a check to prevent it from caching images that failed to save (effectively caching the missing image)

##### 1.1.0
- Rewrote script to try and minimize unnecessary processing. 
- Implemented browser side caching and improved cache detection.
- Changed file naming convention.
- Added example files into the project.

##### 1.0.3
- When adding padding to an image it is now transparent, and the file saved as a PNG.

##### 1.0.2
- Minor change to support spaces in file names (even though there probably shouldn't be spaces in your file names).

##### 1.0.1
- Fixed a minor issue that would break compatibility of older version of PHP.
- Added an example page link and fixed the email link

##### 1.0.0
- Initial Release