<?php
/**
 * Loyalty Card for WordPress Admin.
 *
 * @author Nicola Mustone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * LCWP_Admin class.
 */
class LCWP_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_footer', 'lcwp_print_js', 25 );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		// Functions
		//include_once( 'wc-admin-functions.php' );
		include_once( 'lcwp-meta-box-functions.php' );

		// Classes
		include_once( 'class-lcwp-admin-post-type.php' );
	}

	/**
	 * Change the admin footer text on WooCommerce admin pages
	 *
	 * @since  2.3
	 * @param  string $footer_text
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		$current_screen = get_current_screen();

		// Check to make sure we're on a WooCommerce admin page
		if ( isset( $current_screen->id ) && apply_filters( 'woocommerce_display_admin_footer_text', in_array( $current_screen->id, wc_get_screen_ids() ) ) ) {
			// Change the footer text
			$footer_text = sprintf( __( 'If you like <strong>Loyalty Card for WordPress</strong> please leave us a <a href="%1$s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating on <a href="%1$s" target="_blank">WordPress.org</a>. A huge thank you from Nicola in advance!', 'woocommerce' ), 'https://wordpress.org/support/view/plugin-reviews/woocommerce?filter=5#postform' );
		}

		return $footer_text;
	}

}

return new LCWP_Admin();
