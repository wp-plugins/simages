<?php
class Simages {


	private static $initiated = false;
	private static $excluded = array();

	//a collection of responsive images
	private static $simages = array();

	public static function init() {
		
		// :)

		if ( ! self::$initiated ) {
			self::init_hooks();
		}

	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		self::$initiated = true;

		//add header script
		wp_enqueue_script( 'picturefill', plugins_url( 'simages/js/picturefill.min.js' ), array(), '2.2.0', false );

		//add the picture element script to the header
		add_action('wp_head', array('Simages', 'add_picture_script'), 1);

		add_action('wp_get_attachment_image_attributes', array('Simages', 'add_simage_attrs'), 10, 3 );
		
		add_action('simages_add_image_sizes', array('Simages', 'add_default_2x'), 1, 3);

		self::add_image_sizes();


	}

	public static function add_picture_script(){
		echo '
			<script>
			// Picture element HTML5 shiv
			document.createElement( "picture" );
			</script>
		';
	}

	//returns the image size by name
	private static function get_intermediate_image_size($size){
		global $_wp_additional_image_sizes;

		$new_size = array();

        if ( in_array( $size, array( 'thumbnail', 'medium', 'large' ) ) ) {
            //if standard use theoptions value for the size
            $new_size['width'] = get_option( $size . '_size_w' );
            $new_size['height'] = get_option( $size . '_size_h' );
            $new_size['crop'] = (bool) get_option( $size . '_crop' );


        } elseif ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
        	//use the custom sizes
            $new_size = array( 
                    'width' => $_wp_additional_image_sizes[ $size ]['width'],
                    'height' => $_wp_additional_image_sizes[ $size ]['height'],
                    'crop' =>  $_wp_additional_image_sizes[ $size ]['crop']
            );

        }

        return $new_size;

	}

	//gets all currently setup image sizes
	private static function get_wp_image_sizes(){

		

        $sizes = array();
        $get_intermediate_image_sizes = get_intermediate_image_sizes();

        // Create the full array with sizes and crop info
        foreach( $get_intermediate_image_sizes as $_size ) {

        	$sizes[ $_size ] = self::get_intermediate_image_size($_size);

        }


        return $sizes;

	}

	//adds the simage size for this the given original 
	private static function add_simage_size($original_name, $descriptor, $width, $height, $crop , $sizes){
		$image_name =  $original_name . '-' . $descriptor;

		self::$simages[ $original_name ][] = array(
			'image_name' => $image_name,
			'descriptor' => $descriptor,
			'sizes' => $sizes,
			'width' => $width
		);

		add_image_size( $image_name, $width, $height, $crop );
	}


	//adds the attrs for the srcset
	public static function add_simage_attrs( $attrs, $attachment, $size ){

		//if we have a srcset for this image
		if( array_key_exists($size, self::$simages) && count(self::$simages[ $size ]) ){

			$attrs['srcset'] = '';

			//check to see if we need to add  the original image to the srcset list 
			//the original SRC is ignored when srset with W descriptors are used
			foreach (self::$simages[$size] as $set) {

				if(substr($set['image_name'], -1) == 'w'){

					//get the default image src
					$img_url = wp_get_attachment_image_src( $attachment->ID, $size );

					//get the default image size
					$descriptor = self::get_intermediate_image_size( $size );

					//there is a w present
					$attrs['srcset'] .=  ', ' . $img_url[0]  . ' ' . $descriptor['width'] . 'w';

					// stop this loop!
					break;
				}

			}

			//add the custom image sizes
			foreach (self::$simages[$size] as $set) {
				//get the image src
				$srcset_image = wp_get_attachment_image_src( $attachment->ID, $set['image_name'] );

				//only add the attrs if the widths are the same ( wordpress will use the max image size if size requested does not exist )
				if($srcset_image && ( $set['width'] == $srcset_image[1] ) ){

					$attrs['srcset'] .=  ', ' . $srcset_image[0] . ' ' . $set['descriptor'];

				}


			}

			//always remove that first few characters
			$attrs['srcset'] = substr ($attrs['srcset'], 2); 

			//add the sizes attr
			if($set['sizes' ]){
				$attrs['sizes'] = trim ($set['sizes']);
			}
			
		}


		return $attrs;

	}
	//gets the excluded images
	private static function get_excluded_image_sizes(){
		$excluded = self::$excluded;

		$excluded = apply_filters( 'simages_excluded_image_sizes', $excluded ); 

		return $excluded;

	}

	//adds the 2x image size
	public static function add_default_2x($image_sizes, $size, $values){
		//2x
		$image_sizes = array(
			'srcset' => '2x',
			'sizes' => '',
		);

		return $image_sizes;

	}


	//add additional images values to the default image sizes
	private static function add_image_sizes() {

        $sizes = self::get_wp_image_sizes();

		foreach ($sizes as $size => $values) {
			#add the additional size

			//exclude any excluded image sizes
			if( in_array( $size, self::get_excluded_image_sizes() ) )
				continue;

			$image_sizes = array(
				'srcset' => '',
				'sizes' => '',
			);
			$image_sizes = apply_filters( 'simages_add_image_sizes',$image_sizes, $size, $values );

			if($image_sizes !== false && isset($image_sizes['srcset']) ){

				$found_images=false;
				preg_match_all("/(\d*\.?\d*x)/", $image_sizes['srcset'], $found_images);

				if($found_images[1]){
					//do the ones that match the x pattern
					$srcsets =  array_unique($found_images[1]);

					foreach ($srcsets as $srcset) {
						//grab the multiplier
						$multiplier = preg_replace("/[^0-9,.]/", "", $srcset);
						self::add_simage_size($size, $srcset, $values['width']*$multiplier, $values['height']*$multiplier, $values['crop'], $image_sizes['sizes'] );
					}


				}

				$found_images=false;
				preg_match_all("/(\d+w)/", $image_sizes['srcset'], $found_images);

				if($found_images[1]){
					//do the ones that match the w pattern
					$srcsets =  array_unique($found_images[1]);

					foreach ($srcsets as $srcset) {
						$new_width = preg_replace("/[^0-9,.]/", "", $srcset);
						//get the new height, if its a sub pixel make it smaller, easier to take away from an image than add to it!
						$new_height = floor($values['height'] / $values['width'] * $new_width);

						self::add_simage_size($size, $srcset, $new_width,$new_height, $values['crop'], $image_sizes['sizes']);
					}
				}

				
			} 
			
			

		}//end foreach size


	}
}

?>