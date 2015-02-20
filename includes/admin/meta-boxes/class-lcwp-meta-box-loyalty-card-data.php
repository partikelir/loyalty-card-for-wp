<?php
/**
 * Loyalty Card Data
 *
 * Displays the loylaty card data box
 *
 * @author Nicola Mustone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * LCWP_Meta_Box_Loyalty_Card_Data Class
 */
class LCWP_Meta_Box_Loyalty_Card_Data {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		global $post, $thepostid;

		wp_nonce_field( 'lcwp_save_data', 'lcwp_meta_nonce' );

		$thepostid = $post->ID;
		?>
		<div class="panel-wrap loyalty_card_data">
			<div class="options_group">
				<p class="form-field form-field-wide">
					<?php
					lcwp_wp_hidden_input( array( 'id' => '_card_number' ) );
					?>
				</p>

				<p class="form-field form-field-wide">
					<label for="_owner"><?php _e( 'Owner', 'lcwp' ); ?></label>
					<?php
					$user_string = '';
					$user_id     = absint( get_post_meta( $thepostid, '_owner', true ) );

					if ( ! empty( $user_id ) ) {
						$user        = get_user_by( 'id', $user_id );
						$user_string = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email );
					}
					?>
					<input type="hidden" id="_owner" class="lcwp-user-search" name="_owner" data-placeholder="<?php _e( 'Search for a customer&hellip;', 'lcwp' ); ?>" data-selected="<?php echo esc_attr( $user_string ); ?>" value="<?php echo $user_id; ?>" data-allow_clear="true" />
				</p>
			</div>
			<div class="options_group">
				<div class="form-field form-field-wide">
					<label for="barcode"><?php _e( 'Loyalty card barcode', 'lcwp' ); ?></label>
					<?php echo lcwp_generate_barcode( $thepostid ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Save meta box data
	 */
	public static function save( $post_id ) {
		$card_number = get_post_meta( $post_id, '_card_number', true );

		if ( empty( $card_number ) ) {
			$new_card_number = lcwp_create_loyalty_card_number();
			update_post_meta( $post_id, '_card_number', $new_card_number );
		}

		update_post_meta( $post_id, '_owner', $_POST['_owner'] );
	}
}
