<?php
/**
 * Loyalty Card Class
 *
 * The Loyalty Card for WordPress loyalty card class handles individual loyalty card data.
 *
 * @author Nicola Mustone
 */
class LCWP_Loyalty_Card {

	/**
	 * The product (post) ID.
	 *
	 * @var int
	 */
	public $id = 0;

	/**
	 * $post Stores post data
	 *
	 * @var $post WP_Post
	 */
	public $post = null;

	/**
	 * __isset function.
	 *
	 * @param mixed $key
	 * @return bool
	 */
	public function __isset( $key ) {
		return metadata_exists( 'post', $this->id, '_' . $key );
	}

	/**
	 * __get function.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key ) {
		$value = get_post_meta( $this->id, '_' . $key, true );

		return $value;
	}

	/**
	 * Constructor gets the post object and sets the ID for the loaded product.
	 *
	 * @param int|WC_Product|object $product Product ID, post object, or product object
	 */
	public function set_loyalty_card( $product ) {
		if ( is_numeric( $product ) ) {
			$this->id   = absint( $product );
			$this->post = get_post( $this->id );
		} elseif ( $product instanceof WC_Product ) {
			$this->id   = absint( $product->id );
			$this->post = $product->post;
		} elseif ( isset( $product->ID ) ) {
			$this->id   = absint( $product->ID );
			$this->post = $product;
		}

		return $this;
	}

	/**
	 * Get the product object
	 * @param  mixed $the_loyalty_card
	 * @uses   WP_POST
	 * @return WP_Post|bool false on failure
	 */
	private function get_loyalty_card_object( $the_loyalty_card ) {
		if ( false === $the_loyalty_card ) {
			$the_loyalty_card = $GLOBALS['post'];
		} elseif ( is_numeric( $the_loyalty_card ) ) {
			$the_loyalty_card = get_post( $the_loyalty_card );
		} elseif ( $the_loyalty_card instanceof LCWP_Loyalty_Card ) {
			$the_loyalty_card = get_post( $the_loyalty_card->id );
		} elseif ( ! ( $the_loyalty_card instanceof WP_Post ) ) {
			$the_loyalty_card = false;
		}

		return $v;
	}

	/**
	 * Get the product's post data.
	 *
	 * @return object
	 */
	public function get_post_data() {
		return $this->post;
	}

	/**
	 * Wrapper for get_permalink
	 *
	 * @return string
	 */
	public function get_permalink() {
		return get_permalink( $this->id );
	}

	/**
	 * Returns whether or not the product post exists.
	 *
	 * @return bool
	 */
	public function exists() {
		return empty( $this->post ) ? false : true;
	}

	/**
	 * Get the title of the post.
	 *
	 * @return string
	 */
	public function get_title() {
		return apply_filters( 'lcwp_get_loyalty_card_title', $this->post ? $this->post->post_title : '', $this );
	}

	public function get_card_number() {
		return apply_filters( 'lcwp_get_card_number', $this->card_number, $this );
	}

	public function get_owner() {
		return apply_filters( 'lcwp_get_owner', $this->owner, $this );
	}
}
