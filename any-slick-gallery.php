<?php
/*
Plugin Name: Slick Gallery
Description: Custom output of WordPress native galleries using Slick by Ken Wheeler. 
Plugin URI: https://github.com/anybodesign/slick-galleries/
Version: 1.3
Author: Thomas Villain - Anybodesign
Author URI: https://anybodesign.com/
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
define ('SLKG_VERSION', '1.2');


/* ------------------------------------------
// On activation ----------------------------
--------------------------------------------- */



/* ------------------------------------------
// i18n -------------------------------------
--------------------------------------------- */


load_plugin_textdomain( 'slick-gallery', false, basename( dirname( __FILE__ ) ) . '/languages' );


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

// Default values as vars

$dots = get_option('any_slkg_dots', 1);
if ($dots) { $dotsok = 'true'; } else { $dotsok = 'false'; }
$arrows = get_option('any_slkg_arrows', 1);
if ($arrows) { $arrowsok = 'true'; } else { $arrowsok = 'false'; }
$auto = get_option('any_slkg_auto', 1);
if ($auto) { $autook = 'true'; } else { $autook = 'false'; }
$speed = get_option('any_slkg_speed', 4000);
if ($speed) { $speedok = $speed; } else { $speedok = 4000; }
$slides = get_option('any_slkg_slides', 4);
if ($slides) { $slidesok = $slides; } else { $slidesok = 4; }
$scroll = get_option('any_slkg_scroll', 4);
if ($scroll) { $scrollok = $scroll; } else { $scrollok = 4; }
$style = get_option('any_slkg_style', 'true');
$height = get_option('any_slkg_height', 1);
if ($height) { $heightok = 'true'; } else { $heightok = 'false'; }

print '
<script>
jQuery(document).ready(function() {
	jQuery(".slicky-gallery").slick({
		arrows: '.$arrowsok.',
		dots: '.$dotsok.',
		slidesToShow: '.$slidesok.',
		slidesToScroll: '.$scrollok.',
		autoplay: '.$autook.',
		autoplaySpeed: '.$speedok.',
		fade: '.$style.',		
		adaptiveHeight: '.$heightok.'
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

	wp_register_style(
		'css-slkg', 
	    plugins_url( '/css/slick-gallery.css' , __FILE__ ),
		array(), 
		'1.0', 
		false
	);
	wp_enqueue_style( 'css-slkg' );	
}    
add_action('wp_enqueue_scripts', 'any_slkg_add_css');



function any_slkg_print_css() {

$dotscolor = get_option('any_slkg_dotscolor');
if ($dotscolor) { $dotscolorok = $dotscolor; } else { $dotscolorok = '#999999'; }
$arrowscolor = get_option('any_slkg_arrowscolor');
if ($arrowscolor) { $arrowscolorok = $arrowscolor; } else { $arrowscolorok = '#666666'; }

 
print '<style>
.slicky-gallery .slick-prev:before, .slicky-gallery .slick-next:before {
	color: '.$arrowscolorok.';
}
.slicky-gallery .slick-dots li button:before,
.slicky-gallery .slick-dots li.slick-active button:before {
	color: '.$dotscolorok.';
}
 </style>';
}
add_action('wp_head', 'any_slkg_print_css', 100);



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

function any_slkg_custom_gallery($output, $attr) {

	$post = get_post();

	static $instance = 0;
	$instance++;

	if ( ! empty( $attr['ids'] ) ) {
		if ( empty( $attr['orderby'] ) ) {
			$attr['orderby'] = 'post__in';
		}
		$attr['include'] = $attr['ids'];
	}

	$html5 = current_theme_supports( 'html5', 'gallery' );
	$atts = shortcode_atts( array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post ? $post->ID : 0,
		'captiontag' => $html5 ? 'figcaption' : 'dd',
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => '',
		'link'       => ''
	), $attr, 'gallery' );

	$id = intval( $atts['id'] );

	if ( ! empty( $atts['include'] ) ) {
		$_attachments = get_posts( array( 'include' => $atts['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( ! empty( $atts['exclude'] ) ) {
		$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $atts['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
	} else {
		$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
	}

	if ( empty( $attachments ) ) {
		return '';
	}

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment ) {
			$output .= wp_get_attachment_link( $att_id, $atts['size'], true ) . "\n";
		}
		return $output;
	}


	$captiontag = tag_escape( $atts['captiontag'] );

	$selector = "gallery-{$instance}";
	$size_class = sanitize_html_class( $atts['size'] );
	
	$gallery_div = "<div id='$selector' class='slicky-gallery gallery galleryid-{$id} gallery-size-{$size_class}'>";


	// Output

	$output = $gallery_div;

	$i = 0;
	foreach ( $attachments as $id => $attachment ) {

		$attr = ( trim( $attachment->post_excerpt ) ) ? array( 'aria-describedby' => "$selector-$id" ) : '';
		$image_url = wp_get_attachment_url($id);
		$image_page = get_attachment_link($id);
		$image_meta  = wp_get_attachment_metadata( $id );


		if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] ) {
			
			// Link to file
			$image_output = wp_get_attachment_link( $id, $atts['size'], false, false, false, $attr );
			$caption_output = "
				<a class='' href='$image_url' title='$attachment->post_excerpt'><figcaption class='slicky-caption'><span>
				" . wptexturize($attachment->post_excerpt) . "
				</span></figcaption></a>";
			
		} elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] ) {
			// No link 
			$image_output = wp_get_attachment_image( $id, $atts['size'], false, $attr );
			$caption_output = "
				<figcaption class='slicky-caption'><span>
				" . wptexturize($attachment->post_excerpt) . "
				</span></figcaption>";
		
		} else {
			// Link to attachment page
			$image_output = wp_get_attachment_link( $id, $atts['size'], true, false, false, $attr );
			$caption_output = "
				<a href='$image_page' title='$attachment->post_excerpt'><figcaption class='slicky-caption'><span>
				" . wptexturize($attachment->post_excerpt) . "
				</span></figcaption></a>";
		}
		
		$orientation = '';
		if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
			$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
		}
		
		$output .= "<div class='slicky-item'>";
		$output .= "<figure class='slicky-figure {$orientation}'>";
		$output .= " $image_output ";
		if ( $captiontag && trim($attachment->post_excerpt) ) {
			$output .= " $caption_output ";
		}
		$output .= "</figure></div>";		

	}


	$output .= "
		</div>\n";
	
	//add_thickbox();	
	return $output;
		
}



function any_slkg_add_title_attachment_link( $link, $id = null ) {
	
	$id = intval( $id );
	$_post = get_post( $id );
	$post_title = esc_attr( $_post->post_title );
	$post_caption = esc_attr( $_post->post_excerpt );
	return str_replace('<a href', '<a title="'. $post_caption .'" href', $link);
	
}


add_filter( 'post_gallery', 'any_slkg_custom_gallery', 10, 2 );
add_filter( 'wp_get_attachment_link', 'any_slkg_add_title_attachment_link', 10, 2 );
//add_filter( 'use_default_gallery_style', '__return_false' );