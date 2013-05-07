<?php
/*
Plugin Name: AIT Directory Featured Search Plugin
Plugin URI: http://www.moeloubani.com/ait-directory-featured-search-plugin
Description: This plugin adds a featured checkbox to listings and if checked shows them first in search.
Version: 1.11
Author: Moe Loubani
Author URI: http://www.moeloubani.com
*/

/* THEME OPTIONS PAGE ADD */

//include the main class file
require_once("admin-page-class/admin-page-class.php");
	
/**
 * configure your admin page
 */
$config = array(        
	'menu'             => 'settings',
	'page_title'       => __('Featured Listing Settings','moe_featured_td'),
	'capability'       => 'edit_themes',
	'option_group'     => 'moe_featured_settings', 
	'id'               => 'moe_featured_settings_admin',
	'fields'           => array(),
	'local_images'     => false,
	'use_with_theme'   => false
);    

/**
 * Initiate your admin page
 */
$moe_options_panel = new BF_Admin_Page_Class($config);
$moe_options_panel->OpenTabs_container('');

/**
 * define your admin page tabs listing
 */
$moe_options_panel->TabsListing(array(
	'links' => array(
	'options_1' =>    __('Options','moe_featured_td'),
	)
));

/* Open admin page first tab */
$moe_options_panel->OpenTab('options_1');

/*Add fields admin page */

//title
$moe_options_panel->Title(__("Featured Listings Options","moe_featured_td"));
//Roles checkbox field
$moe_options_panel->addRoles('moe_roles_id',array('type' => 'checkbox_list' ),array('name'=> __('Allowed Packages','moe_featured_td'), 'desc' => __('Turn on the roles you would like to enable featured for.','moe_featured_td')));

$moe_options_panel->CloseTab();

function moe_get_current_role() {
	$moe_user_current_role = wp_get_current_user();
	$moe_plugin_data = get_option('moe_featured_settings');

	$saved_roles = $moe_plugin_data['moe_roles_id'];

	if (!function_exists('moe_get_editable_roles')) {
		function moe_get_editable_roles() {
			global $wp_roles;
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		

		$all_roles = $wp_roles->get_names();
		$editable_roles = apply_filters('editable_roles', $all_roles);

		return $editable_roles;
		}

		$moe_current_roles = moe_get_editable_roles();

		add_action( 'post_submitbox_misc_actions', 'moe_featured_meta_cb' );

		foreach ($moe_current_roles as $moe_rolename => $moe_roleslug) {
			foreach($saved_roles as $saved_role) {
				if (!(($saved_role == $moe_roleslug) && isset($moe_user_current_role->roles[0]) && ($moe_user_current_role->roles[0] == $moe_rolename))) {
						remove_action( 'post_submitbox_misc_actions', 'moe_featured_meta_cb' );
						break 2;
					}
					else { break 2; }
						
				}
		  	}
		}
		
}


add_action('plugins_loaded', 'moe_get_current_role');

// Enqueue scripts

function moe_add_js_feat() {
	wp_enqueue_script(
		'moe_featu_plug',
		plugins_url( '/js/script.js' , __FILE__ ),
		array( 'jquery' )
	);
}

add_action( 'wp_enqueue_scripts', 'moe_add_js_feat' );

// Add meta box in post submit area

function moe_featured_meta_cb( $post )
{
  global $post;
		if (get_post_type($post) == 'ait-dir-item') {
		$values = get_post_custom( $post->ID );
		$check = isset( $values['moe_featured_checkbox'] ) ? esc_attr( $values['moe_featured_checkbox'][0] ) : '';
		wp_nonce_field( 'moe_meta_box_nonce', 'meta_box_nonce' );
		ob_start(); ?>
		<div class="misc-pub-section">
			<input type="checkbox" name="moe_featured_checkbox" id="moe_featured_checkbox" <?php checked( $check, 'on' ); ?> />
			<label for="moe_featured_checkbox"><?php _e("Check for featured item.", 'moe_featured_td');?></label>
		</div>
		<?php  $the_checkbox = ob_get_contents();
		ob_clean();
		echo $the_checkbox;
	}
}

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

function moe_template_redirect()
{
	if (isset($_GET['dir-search'])) {
		$search_template = dirname( __FILE__ ) . '/themefiles/search.php';
		include $search_template;
		exit;
	}/* For category featured work, coming later
	elseif (isset($_GET['dir-item-category'])) {
		$version_dump = wp_get_theme();
		switch ($version_dump->Version) {
			case '2.6': {
				
				$cat_template = dirname( __FILE__ ) . '/themefiles/v26/taxonomy-ait-dir-item-category.php';

				include $cat_template;
				global $wp_query;
				var_dump($wp_query->queried_object);
				exit;
				break;
			}
			case '2.7': {
				$cat_template = dirname( __FILE__ ) . '/themefiles/v27/taxonomy-ait-dir-item-category.php';
				include $cat_template;
				exit;
				break;
			}
			case '2.8': {
				$cat_template = dirname( __FILE__ ) . '/themefiles/v28/taxonomy-ait-dir-item-category.php';
				include $cat_template;
				exit;
				break;
			}
			default: {
				echo '';
			}



		}
	}*/
}
 

// add our function to template_redirect hook
add_action('template_redirect', 'moe_template_redirect');
