<?php
/**
 * Loyalty Card for WordPress Formatting
 *
 * Functions for formatting data.
 *
 * @author Nicola Mustone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Clean variables
 *
 * @param string $var
 * @return string
 */
function lcwp_clean( $var ) {
	return sanitize_text_field( $var );
}
