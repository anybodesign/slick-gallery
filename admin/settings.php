<?php defined('ABSPATH') or die(); 


add_action( 'admin_menu', 'any_slkg_add_admin_menu' );
add_action( 'admin_init', 'any_slkg_settings_init' );


function any_slkg_add_admin_menu(  ) { 

	add_options_page( 
		'Slick Gallery', 
		'Slick Gallery', 
		'manage_options', 
		'slick_gallery', 
		'any_slkg_options_page'
	);

}


function any_slkg_settings_init(  ) { 

	add_settings_section(
		'any_slkg_plugin_page_section', 
		__( 'Galleries Settings', 'slick-gallery' ), 
		'any_slkg_settings_section_callback', 
		'any_slkg_plugin_page'
	);
	
	
		// Style and Autoplay
		
		add_settings_field( 
			'any_slkg_style', 
			__( 'Slide or Fade ?', 'slick-gallery' ), 
			'any_slkg_style_render', 
			'any_slkg_plugin_page', 
			'any_slkg_plugin_page_section' 
		);
		register_setting( 'any_slkg_plugin_page', 'any_slkg_style' );
		
		add_settings_field( 
			'any_slkg_auto', 
			__( 'Autoplay', 'slick-gallery' ), 
			'any_slkg_auto_render', 
			'any_slkg_plugin_page', 
			'any_slkg_plugin_page_section' 
		);
		register_setting( 'any_slkg_plugin_page', 'any_slkg_auto' );
		
		add_settings_field( 
			'any_slkg_speed', 
			__( 'Transition speed', 'slick-gallery' ), 
			'any_slkg_speed_render', 
			'any_slkg_plugin_page', 
			'any_slkg_plugin_page_section' 
		);
		register_setting( 'any_slkg_plugin_page', 'any_slkg_speed' );
		

		// Arrows and Dots
		
		add_settings_field( 
			'any_slkg_arrows', 
			__( 'Navigation arrows', 'slick-gallery' ), 
			'any_slkg_arrows_render', 
			'any_slkg_plugin_page', 
			'any_slkg_plugin_page_section' 
		);
		register_setting( 'any_slkg_plugin_page', 'any_slkg_arrows' );
	
	
		add_settings_field( 
			'any_slkg_arrowscolor', 
			__( 'Arrows color', 'slick-gallery' ), 
			'any_slkg_arrowscolor_render', 
			'any_slkg_plugin_page', 
			'any_slkg_plugin_page_section' 
		);
		register_setting( 'any_slkg_plugin_page', 'any_slkg_arrowscolor' );
		
		add_settings_field( 
			'any_slkg_dots', 
			__( 'Pagination dots', 'slick-gallery' ), 
			'any_slkg_dots_render', 
			'any_slkg_plugin_page', 
			'any_slkg_plugin_page_section' 
		);
		register_setting( 'any_slkg_plugin_page', 'any_slkg_dots' );
	
	
		add_settings_field( 
			'any_slkg_dotscolor', 
			__( 'Dots color', 'slick-gallery' ), 
			'any_slkg_dotscolor_render', 
			'any_slkg_plugin_page', 
			'any_slkg_plugin_page_section' 
		);
		register_setting( 'any_slkg_plugin_page', 'any_slkg_dotscolor' );
		
		// Height
		
		add_settings_field( 
			'any_slkg_height', 
			__( 'Adaptive height', 'slick-gallery' ), 
			'any_slkg_height_render', 
			'any_slkg_plugin_page', 
			'any_slkg_plugin_page_section' 
		);
		register_setting( 'any_slkg_plugin_page', 'any_slkg_height' );
		

}


// Style and Autoplay

function any_slkg_style_render(  ) { 

	$options = get_option( 'any_slkg_style', 'false' );
	?>
	<select name='any_slkg_style'>
		<option value='false' <?php selected( $options, 'false' ); ?>>Slide</option>
		<option value='true' <?php selected( $options, 'true' ); ?>>Fade</option>
	</select>

<?php

}


function any_slkg_auto_render(  ) { 

	$options = get_option( 'any_slkg_auto', 1 );
	?>
	<input type='checkbox' name='any_slkg_auto' <?php checked( 1, $options, true ); ?> value='1'> <?php _e('Enable','slick-gallery'); ?>
	<?php
}

