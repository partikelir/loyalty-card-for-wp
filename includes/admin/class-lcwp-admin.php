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
		//add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
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
}

return new LCWP_Admin();
