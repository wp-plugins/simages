=== Simages ===
Contributors: simalam, mikes000
Tags: responsive images, srcset
Requires at least: 3.0
Tested up to: 4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple plugin to manage the responsive image specification for your WordPress images.

== Description ==

A simple plugin to manage the responsive image specification for your WordPress images. By default it will add 2x retina support to all of your image sizes.

Adds scottjehl's Picturefill for a fallback for browsers that don't currently support the specification.

== Installation ==

1. Extract the contents of the zip file to your WordPress plugin directory
2. Activate Plugin
3. Regenerate your thumbnails using your choice of thumbnail regeneration plugin such as: https://wordpress.org/plugins/force-regenerate-thumbnails/
4. Pour yourself a coffee for a job well done

Hooks/Filters

Filter 'simages_add_image_sizes':

This hook allows you to modify the srcset and sizes attributes for each of the image sizes currently active on your WordPress install.
The filter is called for each WordPress image size and passes along 3 variables. First an array containing the current srcset and sizes attributes for the current image size. Second a string of the name of the current image size. Third is the size details of the original image size.

The 'srcset' key in the array contains a little magic. You can simply add the srcset descriptors and the plugin will add all of the appropriate image sizes for you automatically. 2x will result in an image twice as big, 100w will result in an image 100px wide while maintaining the same aspect ratio.

The 'sizes' key will simply be outputted directly onto the image tag.

Example:
`
add_image_size( 'my-header-image', 1920, 800, true );

function add_srcsets($attrs, $image_name, $image_size){
	//different cases for various image sizes
	switch ($image_name) {
	    case 'thumbnail':
	    	//add 1.5x 2x and 3x support to the thumbnail image size
	    	$attrs['srcset'] ='1.5x, 2x, 3x';
	    	break;
	    case 'my-header-image':
	    	//add a more advanced srcset to the my-header-image size
	        $attrs = array(
				'srcset' => '320w, 600w, 800w, 1200w, 1600w, 2880w',
				'sizes' => '(min-width: 603px) 50vw, (min-width: 900px) 33vw, 100vw',
			);
	        break;

	}
	//return the attributes for srcset
	return $attrs;	
}
add_action('simages_add_image_sizes', 'add_srcsets', 10, 3);
`

Filter 'simages_excluded_image_sizes'
This hook allows you to exclude an image size from having a srcset completely.
`
function excluded_image_sizes($excluded_sizes){
	//add to the array of excluded image sizes
	$excluded_sizes[] = 'large';
	return $excluded_sizes;
}
add_action('simages_excluded_image_sizes', 'excluded_image_sizes');
`

== Frequently Asked Questions ==

None yet!


== Changelog ==

= 1.0 =
* The first version of the plugin!