function any_slkg_speed_render(  ) { 

	$options = get_option( 'any_slkg_speed', 4000 );
	?>
	<input type='text' name='any_slkg_speed' value='<?php echo $options; ?>' placeholder='4000'>
	<?php
}


// Arrows and Dots 

function any_slkg_arrows_render(  ) { 

	$options = get_option( 'any_slkg_arrows', 1 );
	?>
	<input type='checkbox' name='any_slkg_arrows' <?php checked( 1, $options, true ); ?> value='1'> <?php _e('Enable','slick-gallery'); ?>
	<?php
}

function any_slkg_arrowscolor_render(  ) { 

	$options = get_option( 'any_slkg_arrowscolor', '#000000' );
	?>
	<input type='text' name='any_slkg_arrowscolor' value='<?php echo $options; ?>' placeholder='#CCCCCC'>
	<?php
}

function any_slkg_dots_render(  ) { 

	$options = get_option( 'any_slkg_dots', 1 );
	?>
	<input type='checkbox' name='any_slkg_dots' <?php checked( 1, $options, true ); ?> value='1'> <?php _e('Enable','slick-gallery'); ?>
	<?php
}

function any_slkg_dotscolor_render(  ) { 

	$options = get_option( 'any_slkg_dotscolor', '#000000' );
	?>
	<input type='text' name='any_slkg_dotscolor' value='<?php echo $options; ?>' placeholder='#CCCCCC'>
	<?php
}


// Height

function any_slkg_height_render(  ) { 

	$options = get_option( 'any_slkg_height', 1 );
	?>
	<input type='checkbox' name='any_slkg_height' <?php checked( 1, $options, true ); ?> value='1'> <?php _e('Enable','slick-gallery'); ?>
	<?php
}




function any_slkg_settings_section_callback(  ) { 

	echo __( 'Choose options to customize your slider.', 'slick-gallery' );

}



// The Admin page


function any_slkg_options_page() { 

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	
	?>

<div class="wrap">
		
	<h2><?php echo slkg_NAME; ?></h2>
	
	<form action='options.php' method='post'>

		<?php
		settings_fields( 'any_slkg_plugin_page' );
		do_settings_sections( 'any_slkg_plugin_page' );
		submit_button();
		?>
		
	</form>

	<? /*
	<h3><?php _e('CSS customization','slick-gallery'); ?></h3>			
	<p><?php _e('If you want to customize the design of the galleries, here is the generated code:','slick-gallery'); ?></p>
			
<pre>
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
&lt;div class=&quot;slicky-slides&quot;&gt;
   
   &lt;div class=&quot;slicky-item&quot;&gt;
        &lt;figure class=&quot;slicky-figure&quot;&gt;
        &lt;img src=&quot;your-image.jpg&quot; alt=&quot;image description&quot;&gt;
          &lt;figcaption class=&quot;slicky-caption&quot;&gt;
            Your slide content
          &lt;/figcaption&gt;
        &lt;/figure&gt;
   &lt;/div&gt;
   
&lt;/div&gt;
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
</pre>

	<p><?php _e('Your can override the class you need in your own stylesheet:','slick-gallery'); ?></p>
	<ul>
		<li><code>.slicky-slides</code> : <?php _e('The slider container','slick-gallery'); ?></li>
		<li><code>.slicky-item</code> : <?php _e('The slide','slick-gallery'); ?></li>
		<li><code>.slicky-figure</code> : <?php _e('The image container','slick-gallery'); ?></li>
		<li><code>.slicky-caption</code> : <?php _e('The caption container','slick-gallery'); ?></li>
	</ul>

	*/ ?>

	<h3><?php _e('Credits','slick-gallery'); ?></h3>
	
		<p><?php _e('This plugin is based on Slick, a jQuery plugin by Ken Wheeler. You can visit the official website here: <a href="https://kenwheeler.github.io/slick/" title="Slick official site">https://kenwheeler.github.io/slick/','slick-gallery'); ?></a></p>

		<p><?php echo '<img src="' . slkg_PATH .'/img/anybodesign-logo.svg" width="70" alt="logo anybodesign" style="vertical-align:middle" /> '; ?> 
		<?php _e('Made by <a href="http://anybodesign.com" title="graphic and web design">anybodesign.com</a> :)','slick-gallery'); ?></p>

</div>
	
	<?php

}
