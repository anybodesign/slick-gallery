<?php
/*
Plugin Name: Slick Gallery
Description: Custom output of WordPress native galleries using Slick by Ken Wheeler. 
Plugin URI: https://github.com/anybodesign/slick-galleries/
Version: 1.0
Author: Thomas Villain - Anybodesign
Author URI: http://anybodesign.com/
License: GPL2
*/

/*
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

defined('ABSPATH') or die('°_°’'); 


/* ------------------------------------------
// Some constants ---------------------------
--------------------------------------------- */


define ('SLKG_PATH', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/' );
define ('SLKG_NAME', 'Slick Gallery');
define ('SLKG_VERSION', '1.0');


/* ------------------------------------------
// On activation ----------------------------
--------------------------------------------- */



/* ------------------------------------------
// i18n -------------------------------------
--------------------------------------------- */


load_plugin_textdomain( 'slick-galleries', false, basename( dirname( __FILE__ ) ) . '/languages' );


/* ------------------------------------------
// Enqueue JS -------------------------------
--------------------------------------------- */


function any_slkg_add_js() {
    if (!is_admin()) {

	    wp_enqueue_script( 
	    	'slick', 
	    	plugins_url( '/js/slick.min.js' , __FILE__ ),
	    	array('jquery'), 
	    	'1.3.15', 
	    	true
	    );
	}
}    
add_action('wp_enqueue_scripts', 'any_slkg_add_js');



function any_slkg_print_script() {

print '
<script>
jQuery(document).ready(function() {
	jQuery(".slicky-gallery").slick({
		arrows: true,
		dots: true,
		slidesToShow: 3,
		slidesToScroll: 3
	});
});
</script>
';
}
add_action('wp_footer', 'any_slkg_print_script', 100);


/* ------------------------------------------
// Enqueue CSS ------------------------------
--------------------------------------------- */


function any_slkg_add_css() {
	
	wp_register_style(
		'slick', 
	    plugins_url( '/css/slick.css' , __FILE__ ),
		array(), 
		'1.3.15', 
		false
	);
	wp_enqueue_style( 'slick' );
}    
add_action('wp_enqueue_scripts', 'any_add_slickg_css');



function any_print_slickg_css() {
 
print '<style>
.slicky-gallery .slick-prev:before, .slicky-gallery .slick-next:before {
	color: #999999;
}
.slicky-gallery .slick-dots li button:before,
.slicky-gallery .slick-dots li.slick-active button:before {
	color: #666666;
}
 </style>';
}
add_action('wp_head', 'any_slkg_add_css', 100);



/* ------------------------------------------
// Admin Options ----------------------------
--------------------------------------------- */
 
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'any_slkg_plugin_settings_link' );

function any_slkg_plugin_settings_link($links) {
	 $mylinks = array(
	 	'<a href="' . admin_url( 'options-general.php?page=slick_gallery' ) . '">'.__('Settings').'</a>', 
	 );
	return array_merge( $links, $mylinks );
}

include( dirname( __FILE__ ) . '/admin/settings.php' );


/* ------------------------------------------
// Gallery Output ---------------------------
--------------------------------------------- */
 
 
remove_shortcode('gallery', 'gallery_shortcode');
add_shortcode('gallery', 'any_custom_gallery');

function any_custom_gallery($attr) {
	$post = get_post();

	static $instance = 0;
	$instance++;

	if ( ! empty( $attr['ids'] ) ) {
		// 'ids' is explicitly ordered, unless you specify otherwise.
		if ( empty( $attr['orderby'] ) )
			$attr['orderby'] = 'post__in';
		$attr['include'] = $attr['ids'];
	}

	// Allow plugins/themes to override the default gallery template.
	$output = apply_filters('post_gallery', '', $attr);
	if ( $output != '' )
		return $output;

	// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] )
			unset( $attr['orderby'] );
	}

	extract(shortcode_atts(array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post ? $post->ID : 0,
		'itemtag'    => 'li',
		'icontag'    => '',
		'captiontag' => 'p',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => '',
		'link'       => ''
	), $attr, 'gallery'));

	$id = intval($id);
	if ( 'RAND' == $order )
		$orderby = 'none';

	if ( !empty($include) ) {
		$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( !empty($exclude) ) {
		$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	} else {
		$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	}

	if ( empty($attachments) )
		return '';

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment )
			$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
		return $output;
	}

	$itemtag = tag_escape($itemtag);
	$captiontag = tag_escape($captiontag);
	$icontag = tag_escape($icontag);
	$valid_tags = wp_kses_allowed_html( 'post' );
	if ( ! isset( $valid_tags[ $itemtag ] ) )
		$itemtag = 'li';
	if ( ! isset( $valid_tags[ $captiontag ] ) )
		$captiontag = 'p';
	if ( ! isset( $valid_tags[ $icontag ] ) )
		$icontag = '';

	$columns = intval($columns);
	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
	$float = is_rtl() ? 'right' : 'left';

	$selector = "gallery-{$instance}";

	$gallery_style = $gallery_div = '';
	if ( apply_filters( 'use_default_gallery_style', true ) )
		$gallery_style = " ";
	$size_class = sanitize_html_class( $size );
	
	$gallery_div = "<div class='slicky-gallery galleryid-{$id}'>";
	
	$output = apply_filters( 'gallery_style', $gallery_style . "\n\t\t" . $gallery_div );

	$i = 0;
	foreach ( $attachments as $id => $attachment ) {
		if ( ! empty( $link ) && 'file' === $link )
			$image_output = wp_get_attachment_link( $id, 'large', false, false );
		elseif ( ! empty( $link ) && 'none' === $link )
			$image_output = wp_get_attachment_image( $id, 'large', false );
		else
			$image_output = wp_get_attachment_link( $id, 'large', true, false );

		$image_meta  = wp_get_attachment_metadata( $id );

		$orientation = '';
		if ( isset( $image_meta['height'], $image_meta['width'] ) )
			$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';

		$output .= "<div class='slicky-item'><figure class='slicky-figure'>";
		$output .= " $image_output ";
		
		if ( $captiontag && trim($attachment->post_excerpt) ) {
			$output .= "
				<figcaption class='slicky-caption'>
				" . wptexturize($attachment->post_excerpt) . "
				</figcaption>";
		}
		
		$output .= "</figure></div>";
	}
		$output .= "</div>";

	return $output;
}


function any_add_title_attachment_link($link, $id = null) {
	$id = intval( $id );
	$_post = get_post( $id );
	$post_title = esc_attr( $_post->post_title );
	$post_caption = esc_attr( $_post->post_excerpt );
	return str_replace('<a href', '<a title="'. $post_caption .'" href', $link);
}
add_filter('wp_get_attachment_link', 'any_add_title_attachment_link', 10, 2);
