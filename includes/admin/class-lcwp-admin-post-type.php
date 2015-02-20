<?php
/**
 * Post Type Admin
 *
 * @author Nicola Mustone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'LCWP_Admin_Post_Type' ) ) :

/**
 * LCWP_Admin_Post_Type Class
 *
 * Handles the edit posts views and some functionality on the edit post screen for LCWP post type.
 */
class LCWP_Admin_Post_Type {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );

		// WP List table columns. Defined here so they are always available for events such as inline editing.
		add_filter( 'manage_loyalty_card_posts_columns', array( $this, 'loyalty_card_columns' ) );
		add_action( 'manage_loyalty_card_posts_custom_column', array( $this, 'render_loyalty_card_columns' ), 2 );
		add_filter( 'manage_edit-loyalty_card_sortable_columns', array( $this, 'loyalty_card_sortable_columns' ) );

		// Views
		add_filter( 'views_edit-loyalty_card', array( $this, 'loyalty_card_sorting_link' ) );

		// Filters
		add_filter( 'request', array( $this, 'request_query' ) );
		add_filter( 'posts_search', array( $this, 'loyalty_card_search' ) );

		// Edit post screens
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );
		add_filter( 'media_view_strings', array( $this, 'change_insert_into_post' ) );

		include_once( 'class-lcwp-admin-meta-boxes.php' );
	}

	/**
	 * Define custom columns for products
	 * @param  array $existing_columns
	 * @return array
	 */
	public function loyalty_card_columns( $existing_columns ) {
		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
			$existing_columns = array();
		}

		unset( $existing_columns['title'], $existing_columns['comments'], $existing_columns['date'] );

		$columns                 = array();
		$columns['cb']           = '<input type="checkbox" />';
		$columns['name']         = __( 'Name', 'lcwp' );
		$columns['owner']        = __( 'Owner', 'lcwp' );
		$columns['barcode']      = __( 'Barcode', 'lcwp' );
		$columns['date']         = __( 'Date', 'lcwp' );

		return array_merge( $columns, $existing_columns );

	}

	/**
	 * Ouput custom columns for products
	 * @param  string $column
	 */
	public function render_loyalty_card_columns( $column ) {
		global $post;

		if ( empty( $the_loyalty_card ) || $the_loyalty_card->id != $post->ID ) {
			$the_loyalty_card = lcwp_get_loyalty_card( $post );
		}

		switch ( $column ) {
			case 'name' :
				$edit_link        = get_edit_post_link( $post->ID );
				$title            = _draft_or_post_title();
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) .'">' . $title.'</a>';

				_post_states( $post );

				echo '</strong>';

				if ( $post->post_parent > 0 ) {
					echo '&nbsp;&nbsp;&larr; <a href="'. get_edit_post_link( $post->post_parent ) .'">'. get_the_title( $post->post_parent ) .'</a>';
				}

				// Get actions
				$actions = array();

				$actions['id'] = 'ID: ' . $post->ID;

				if ( $can_edit_post && 'trash' != $post->post_status ) {
					$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item', 'lcwp' ) ) . '">' . __( 'Edit', 'lcwp' ) . '</a>';
				}
				if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
					if ( 'trash' == $post->post_status ) {
						$actions['untrash'] = '<a title="' . esc_attr( __( 'Restore this item from the Trash', 'lcwp' ) ) . '" href="' . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . '">' . __( 'Restore', 'lcwp' ) . '</a>';
					} elseif ( EMPTY_TRASH_DAYS ) {
						$actions['trash'] = '<a class="submitdelete" title="' . esc_attr( __( 'Move this item to the Trash', 'lcwp' ) ) . '" href="' . get_delete_post_link( $post->ID ) . '">' . __( 'Trash', 'lcwp' ) . '</a>';
					}

					if ( 'trash' == $post->post_status || ! EMPTY_TRASH_DAYS ) {
						$actions['delete'] = '<a class="submitdelete" title="' . esc_attr( __( 'Delete this item permanently', 'lcwp' ) ) . '" href="' . get_delete_post_link( $post->ID, '', true ) . '">' . __( 'Delete Permanently', 'lcwp' ) . '</a>';
					}
				}
				if ( $post_type_object->public ) {
					if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) ) {
						if ( $can_edit_post )
							$actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'lcwp' ), $title ) ) . '" rel="permalink">' . __( 'Preview', 'lcwp' ) . '</a>';
					} elseif ( 'trash' != $post->post_status ) {
						$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'lcwp' ), $title ) ) . '" rel="permalink">' . __( 'View', 'lcwp' ) . '</a>';
					}
				}

				$actions = apply_filters( 'post_row_actions', $actions, $post );

				echo '<div class="row-actions">';

				$i = 0;
				$action_count = sizeof( $actions );

				foreach ( $actions as $action => $link ) {
					++$i;
					( $i == $action_count ) ? $sep = '' : $sep = ' | ';
					echo '<span class="' . $action . '">' . $link . $sep . '</span>';
				}
				echo '</div>';

				get_inline_data( $post );

				/* Custom inline data for woocommerce */
				echo '
					<div class="hidden" id="woocommerce_inline_' . $post->ID . '">
						<div class="card_number">' . $the_loyalty_card->card_number . '</div>
					</div>
				';

			break;
			case 'owner' :
				$owner_id = $the_loyalty_card->get_owner();

				$owner = false;
				if ( $owner_id ) {
					$owner = get_user_by( 'id', $owner_id );

					if ( false !== $owner ) {
						$owner = $owner->first_name . ' ' . $owner->last_name . ' &lt;' . $owner->user_email . '&gt;';
					}
				}

				echo $owner ? $owner : '&ndash;';

				break;
			case 'barcode':
				echo lcwp_generate_barcode( $the_loyalty_card->id );
				break;
			default :
				break;
		}
	}

	/**
	 * Make columns sortable - https://gist.github.com/906872
	 *
	 * @param array $columns
	 * @return array
	 */
	public function loyalty_card_sortable_columns( $columns ) {
		$custom = array(
			'card_number' => 'card_number',
			'name'        => 'title'
		);
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Product sorting link
	 *
	 * Based on Simple Page Ordering by 10up (http://wordpress.org/extend/plugins/simple-page-ordering/)
	 *
	 * @param array $views
	 * @return array
	 */
	public function loyalty_card_sorting_link( $views ) {
		global $post_type, $wp_query;

		if ( ! current_user_can('edit_others_pages') ) {
			return $views;
		}

		$class            = ( isset( $wp_query->query['orderby'] ) && $wp_query->query['orderby'] == 'menu_order title' ) ? 'current' : '';
		$query_string     = remove_query_arg(array( 'orderby', 'order' ));
		$query_string     = add_query_arg( 'orderby', urlencode('menu_order title'), $query_string );
		$query_string     = add_query_arg( 'order', urlencode('ASC'), $query_string );

		return $views;
	}

	/**
	 * Query vars for custom searches.
	 *
	 * @param mixed $public_query_vars
	 * @return array
	 */
	public function add_custom_query_var( $public_query_vars ) {
		$public_query_vars[] = 'card_number';

		return $public_query_vars;
	}

	/**
	 * Filters and sorting handler
	 *
	 * @param  array $vars
	 * @return array
	 */
	public function request_query( $vars ) {
		global $typenow, $wp_query;

		if ( 'loyalty_card' === $typenow ) {
			// Sorting
			if ( isset( $vars['orderby'] ) ) {
				if ( 'card_number' == $vars['orderby'] ) {
					$vars = array_merge( $vars, array(
						'meta_key'  => '_card_number',
						'orderby'   => 'meta_value'
					) );
				}
			}

		}

		return $vars;
	}

	/**
	 * Search by SKU or ID for products.
	 *
	 * @param string $where
	 * @return string
	 */
	public function loyalty_card_search( $where ) {
		global $pagenow, $wpdb, $wp;

		if ( 'edit.php' != $pagenow || ! is_search() || ! isset( $wp->query_vars['s'] ) || 'loyalty_card' != $wp->query_vars['post_type'] ) {
			return $where;
		}

		$search_ids = array();
		$terms      = explode( ',', $wp->query_vars['s'] );

		foreach ( $terms as $term ) {
			if ( is_numeric( $term ) ) {
				$search_ids[] = $term;
			}
			// Attempt to get a Card Number
			$sku_to_id = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_sku' AND meta_value LIKE '%%%s%%';", wc_clean( $term ) ) );

			if ( $sku_to_id && sizeof( $sku_to_id ) > 0 ) {
				$search_ids = array_merge( $search_ids, $sku_to_id );
			}
		}

		$search_ids = array_filter( array_map( 'absint', $search_ids ) );

		if ( sizeof( $search_ids ) > 0 ) {
			$where = str_replace( ')))', ") OR ({$wpdb->posts}.ID IN (" . implode( ',', $search_ids ) . "))))", $where );
		}

		return $where;
	}

	/**
	 * Change messages when a post type is updated.
	 *
	 * @param  array $messages
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages['product'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Loyalty Card updated. <a href="%s">View Loyalty Card</a>', 'lcwp' ), esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.', 'lcwp' ),
			3 => __( 'Custom field deleted.', 'lcwp' ),
			4 => __( 'Loyalty Card updated.', 'lcwp' ),
			5 => isset( $_GET['revision'] ) ? sprintf( __( 'Loyalty Card restored to revision from %s', 'lcwp' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Loyalty Card published. <a href="%s">View Loyalty Card</a>', 'lcwp' ), esc_url( get_permalink( $post_ID ) ) ),
			7 => __( 'Loyalty Card saved.', 'lcwp' ),
			8 => sprintf( __( 'Loyalty Card submitted. <a target="_blank" href="%s">Preview Loyalty Card</a>', 'lcwp' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9 => sprintf( __( 'Loyalty Card scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Loyalty Card</a>', 'lcwp' ),
			  date_i18n( __( 'M j, Y @ G:i', 'lcwp' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Loyalty Card draft updated. <a target="_blank" href="%s">Preview Loyalty Card</a>', 'lcwp' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
	}

	/**
	 * Change title boxes in admin.
	 *
	 * @param  string $text
	 * @param  object $post
	 * @return string
	 */
	public function enter_title_here( $text, $post ) {
		switch ( $post->post_type ) {
			case 'loyalty_card' :
				$text = __( 'Loyalty Card name', 'lcwp' );
			break;
		}

		return $text;
	}

	/**
	 * Change label for insert buttons.
	 *
	 * @param array $strings
	 * @return array
	 */
	public function change_insert_into_post( $strings ) {
		global $post_type;

		if ( $post_type === 'loyalty_card' ) {
			$obj = get_post_type_object( $post_type );

			$strings['insertIntoPost']     = sprintf( __( 'Insert into %s', 'lcwp' ), $obj->labels->singular_name );
			$strings['uploadedToThisPost'] = sprintf( __( 'Uploaded to this %s', 'lcwp' ), $obj->labels->singular_name );
		}

		return $strings;
	}
}

endif;

new LCWP_Admin_Post_Type();
