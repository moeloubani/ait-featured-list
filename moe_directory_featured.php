<?php
/*
Plugin Name: AIT Directory Featured Search Plugin
Plugin URI: http://www.moeloubani.com/ait-directory-featured-search-plugin
Description: This plugin adds a featured checkbox to listings and if checked shows them first in search.
Version: 1.0
Author: Moe Loubani
Author URI: http://www.moeloubani.com
*/

// Add meta box in post submit area
function moe_featured_meta_cb( $post )
{
  global $post;
		if (get_post_type($post) == 'ait-dir-item') {
		$values = get_post_custom( $post->ID );
		$check = isset( $values['moe_featured_checkbox'] ) ? esc_attr( $values['moe_featured_checkbox'][0] ) : '';
		wp_nonce_field( 'moe_meta_box_nonce', 'meta_box_nonce' );
		?>
		<div class="misc-pub-section">
			<input type="checkbox" name="moe_featured_checkbox" id="moe_featured_checkbox" <?php checked( $check, 'on' ); ?> />
			<label for="moe_featured_checkbox">Check for featured item.</label>
		</div>
		<?php	
	}
}

add_action( 'post_submitbox_misc_actions', 'moe_featured_meta_cb' );


// Save the check box
function moe_meta_box_save( $post_id )
{
	// Bail if we're doing an auto save
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	
	// dont run if nonce fails
	if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'moe_meta_box_nonce' ) ) return;
	
	// if our current user can't edit this post, bail
	if( !current_user_can( 'edit_post' ) ) return;
	
	// Probably a good idea to make sure your data is set
	// This is purely my personal preference for saving checkboxes
	$chk = ( isset( $_POST['moe_featured_checkbox'] ) && $_POST['moe_featured_checkbox'] ) ? 'on' : 'off';
	update_post_meta( $post_id, 'moe_featured_checkbox', $chk );
}

add_action( 'save_post', 'moe_meta_box_save' );

//Swap theme search with plugin page

function templateRedirect()
{
	if (isset($_GET['dir-search'])) {
    $search_template = dirname( __FILE__ ) . '/themefiles/search.php';
    include $search_template;
    exit;
	}
}
 
// add our function to template_redirect hook
add_action('template_redirect', 'templateRedirect');

function moe_add_js_feat() {
	wp_enqueue_script(
		'moe_featu_plug',
		plugins_url( '/js/script.js' , __FILE__ ),
		array( 'jquery' )
	);
}

add_action( 'wp_enqueue_scripts', 'moe_add_js_feat' );
