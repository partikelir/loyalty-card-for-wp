<?php
/**
 * Loyalty Card for WordPress Meta Boxes
 *
 * Sets up the write panels used by loyalty cards (custom post types)
 *
 * @author Nicola Mustone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * LCWP_Admin_Meta_Boxes
 */
class LCWP_Admin_Meta_Boxes {

	private static $saved_meta_boxes = false;
	private static $meta_box_errors  = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 1, 2 );

		// Save Cards Meta Boxes
		add_action( 'lcwp_process_loyalty_card_meta', 'LCWP_Meta_Box_Loyalty_Card_Data::save' );

		// Error handling (for showing errors from meta boxes on next page load)
		add_action( 'admin_notices', array( $this, 'output_errors' ) );
		add_action( 'shutdown', array( $this, 'save_errors' ) );
	}

	/**
	 * Add an error message
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$meta_box_errors[] = $text;
	}

	/**
	 * Save errors to an option
	 */
	public function save_errors() {
		update_option( 'lcwp_meta_box_errors', self::$meta_box_errors );
	}

	/**
	 * Show any stored error messages.
	 */
	public function output_errors() {
		$errors = maybe_unserialize( get_option( 'lcwp_meta_box_errors' ) );

		if ( ! empty( $errors ) ) {

			echo '<div id="lcwp_errors" class="error fade">';

			foreach ( $errors as $error ) {
				echo '<p>' . esc_html( $error ) . '</p>';
			}

			echo '</div>';

			// Clear
			delete_option( 'lcwp_meta_box_errors' );
		}
	}

	/**
	 * Add WC Meta boxes
	 */
	public function add_meta_boxes() {
		add_meta_box( 'lcwp-loyalty-card-data', __( 'Loyalty Card Data', 'lcwp' ), 'LCWP_Meta_Box_Loyalty_Card_Data::output', 'loyalty_card', 'normal', 'high' );
	}

	/**
	 * Remove bloat
	 */
	public function remove_meta_boxes() {
		remove_meta_box( 'postexcerpt', 'product', 'normal' );
		remove_meta_box( 'pageparentdiv', 'product', 'side' );
		remove_meta_box( 'commentstatusdiv', 'product', 'normal' );
		remove_meta_box( 'commentstatusdiv', 'product', 'side' );
		remove_meta_box( 'commentstatusdiv', 'shop_coupon' , 'normal' );
		remove_meta_box( 'slugdiv', 'shop_coupon' , 'normal' );
	}

	/**
	 * Check if we're saving, the trigger an action based on the post type
	 *
	 * @param  int $post_id
	 * @param  object $post
	 */
	public function save_meta_boxes( $post_id, $post ) {
		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) || self::$saved_meta_boxes ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce
		if ( empty( $_POST['lcwp_meta_nonce'] ) || ! wp_verify_nonce( $_POST['lcwp_meta_nonce'], 'lcwp_save_data' ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		self::$saved_meta_boxes = true;

		do_action( 'lcwp_process_loyalty_card_meta', $post_id );
	}
}

new LCWP_Admin_Meta_Boxes();
