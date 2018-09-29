<?php

/*
  Plugin Name: Dynamic Deals and Discounts for WooCommerce Basic
  Plugin URI: https://moreaddons.com/downloads/dynamic-deals-discounts-woocommerce/
  Description: An e-commerce add-on for WooCommerce, supplying Dynamic Deals and Discounts functionality.
  Author: MoreAddons
  Author URI: https://moreaddons.com
  Version: 1.0.1
 */

if (!defined('ABSPATH')) {
    exit;
}
require_once(ABSPATH . "wp-admin/includes/plugin.php");
if (in_array('woocommerce/woocommerce.php', get_option('active_plugins'))) {
    if (!in_array('deals-and-discounts-for-woocommerce-pro/deals-and-discounts-for-woocommerce-pro.php', get_option('active_plugins'))) {
        if (!defined('MA_OFF_MAIN_URL')) {
            define('MA_OFF_MAIN_URL', plugin_dir_url(__FILE__));
        }
        if (!defined('MA_OFF_MAIN_PATH')) {
            define('MA_OFF_MAIN_PATH', plugin_dir_path(__FILE__));
        }
        if (!defined('MA_OFF_VERSION')) {
            define('MA_OFF_VERSION', '1.0.1');
        }
        if (!defined('MA_OFF_MAIN_FILE')) {
            define('MA_OFF_MAIN_FILE', __FILE__);
        }
        if (!defined('MA_OFF_MAIN_IMG')) {
            define('MA_OFF_MAIN_IMG', MA_OFF_MAIN_URL . "assets/img/");
        }
        if (!defined('MA_OFF_MAIN_CSS')) {
            define('MA_OFF_MAIN_CSS', MA_OFF_MAIN_URL . "assets/css/");
        }
        if (!defined('MA_OFF_MAIN_JS')) {
            define('MA_OFF_MAIN_JS', MA_OFF_MAIN_URL . "assets/js/");
        }
        if (!defined('MA_OFF_MAIN_VIEWS')) {
            define('MA_OFF_MAIN_VIEWS', MA_OFF_MAIN_PATH . "views/");
        }
        function ma_off_run() {
            if (!class_exists('MA_Offers_Init_Handler')) {
                require_once (MA_OFF_MAIN_PATH . "includes/ma-offers-public-functions.php");
                require_once (MA_OFF_MAIN_PATH . "includes/class-offers-init-handler.php");
                require_once (MA_OFF_MAIN_PATH . "includes/class-offers-settings-handler.php");
                require_once (MA_OFF_MAIN_PATH . "includes/class-offers-request-functions.php");
                require_once (MA_OFF_MAIN_PATH . "includes/class-offers-available-data.php");
                new MA_Offers_Init_Handler();
                if (!class_exists('MoreAddons_Uninstall_feedback_Listener')) {
                    require_once (MA_OFF_MAIN_PATH . "includes/class-moreaddons-uninstall.php");
                }
                $qvar = array(
                    'name' => 'Dynamic Deals and Discounts for WooCommerce Basic',
                    'version' => MA_OFF_VERSION,
                    'slug' => 'deals-and-discounts-for-woocommerce-basic',
                    'lang' => 'ma_offers_zone',
                    'logo' => MA_OFF_MAIN_IMG.'logo.png'
                );
                new MoreAddons_Uninstall_feedback_Listener($qvar);
                if (!class_exists('MA_Offers_Sync_Setup')) {
                    require_once (MA_OFF_MAIN_PATH . "includes/class-offers-sync-setup.php");
                    new MA_Offers_Sync_Setup();
                }
            }
        }
        add_action("init", 'ma_off_run',99);
        add_filter('plugin_row_meta', 'ma_discounts_plugin_row_meta', 10, 2);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ma_discounts_action_link');
        function ma_discounts_action_link($links) {
            $plugin_links = array(
                '<a href="' . admin_url('admin.php?page=ma_offers_products&tab=new_offer') . '">'.__('Create a deal','ma_offers_zone').'</a>',
                '<a href="' . admin_url('admin.php?page=wc-settings&tab=ma_offers_settings') . '">'.__('Deal settings','ma_offers_zone').'</a>'
            );
        if ( array_key_exists( 'deactivate', $links ) ) {
            $links['deactivate'] = str_replace( '<a', '<a class="deals-and-discounts-for-woocommerce-basic-deactivate-link"', $links['deactivate'] );
        }
        return array_merge($plugin_links, $links);
        }
        function ma_discounts_plugin_row_meta($links, $file) {
            if ($file == plugin_basename(__FILE__)) {
                $row_meta = array(
                    '<a href="https://moreaddons.com/category/documentation/dynamic-deals-and-discounts-for-woocommerce/" target="_blank">'.__('Documentation','ma_offers_zone').'</a>',
                    '<a href="https://wordpress.org/support/plugin/dynamic-deals-and-discounts-for-woocommerce-basic/" target="_blank">'.__('Support','ma_offers_zone').'</a>'
                );
                return array_merge($links, $row_meta);
            }
            return (array) $links;
        }
    }
    else
    {
        add_action('admin_notices','ma_discount_basic_admin_notices', 99);
        deactivate_plugins(plugin_basename(__FILE__));
        function ma_discount_basic_admin_notices()
        {
            is_admin() && add_filter('gettext', function($translated_text, $untranslated_text, $domain)
            {
                $old = array(
                    "Plugin <strong>activated</strong>.",
                    "Selected plugins <strong>activated</strong>."
                    );
                $new = "<span style='color:red'>Dynamic Deals and Discounts for WooCommerce Basic - Pro is currently installed and active</span>";
                if (in_array($untranslated_text, $old, true)) {
                    $translated_text = $new;
                }
                return $translated_text;
            }, 99, 3);
        }
        return;
    }
}
else
{
    add_action('admin_notices','ma_discount_wc_basic_admin_notices', 99);
    deactivate_plugins(plugin_basename(__FILE__));
    function ma_discount_wc_basic_admin_notices()
    {
        is_admin() && add_filter('gettext', function($translated_text, $untranslated_text, $domain)
        {
            $old = array(
                "Plugin <strong>activated</strong>.",
                "Selected plugins <strong>activated</strong>."
                );
            $new = "<span style='color:red'>Dynamic Deals and Discounts for WooCommerce Basic - WooCommerce is not Installed</span>";
            if (in_array($untranslated_text, $old, true)) {
                $translated_text = $new;
            }
            return $translated_text;
        }, 99, 3);
    }
    return;
}
