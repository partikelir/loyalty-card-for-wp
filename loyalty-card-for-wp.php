<?php
/**
 * Plugin Name: Loyalty Card for WordPress
 * Plugin URI: https://nicolamustone.it/loyalty-card-for-wp/
 * Description: Loyalty Card for WordPress adds loyalty cards to your site and you can create beautiful cards for your customers.
 * Version: 1.0.0
 * Author: Nicola Mustone
 * Author URI: https://nicolamustone.it/
 *
 * License: GPL v3
 *
 * Loyalty Card for WordPress plugin
 * Copyright (C) 2015, Nicola Mustone <mustone.nicola@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class
 */
final class LoyaltyCardForWP {
	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * The single instance of the class
	 *
	 * @var    LoyaltyCardForWP
	 * @access protected
	 */
	protected static $_instance;

	/**
	 * Main LoyaltyCardForWP Instance
	 *
	 * Ensures only one instance of LoyaltyCardForWP is loaded or can be loaded.
	 *
	 * @static
	 * @see    LCWP()
	 * @return LoyaltyCardForWP - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'lcwp' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'lcwp' ), '1.0.0' );
	}

	/**
	 * LoyaltyCardForWP Constructor.
	 */
	public function __construct() {
		$this->_define_constants();
		$this->_includes();
		$this->_init();
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Frontend/global Locales found in:
	 * 		- WP_LANG_DIR/loyalty-card-for-wp/loyalty-card-for-wp-LOCALE.mo
	 * 	 	- loyalty-card-for-wp/i18n/loyalty-card-for-wp-LOCALE.mo (which if not found falls back to:)
	 * 	 	- WP_LANG_DIR/plugins/loyalty-card-for-wp-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'lcwp' );

		load_textdomain( 'lcwp', WP_LANG_DIR . '/loyalty-card-for-wp/loyalty-card-for-wp-' . $locale . '.mo' );
		load_plugin_textdomain( 'lcwp', false, plugin_basename( dirname( __FILE__ ) ) . "/i18n" );
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public function admin_scripts() {
		$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'lcwp-admin', LCWP()->plugin_url() . '/assets/js/admin/lcwp_admin' . $suffix . '.js', array( 'jquery' ), LCWP_VERSION, true );
		wp_register_script( 'select2', LCWP()->plugin_url() . '/assets/js/select2/select2' . $suffix . '.js', array( 'jquery' ), LCWP_VERSION, true );
		wp_register_script( 'jquery-tiptip', LCWP()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), LCWP_VERSION, true );
		wp_register_script( 'jquery-barcode', LCWP()->plugin_url() . '/assets/js/jquery-barcode/jquery-barcode' . $suffix . '.js', array( 'jquery' ), LCWP_VERSION, true );

		wp_enqueue_script( 'lcwp-admin' );
		wp_enqueue_script( 'jquery-tiptip' );
		wp_enqueue_script( 'jquery-barcode' );
		wp_enqueue_script( 'select2' );

		wp_localize_script( 'select2', 'lcwp_select_params', array(
			'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'lcwp' ),
			'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'lcwp' ),
			'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'lcwp' ),
			'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'lcwp' ),
			'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'lcwp' ),
			'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'lcwp' ),
			'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'lcwp' ),
			'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'lcwp' ),
			'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'lcwp' ),
			'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'lcwp' ),
			'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'lcwp' ),
			'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'lcwp' ),
		) );

		wp_localize_script( 'lcwp-admin', 'lcwp_enhanced_select_params', array(
			'ajax_url'           => admin_url( 'admin-ajax.php' ),
			'search_users_nonce' => wp_create_nonce( 'search-users' )
		) );
	}

	public function admin_styles() {
		wp_enqueue_style( 'lcwp-admin', LCWP()->plugin_url() . '/assets/css/admin.css' );
	}

	/**
	 * Init Loyalty Cards for WordPress when WordPress Initialises.
	 *
	 * @access private
	 * @return void
	 */
	private function _init() {
		do_action( 'before_lcwp_init' );

		$this->load_plugin_textdomain();
		$this->loyalty_card = new LCWP_Loyalty_Card();

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

			add_action( 'wp_ajax_lcwp_json_search_users', 'lcwp_json_search_users' );
		}

		do_action( 'lcwp_init' );
	}

	/**
	 * Define WC Constants
	 *
	 * @access private
	 * @return void
	 */
	private function _define_constants() {
		$upload_dir = wp_upload_dir();

		define( 'LCWP_PLUGIN_FILE', __FILE__ );
		define( 'LCWP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'LCWP_VERSION', $this->version );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @access private
	 * @return void
	 */
	private function _includes() {
		include_once $this->plugin_path() . '/includes/class-lcwp-autoloader.php';
		include_once $this->plugin_path() . '/includes/lcwp-core-functions.php';
		include_once $this->plugin_path() . '/includes/class-lcwp-post-type.php';

		//Loyalty Card
		include_once $this->plugin_path() . '/includes/class-lcwp-loyalty-card.php';

		if ( is_admin() ) {
			include_once $this->plugin_path() . '/includes/admin/class-lcwp-admin.php';
		}
	}
}

/**
 * Returns the main instance of LoyaltyCardForWP to prevent the need to use globals.
 *
 * @return LoyaltyCardForWP
 */
function LCWP() {
	return LoyaltyCardForWP::instance();
}

LCWP();
