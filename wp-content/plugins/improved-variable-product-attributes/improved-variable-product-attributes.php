<?php
/*
Plugin Name: Improved Variable Product Attributes for WooCommerce
Plugin URI: http://www.mihajlovicnenad.com/improved-variable-product-attributes
Description: Improved Variable Product Attributes for WooCommerce! - mihajlovicnenad.com
Author: Mihajlovic Nenad
Version: 4.0.0
Author URI: https://www.mihajlovicnenad.com
Text Domain: ivpawoo
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'WC_Improved_Variable_Product_Attributes_Init' ) ) :

	final class WC_Improved_Variable_Product_Attributes_Init {

		public static $version = '4.0.0';

		protected static $_instance = null;

		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public function __construct() {
			do_action( 'wcmnivpa_loading' );

			$this->includes();

			$this->init_hooks();

			do_action( 'wcmnivpa_loaded' );
		}

		private function init_hooks() {
			register_activation_hook( __FILE__, array( $this, '_ivpa_flush_cache' ) );
			register_deactivation_hook( __FILE__, array( $this, '_ivpa_flush_cache' ) );
			add_action( 'plugins_loaded', array( $this, '_ivpa_flush_cache_on_update' ) );

			add_action( 'init', array( $this, 'init' ), 0 );
		}

		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		public function includes() {

			if ( $this->is_request( 'admin' ) ) {
				include_once ( 'includes/ivpa-settings.php' );

				$purchase_code = get_option( 'wc_settings_ivpa_purchase_code', '' );
				if ( $purchase_code ) {
					require 'includes/update/plugin-update-checker.php';
					$pf_check = PucFactory::buildUpdateChecker(
						'http://mihajlovicnenad.com/envato/get_json.php?p=9981757&k=' . $purchase_code,
						__FILE__
					);
				}

			}

			if ( $this->is_request( 'frontend' ) ) {
				$this->frontend_includes();
			}

		}

		public function frontend_includes() {
			include_once( 'includes/ivpa-frontend.php' );
		}

		public function init() {

			do_action( 'before_wcmnivpa_init' );

			$this->load_plugin_textdomain();

			do_action( 'after_wcmnivpa_init' );

		}

		public function load_plugin_textdomain() {

			$domain = 'ivpawoo';
			$dir = untrailingslashit( WP_LANG_DIR );
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			if ( $loaded = load_textdomain( $domain, $dir . '/plugins/' . $domain . '-' . $locale . '.mo' ) ) {
				return $loaded;
			}
			else {
				load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/lang/' );
			}

		}

		public function _ivpa_flush_cache_on_update() {

			$version = self::$version;
			$transient = get_transient( '_ivpa_version' );

			if ( $transient === false ) {
				set_transient( '_ivpa_version', $version );
			}
			else if ( version_compare( $transient, $version, '<' ) ) {
				global $wpdb;
				$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta WHERE meta.meta_key LIKE '_ivpa_cached_%';" );
				set_transient( '_ivpa_version', $version );
			}

		}

		public function _ivpa_flush_cache() {

			global $wpdb;
			$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta WHERE meta.meta_key LIKE '_ivpa_cached_%';" );

		}

		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		public function plugin_basename() {
			return untrailingslashit( plugin_basename( __FILE__ ) );
		}

		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}

		public static function version_check( $version = '3.0.0' ) {
			if ( class_exists( 'WooCommerce' ) ) {
				global $woocommerce;
				if( version_compare( $woocommerce->version, $version, ">=" ) ) {
					return true;
				}
			}
			return false;
		}

		public function version() {
			return self::$version;
		}

	}

	function Wcmnivpa() {
		return WC_Improved_Variable_Product_Attributes_Init::instance();
	}

	WC_Improved_Variable_Product_Attributes_Init::instance();

endif;

?>