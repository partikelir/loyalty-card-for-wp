<?php
/**
* Loyalty Card for WordPress User Functions
*
* @author Nicola Mustone
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Search for users and return json
 */
function lcwp_json_search_users() {
	ob_start();

	check_ajax_referer( 'search-users', 'security' );

	$term = lcwp_clean( stripslashes( $_GET['term'] ) );

	if ( empty( $term ) ) {
		die();
	}

	$found_users = array();

	add_action( 'pre_user_query', 'lcwp_json_search_user_name' );

	$users_query = new WP_User_Query( apply_filters( 'lcwp_json_search_users_query', array(
		'fields'         => 'all',
		'orderby'        => 'display_name',
		'search'         => '*' . $term . '*',
		'search_columns' => array( 'ID', 'user_login', 'user_email', 'user_nicename' )
	) ) );

	remove_action( 'pre_user_query', 'lcwp_json_search_user_name' );

	$users = $users_query->get_results();

	if ( $users ) {
		foreach ( $users as $user ) {
			$found_users[ $user->ID ] = $user->display_name . ' (#' . $user->ID . ' &ndash; ' . sanitize_email( $user->user_email ) . ')';
		}
	}

	wp_send_json( $found_users );
}

/**
 * When searching using the WP_User_Query, search names (user meta) too
 *
 * @param  object $query
 * @return object
 */
function lcwp_json_search_user_name( $query ) {
	global $wpdb;

	$term = lcwp_clean( stripslashes( $_GET['term'] ) );
	$term = $wpdb->esc_like( $term );

	$query->query_from  .= " INNER JOIN {$wpdb->usermeta} AS user_name ON {$wpdb->users}.ID = user_name.user_id AND ( user_name.meta_key = 'first_name' OR user_name.meta_key = 'last_name' ) ";
	$query->query_where .= $wpdb->prepare( " OR user_name.meta_value LIKE %s ", '%' . $term . '%' );
}
