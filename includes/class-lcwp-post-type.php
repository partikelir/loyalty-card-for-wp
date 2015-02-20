<?php
/**
 * Post Type
 *
 * Registers post type
 *
 * @author Nicola Mustone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LoyaltyCardForWP_Post_Type
 */
class LoyaltyCardForWP_Post_Type {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ), 15 );
	}

	public static function register_post_type() {
		$labels = array(
			'name'                => __( 'Loyalty Cards', 'lcwp' ),
			'singular_name'       => __( 'Loyalty Card', 'lcwp' ),
			'add_new'             => _x( 'Add New Card', 'lcwp', 'lcwp' ),
			'add_new_item'        => __( 'Add New Card', 'lcwp' ),
			'edit_item'           => __( 'Edit Card', 'lcwp' ),
			'new_item'            => __( 'New Loyalty Card', 'lcwp' ),
			'view_item'           => __( 'View Loyalty Card', 'lcwp' ),
			'search_items'        => __( 'Search Loyalty Cards', 'lcwp' ),
			'not_found'           => __( 'No Loyalty Cards found', 'lcwp' ),
			'not_found_in_trash'  => __( 'No Loyalty Cards found in Trash', 'lcwp' ),
			'parent_item_colon'   => __( 'Parent Loyalty Card:', 'lcwp' ),
			'menu_name'           => __( 'Loyalty Cards', 'lcwp' ),
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'description'         => 'description',
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 30,
			'menu_icon'           => 'dashicons-id',
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'supports'            => array( 'title', 'custom-fields' )
		);

		register_post_type( 'loyalty_card', $args );
	}
}

LoyaltyCardForWP_Post_Type::init();
