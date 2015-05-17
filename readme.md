# DIR - The Directory Image Resizer

## Requirements
The server must support .htaccess rewrites and have image GD installed. This has only been tested with PHP 5.4.X, but is likely to work with older versions.

_Note: This script works under the assumption that all of the format handelers are installed with image GD, if however one is missing (like PNG is missing in the default Max OS 10.10 Yosimite install) then that format will be unavailable for resizing or saving._

## Example
You can copy the `example` directory to the server to create a simple example of the script (although you may need to adjust permissions of the caching directory). An example is also available online at [http://phillipgooch.com/github-examples/dir/](http://phillipgooch.com/github-examples/dir/). _Please note the online example may not be completely up-to-date._

## Setup
To install DIR you will need to move both `_resizer.php` and `.htaccess` to your intended image directory. Give the directory write permissions or create the intended caching directory and give that write permissions. Before running the script you may also want to look through the options in `_resizer.php` to adjust them to your liking. 

##### default_resize `c` 
Sets the default resizing method if one is not passed in the URL, these methods are described in the usage section below. 

##### images_directory `basename(__DIR__)`
The directory your using for images, which most likely will be the same directory the `_resizer.php` is in.

##### cache_directory `./_cache`
The location the script will cache images. If the directory cannot be found it will attempt to create it. This directory will need write permissions.

##### enable_retina_support `true`
Sets whether or not to check for the cookie to change images resizing based on pixel ratio. The included `div.js` will need to be included on the page for this feature to work.

##### resize_fuzziness_factor `0.11`
Determines how much distortion is allowed when resizing. Takes a floating value between 0 and 1 representing the percentage of distortion allowed. To keep old functionality or disable distortion, set to 0. To force distortion on all images set it to 1. Defaults to 0.11 (or 11%).

##### thumbnail_file_extension `true`
The file extension for cached files. If set to `true` the proper extension for the images mime type will be used. If a string is passed that will be used. Settings this to `false` omit the file extension completely.

##### force_jpeg_thumbs `false`
Determines wether or not you want to force the outputed thumbnail to be a JPEG, regardless of what the input format or resize method might be.

##### padding_color `255,255,255`
The color you want to use when padding an image. You will only see then in if the above option is set to true, otherwise transparent padding is added and the images are saved as PNGs.

##### show_debug `false`
Will stop normal functionality and instead display debug information when loading an image directly. This can be useful if you are unable to determine why an image is not resizing properly.

## Usage
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

In the `desired_width` and `desired_height` values in the pseudo directory you may also use the following code letters.

- **`a`** : Auto - The width or height of the image.
- **`f`** : Fullscreen - The horizontal or verticaly resultion of the users screen (the resultion of the users _screen_, not viewport). 

## Output
The `_resizer.php` script will save a copy of the resized image locally before outputting it to the browser. The local file will be used for subsequent calls to that image unless the image has been changed (checked with mfiletime and/or md5 depending on whether the image is remote or local). This local file will be located in the specified `cache_directory` and can be cleared at any time.

Image formats for resized images default to JPEG unless the source image was a PNG, a GIF, or the padding resizing method was used, in which case the output will be a PNG. A limitation of the script is that animated images only resize the first frame.

If an image fails to load opening just the image in a new tab may give an error message explaining why. If the message is of no help, or you are not receiving any, you can use `show_debug` information to get more details on the error. This feature should remain off when not in use as it does completely disable normal functionality.

For further support leave a GitHub issue or contact me directly at [phillip.gooch@gmail.com](mailto:phillip.gooch@gmail.com).

## Notes
Some browsers may not return the correct pixel ratio when requested, for example Chrome on Mac OSX return a pixel ratioof 2 even when the display is scaled (and the real value should be less). I can't see a way to avoid this, however whenever I've noticed it it has always errored on the side of having a larger, more detailed version of the image that needed. If anybody has a simple and efficient way of detecting the proper ratio please drop it in an issue so it can be implemented.

## Version History
#### 1.4.3
- Fixed an issue where switching pixel ratio (like when moving between a HiDPI screen and a normal one) would not be reflected in the images (a pre-switch version would still be loaded).
- Added support for the "a" tag in the resizing pseudo directory to use the image width or height
- Added support for the "f" tag in the resizing pesudo directory to use the users screen resultion width or height.

#### 1.3.3
- Fixed a bug that would cause the script to not load cached files even when they were available.
- Added the option to override the default saving system and always save as a JPEG.
- Added the option to change the color used when padding an image (although inless you are forcing the system to save in JPEG format you will not see the color).
- Made some adjustments to the debugging messages.

#### 1.2.2
- Changed the script from div.js to dir.js, and changed other references to dir.js (not sure where my wires got crossed there).
- Updated the dir.js to include a designated path, preventing it from creating multiple cookies when one 1 is needed.
- Added the resize_fuzziness_factor option allowing it to distort things just a little bit. 

#### 1.1.1
- Added a check to prevent it from caching images that failed to save (effectively caching the missing image)

#### 1.1.0
- Rewrote script to try and minimize unnecessary processing. 
- Implemented browser side caching and improved cache detection.
- Changed file naming convention.
- Added example files into the project.

#### 1.0.3
- When adding padding to an image it is now transparent, and the file saved as a PNG.

#### 1.0.2
- Minor change to support spaces in file names (even though there probably shouldn't be spaces in your file names).

#### 1.0.1
- Fixed a minor issue that would break compatibility of older version of PHP.
- Added an example page link and fixed the email link

#### 1.0.0
- Initial Release