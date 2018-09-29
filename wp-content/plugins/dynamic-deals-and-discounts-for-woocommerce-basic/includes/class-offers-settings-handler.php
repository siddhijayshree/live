<?php

if (!defined('ABSPATH')) {
    exit;
}

class MA_Offers_Settings_Handler {

    function ma_avilable_offers_tab_callback() {
        include(MA_OFF_MAIN_VIEWS . "available_offers_main_view.php");
    }

    function ma_new_offer_tab_callback() {
        include(MA_OFF_MAIN_VIEWS . "new_offer_main_view.php");
    }

    function ma_offer_edit_callback($id) {
        include(MA_OFF_MAIN_VIEWS . "edit_offer_main_view.php");
    }

    function ma_offer_view_callback($id) {
        include(MA_OFF_MAIN_VIEWS . "view_offer_main_view.php");
    }

    function get_offer_settings() {
        woocommerce_admin_fields($this->get_offer_settings_data());
    }

    function update_offer_settings() {
        woocommerce_update_options($this->get_offer_settings_data());
    }

    function get_offer_settings_data() {
        $settings = array(
            'basic_section_title' => array(
                'name' => __('Basic Settings', 'ma_offers_zone'),
                'type' => 'title'
            ),
            'show_offer_badge' => array(
                'title' => __('Show Badge', 'ma_offers_zone'),
                'type' => 'checkbox',
                'default' => 'yes',
                'desc_tip' => __('Enable this option will show offer badge on product image.', 'ma_offers_zone'),
                'desc' => "Enable",
                'id' => 'ma_offers_settings_show_offer_badge'
            ),
            'show_discount_badge' => array(
                'title' => __('Show Discount Text', 'ma_offers_zone'),
                'type' => 'checkbox',
                'default' => 'yes',
                'desc_tip' => __('Enable this option will show discount text on product.', 'ma_offers_zone'),
                'desc' => "Enable",
                'id' => 'ma_offers_settings_show_discount_badge'
            ),
            'sale_badge_replace' => array(
                'title' => __('Replace Sales Badge', 'ma_offers_zone'),
                'type' => 'checkbox',
                'default' => 'yes',
                'desc_tip' => __('Enable this option will replace sales badge of products with offer badge.', 'ma_offers_zone'),
                'desc' => "Replace",
                'id' => 'ma_offers_settings_sale_badge_replace'
            ),
            'basic_section_end' => array(
                'type' => 'sectionend',
            ),
            'ad_section_title' => array(
                'name' => __('Deals Ad Settings', 'ma_offers_zone'),
                'type' => 'title'
            ),
            'show_ad_image' => array(
                'title' => __('Show Ad Image', 'ma_offers_zone'),
                'type' => 'checkbox',
                'default' => 'yes',
                'desc_tip' => __('Enable this option will show advertisement image to checkout page.', 'ma_offers_zone'),
                'desc' => "Show",
                'id' => 'ma_offers_settings_ad_image'
            ),
            'show_ad_image_position' => array(
                'title' => __('Ad Image Position', 'ma_offers_zone'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'desc_tip' => __('Select the position of Image that needs to be displayed.', 'ma_offers_zone'),
                'options' => array(
                    'top' => 'Top',
                    'bottom' => 'Bottom'
                ),
                'default' => 'top',
                'id' => 'ma_offers_settings_show_ad_image_position',
            ),
            'show_ad_image_duration' => array(
                'title' => __('Ad Image Duration', 'ma_offers_zone'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'desc_tip' => __('Select the Ad Image duration.', 'ma_offers_zone'),
                'options' => array(
                    'week' => 'Next Week Offer',
                    'month' => 'Next Month Offer',
                    'year' => 'Next Year Offer',
                    'all' => 'All Upcoming Offer',
                ),
                'default' => 'week',
                'id' => 'ma_offers_settings_show_ad_image_duration',
            ),
            'show_ad_image_checkout' => array(
                'title' => __('Show Ad Image in Checkout', 'ma_offers_zone'),
                'type' => 'checkbox',
                'default' => 'yes',
                'desc' => "Show",
                'id' => 'ma_offers_settings_show_ad_image_checkout'
            ),
            'ad_section_end' => array(
                'type' => 'sectionend',
            )
        );
        return apply_filters('wc_settings_tab_ma_offers_settings', $settings);
    }
}