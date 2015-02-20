<?php
include_once 'lcwp-formatting-functions.php';
include_once 'lcwp-loyalty-card-functions.php';
include_once 'lcwp-user-functions.php';

/**
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code
 */
function lcwp_enqueue_js( $code ) {
	global $lcwp_queued_js;

	if ( empty( $lcwp_queued_js ) ) {
		$lcwp_queued_js = '';
	}

	$lcwp_queued_js .= "\n" . $code . "\n";
}

/**
 * Output any queued javascript code in the footer.
 */
function lcwp_print_js() {
	global $lcwp_queued_js;

	if ( ! empty( $lcwp_queued_js ) ) {

		echo "<!-- Loyalty Card for WordPress JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) {";

		// Sanitize
		$lcwp_queued_js = wp_check_invalid_utf8( $lcwp_queued_js );
		$lcwp_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $lcwp_queued_js );
		$lcwp_queued_js = str_replace( "\r", '', $lcwp_queued_js );

		echo $lcwp_queued_js . "});\n</script>\n";

		unset( $lcwp_queued_js );
	}
}
