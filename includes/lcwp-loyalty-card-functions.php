<?php
/**
 * Loyalty Card Functions
 *
 * Functions for loyalty card specific things.
 *
 * @author Nicola Mustone
 */

/**
 * Main function for returning loyalty cards, uses the WC_Product_Factory class.
 *
 * @param mixed $the_loyalty_card Post object or post ID of the loyalty card.
 * @param array $args (default: array()) Contains all arguments to be used to get this loyalty card.
 * @return WC_Product
 */
function lcwp_get_loyalty_card( $the_loyalty_card = false ) {
	return LCWP()->loyalty_card->set_loyalty_card( $the_loyalty_card );
}

/**
 * Return an array with all the existing loyalty cards number.
 *
 * @return array;
 */
function lcwp_get_loyalty_cards_number() {
	global $wpdb;
	return $wpdb->get_col( "SELECT `meta_value` FROM $wpdb->postmeta WHERE `meta_key` = '_card_number' ORDER BY `meta_id` DESC" );
}

function lcwp_create_loyalty_card_number() {
	$existing_cards = array_map( 'absint', lcwp_get_loyalty_cards_number() );

	// Ensure card number is unique
	$new_card_number = uniqid();
	while ( in_array( $new_card_number, $existing_cards ) ) {
		$new_card_number++;
	}

	return $new_card_number;
}

function lcwp_generate_barcode( $post_id ) {
	if ( empty( $post_id ) ) {
		return false;
	}

	$card_number = get_post_meta( $post_id, '_card_number', true );

	if ( ! empty ( $card_number ) ) {
		$js = "$( '#barcode_container-" . $post_id . "' ).barcode( { code: '" . $card_number . "' }, 'code93', { color: '#000000', bgColor: '#ffffff', barWidth: 2, barHeight: 50, fontSize: 14, output: 'bmp' } );";
		ob_start();

		// Set barcode container
		echo '<div id="barcode_container-' . $post_id . '" class="barcode_container"></div>';
		echo '<p class="barcode_number">' . $card_number . '</p>';

		// Run JS for barcode generation
		lcwp_enqueue_js( $js );

		$barcode = ob_get_clean();
		return $barcode;
	}

}
