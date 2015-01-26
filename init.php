<?php
/*
Plugin Name: Responsive Images
Plugin URI: 
Description: A simple plugin used to help with responsive images
Version: 1.0
Author: Simalam
Author URI: http://simalam.com
Author Email: development@simalam.com
License:
 
	Copyright 2015 Simalam (development@simalam.com)
 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.
 
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
 
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	 
*/


require_once( plugin_dir_path( __FILE__ ) .  'classes/simages.class.php');

add_action( 'init', array( 'Simages', 'init' ) );

?>