<?php

if (!defined('ABSPATH')) {
    exit;
}

class MA_Offers_Init_Handler {

    protected $menu_settings;

    function __construct() {
        $this->menu_settings = new MA_Offers_Settings_Handler();
        add_action('admin_menu', array($this, 'ma_offers_menu_add'));
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
        add_action('woocommerce_settings_tabs_ma_offers_settings', array($this->menu_settings, 'get_offer_settings'));
        add_action('woocommerce_update_options_ma_offers_settings', array($this->menu_settings, 'update_offer_settings'));
        add_action('admin_enqueue_scripts', array($this, 'ma_offers_register_styles_scripts'));
        add_action('wp_ajax_ma_offer_add_new', array("MA_Offer_Ajax_Requests", 'ma_offer_add_new'));
        add_action('wp_ajax_ma_offer_edit', array("MA_Offer_Ajax_Requests", 'ma_offer_edit'));
        add_action('wp_ajax_ma_offer_edit_status', array("MA_Offer_Ajax_Requests", 'ma_offer_edit_status'));
        add_filter('set-screen-option', array($this,'set_screen'), 10, 3 );
        add_filter('post_thumbnail_html', array($this,'modify_post_thumbnail_html'), 100, 2);
        add_filter('woocommerce_sale_flash',array($this,'replace_sales_badge'),21,3);
        if(ma_get_offer_settings("ma_offers_settings_ad_image", 'yes')==='yes')
        {
            if(ma_get_offer_settings("ma_offers_settings_show_ad_image_checkout", 'yes')==='yes')
            {
                if(ma_get_offer_settings("ma_offers_settings_show_ad_image_position", 'top')==='top')
                {
                    add_action("woocommerce_before_checkout_form", array($this,'display_banners'),20);
                }
                else
                {
                    add_action("woocommerce_after_checkout_form", array($this,'display_banners'),20);
                }
            }
        }
        if(ma_get_offer_settings("ma_offers_settings_show_discount_badge", 'yes')==='yes')
        {
            add_action("woocommerce_after_shop_loop_item_title", array($this,'show_discount_badge_text_up'),9);
            add_action("woocommerce_after_shop_loop_item_title", array($this,'show_discount_badge_text_down'),10);
            add_action("woocommerce_before_add_to_cart_form", array($this,'show_discount_badge_text_up'),10);
            add_action("woocommerce_before_add_to_cart_button", array($this,'show_discount_badge_text_down'),10);
        }
        add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'ma_cart_process_discounts' ), 109 );
        add_action( 'woocommerce_ajax_added_to_cart', array( $this, 'ma_cart_process_discounts' ), 120 );
        add_filter( 'woocommerce_cart_item_price', array( $this, 'ma_replace_cart_item_price' ), 110, 3 );
        add_action( 'woocommerce_checkout_order_processed', array( $this, 'ma_wc_checkout_order_processed'), 10, 1 );
        add_action('woocommerce_single_product_image_thumbnail_html', array($this,'filter_woocommerce_single_product_image_html'), 1, 2 );
        add_action("wp_enqueue_scripts", array($this,"front_enqueue_scripts"));
    }
    
    function show_discount_badge_text_up()
    {
        global $post;
        $id = $post->ID;
        $avail_off = ma_get_active_offers();
        if($avail_off!=="")
        {
            $offer = ma_get_offer_data($avail_off);
            if(ma_is_product_on_offfer($offer, $id) && $offer['discount_badge_position'] == 'up_price')
            {
                $html = '';
                if($offer['by']==="price")
                {
                    $html=get_woocommerce_currency_symbol().$offer['unit'];
                }
                else
                {
                    $html=$offer['unit']."%";
                }
                echo '<div class="ma_discount_badge_text">'.str_replace('{ma_discount}', $html, $offer['discount_badge']).'</div>';
            }
        }
    }
    
    function show_discount_badge_text_down()
    {
        global $post;
        $id = $post->ID;
        $avail_off = ma_get_active_offers();
        if($avail_off!=="")
        {
            $offer = ma_get_offer_data($avail_off);
            if(ma_is_product_on_offfer($offer, $id) && $offer['discount_badge_position'] == 'down_price')
            {
                $html = '';
                if($offer['by']==="price")
                {
                    $html=get_woocommerce_currency_symbol().$offer['unit'];
                }
                else
                {
                    $html=$offer['unit']."%";
                }
                echo '<div class="ma_discount_badge_text">'.str_replace('{ma_discount}', $html, $offer['discount_badge']).'</div>';
            }
        }
    }
    
    function ma_cart_process_discounts( ){

        if ( empty( WC()->cart->cart_contents ) ){
            return;
        }

        foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {

            if ( isset( WC()->cart->cart_contents[$cart_item_key]['ma_discount_data'] ) ) {
                unset( WC()->cart->cart_contents[$cart_item_key]['ma_discount_data'] );
            }
            $avail_off = ma_get_active_offers();
            $item_discounts = $this->ma_get_adjusts_to_product( $cart_item, $avail_off);
            if ( !empty( $item_discounts ) ) {
                $this->ma_apply_discount( $cart_item, $cart_item_key, $item_discounts,$avail_off);
            }
            else
            {
                unset(WC()->session->ma_fest_offer_cart_track);
                unset(WC()->session->ma_fest_offer_cart_track_off_id);
            }
        }

    }
    
    function ma_replace_cart_item_price( $price, $cart_item,$cart_item_key){

        if ( !isset( $cart_item['ma_discount_data'] ) ) {
            return $price;
        }
        $avail_off = ma_get_active_offers();
        $up = '';
        $down = '';
        if($avail_off!=="")
        {
            $offer = ma_get_offer_data($avail_off);
            $html = '';
            if($offer['by']==="price")
            {
                $html=get_woocommerce_currency_symbol().$offer['unit'];
            }
            else
            {
                $html=$offer['unit']."%";
            }
            if($offer['discount_badge_position'] == 'down_price')
            {
                $down = '<div class="ma_discount_badge_text">'.str_replace('{ma_discount}', $html, $offer['discount_badge']).'</div>';
            }
            else
            {
                $up = '<div class="ma_discount_badge_text">'.str_replace('{ma_discount}', $html, $offer['discount_badge']).'</div>';
            }
        }
        return $up.'<del>' . wc_price( $cart_item['ma_discount_data']['default_price'] ) . '</del> '. WC()->cart->get_product_price( $cart_item['data'] ).$down;

    }
    
    public function ma_apply_discount( $cart_item, $cart_item_key, $discount,$avail_off ){

        $default_price = $cart_item['data']->get_price();
        $price = $default_price;
        if( $discount['type'] == 'percentage' ){
            $price = $price - ($price * ($discount['amount']/100));
        }
        if( $discount['type'] == 'price' ){
            $price = $price - $discount['amount'];
        }
        $product = WC()->cart->cart_contents[$cart_item_key]['data'];

        WC()->cart->cart_contents[$cart_item_key]['ma_discount_data'] = array(
            'default_price'    => ( WC()->cart->tax_display_cart == 'excl' ) ? $this->ma_price_excluding_tax( $product ) : $this->ma_price_including_tax( $product ),
            'discount_applied' => $discount,
        );
        if ( version_compare( WC()->version, '2.6', '<' ) ) 
        {
            WC()->cart->cart_contents[$cart_item_key]['data']->price = $price;
            WC()->cart->cart_contents[$cart_item_key]['data']->has_dynamic_price = true;
        }
        else
        {
            WC()->cart->cart_contents[$cart_item_key]['data']->set_price($price);
            WC()->cart->cart_contents[$cart_item_key]['data']->has_dynamic_price = true;
        }
        WC()->session->ma_fest_offer_cart_track_off_id = $avail_off;
    }
    
    function ma_get_adjusts_to_product( $cart_item ,$avail_off) {

        if ( empty( $cart_item ) ) {
            return false;
        }
        $item_discounts = array();
        $product_id = $cart_item['product_id'];
        if($avail_off!=="")
        {
            $offer = ma_get_offer_data($avail_off);
            if(ma_is_product_on_offfer($offer, $product_id))
            {
                $item_discounts = array( 'type' => $offer['by'], 'amount' => $offer['unit'] );
            }
        }
        return $item_discounts;

    }
    
    function ma_price_including_tax( $product, $qty = 1, $price = '' ) {

        if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {

            $price = wc_get_price_including_tax( $product, array( 'qty' => $qty, 'price' => $price ) );
        } else {

            $price = $product->get_price_including_tax( $qty, $price );
        }

        return $price;
    }
    
    function ma_price_excluding_tax( $product, $qty = 1, $price = '' ) {

        if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {

            $price = wc_get_price_excluding_tax( $product, array( 'qty' => $qty, 'price' => $price ) );
        } else {

            $price = $product->get_price_excluding_tax( $qty, $price );
        }

        return $price;
    }
    
    function front_enqueue_scripts() {
        $avail_off = ma_get_active_offers();
        global $wp_query;
        $content = $wp_query->post->post_content;
        $off_page = has_shortcode($content, 'ma_offer_page');
        if((is_product() || is_shop() || is_product_category() || $off_page ) && $avail_off !== "")
        {
            wp_enqueue_style("ma_fest_offers", MA_OFF_MAIN_CSS . "ma_fest_offers.css");
        }
        if(ma_get_offer_settings("ma_offers_settings_show_ad_image_checkout", 'yes')==='yes' && is_checkout())
        {
            wp_enqueue_style("ma_fest_offers_slider", MA_OFF_MAIN_CSS . "offer_slider.css");
        }
        
    }
    
    function ma_wc_checkout_order_processed($order_id)
    {
        if(isset(WC()->session->ma_fest_offer_cart_track_off_id) && !empty(WC()->session->ma_fest_offer_cart_track_off_id))
        {
            $orders = get_option('ma_offer_zone_ordered_orders',array());
            $offer = WC()->session->ma_fest_offer_cart_track_off_id;
            if(isset($orders[$offer]))
            {
                $parsed_orders = explode(',', $orders[$offer]);
                array_push($parsed_orders,$order_id);
                $orders[$offer] = implode(',',$parsed_orders);
                update_option('ma_offer_zone_ordered_orders',$orders);
            }
            else
            {
                $orders[$offer] = $order_id;
                update_option('ma_offer_zone_ordered_orders',$orders);
            }
        }
    }
    
    function display_banners()
    {
        $banner = '';
        $upcoming = ma_get_upcoming_offer();
        if(!empty($upcoming))
        {
            $img = '';
            $span = '';
            $position = array(
                'bottomleft',
                'bottomright',
                'topleft',
                'topright',
                'middle'
            );
            $i=1;
            $p=0;
            foreach ($upcoming as $id) {
                $offer = ma_get_offer_data($id);
                $color = apply_filters('ma_offer_zone_slider_box_color', 'ma-indigo');
                if($offer['banner']!=='')
                {
                    $img .= '<div class="ma-display-container mySlides">
                             <img src="'.$offer['banner'].'" style="width:100%">
                             <div class="ma-display-'.$position[$p].' ma-large ma-container ma-padding-16 '.$color.'" style="background-color:'.$color.'">
                                 <strong>'.$offer['name'].'</strong> starts on <strong>'.$offer['start'].'</strong>
                             </div>
                           </div>';
                    if($p==4)
                    {
                        $p=0;
                    }
                    $i++;
                    $p++;
                }
            }
            if($img!=='')
            {
                $banner= '
                            <style>
                                .ma-left, .ma-right, .ma-badge {cursor:pointer}
                                .ma-badge {height:13px;width:13px;padding:5px;margin-left:5px;}
                            </style>
                            <div class="ma-content ma-display-container">
                                '.$img.'
                                <button class="ma-button ma-display-left '.$color.'" style="background-color:'.$color.'" onclick="plusDivs(-1)">&#10094;</button>
                                <button class="ma-button ma-display-right '.$color.'" style="background-color:'.$color.'" onclick="plusDivs(1)">&#10095;</button>
                            </div>
                            <script>
                                var slideIndex = 1;
                                showDivs(slideIndex);
                                function plusDivs(n) {
                                  showDivs(slideIndex += n);
                                }

                                function currentDiv(n) {
                                  showDivs(slideIndex = n);
                                }

                                function showDivs(n) {
                                    var i;
                                    var x = document.getElementsByClassName("mySlides");
                                    if (n > x.length) {slideIndex = 1}    
                                    if (n < 1) {slideIndex = x.length}
                                    for (i = 0; i < x.length; i++) {
                                       x[i].style.display = "none";  
                                    }
                                    x[slideIndex-1].style.display = "block";  
                                }
                            </script>
                         ';
            }
        }
        echo $banner;
    }
    
    function replace_sales_badge($html,$post,$product)
    {
        $id = $post->ID;
        $avail_off = ma_get_active_offers();
        if($avail_off!=="")
        {
            $offer = ma_get_offer_data($avail_off);
            if(ma_is_product_on_offfer($offer, $id) && ma_get_offer_settings('ma_offers_settings_sale_badge_replace', 'yes') === 'yes')
            {
                return '<span class="onsale">' . $offer['tag'] . '</span>';
            }
        }
        return $html;
    }
    function modify_post_thumbnail_html($html, $post_id) {
        global $wp_query;
        $content = $wp_query->post->post_content;
        $page = has_shortcode($content, 'ma_offer_page');
        $post_page = isset($_GET['post_type'])?$_GET['post_type']:'';
        $avail_off = ma_get_active_offers();
        if($avail_off!=="")
        {
            $offer = ma_get_offer_data($avail_off);
            if(ma_is_product_on_offfer($offer, $post_id) && ma_get_offer_settings('ma_offers_settings_show_offer_badge', 'yes') === 'yes')
            {
                if(is_shop() && ($post_page !== 'product') || is_product_category() || $page)
                {
                    return '<div class="ma-ribbon-image-box">
                                <div class="ma-product-badge-ribbon"><span>'.$offer['tag'].'</span></div>
                                '.$html.'
                            </div>';
                }
            }
        }
        return $html;
    }
    
    function filter_woocommerce_single_product_image_html( $html) {
        global $post;
        $id = $post->ID;
        $avail_off = ma_get_active_offers();
        if($avail_off!=="")
        {
            $offer = ma_get_offer_data($avail_off);
            if(ma_is_product_on_offfer($offer, $id) && ma_get_offer_settings('ma_offers_settings_show_offer_badge', 'yes') === 'yes')
            {
                return '<div class="box">
                        <div class="ma-product-badge-ribbon"><span>'.$offer['tag'].'</span></div>
                        '.$html.'
                    </div>';
            }
        }
        return $html;
    } 
    
    
    
    function set_screen( $status, $option, $value ) {
        return $value;
    }
        
    function add_settings_tab($settings_tabs) {
        $settings_tabs['ma_offers_settings'] = __('Dynamic Deal Settings', 'ma_offers_zone');
        return $settings_tabs;
    }

    function ma_offers_menu_add() {
        add_menu_page(__('Dynamic Deals','wsdesk'), "Dynamic Deals", "manage_woocommerce", "ma_offers_products", array($this, 'ma_offers_products_tab'), MA_OFF_MAIN_IMG."menu_icon.png", 35);
        $hook = add_submenu_page('ma_offers_products', 'Dynamic Deals', 'Dynamic Deals', "manage_woocommerce", 'ma_offers_products', array($this, 'ma_offers_products_tab'));
        add_submenu_page('ma_offers_products', 'Deal Settings', 'Deal Settings', "manage_woocommerce", 'wc-settings&tab=ma_offers_settings',  array($this->menu_settings, 'get_offer_settings'));
        add_submenu_page('ma_offers_products', 'Pro Featuers', 'Pro Featuers', "manage_woocommerce", 'ma_offers_pro_features', array($this, 'ma_offers_pro_features_tab'));
        add_action( "load-$hook", [ $this, 'screen_option' ] );
    }
    
    function screen_option() {
        $option = 'per_page';
        $args   = [
            'label'   => 'Available Deals',
            'default' => 20,
            'option'  => 'results_per_page'
        ];
        add_screen_option( $option, $args );
        new MA_Available_Offers();
    }
        
    function ma_offers_register_styles_scripts() {
        $page = (isset($_GET['page']) ? $_GET['page'] : '');
        $tab = (isset($_GET['tab']) ? $_GET['tab'] : '');
        $edit= (!empty($_GET['edit']))? esc_attr($_GET['edit']) : '';
        $view= (!empty($_GET['view']))? esc_attr($_GET['view']) : '';
        if ($page === "wc-settings" && $tab === 'ma_offers_settings') {
            wp_enqueue_script("jquery");
            wp_enqueue_script("ma_offers_settings", MA_OFF_MAIN_JS . "ma_offers_settings.js",array('jquery'));
        }
        if ($page === "ma_offers_products") {
            wp_enqueue_script("jquery");
            wp_enqueue_script('wc-enhanced-select');
            wp_enqueue_style( 'woocommerce_admin_styles');
            wp_enqueue_script("alertify", MA_OFF_MAIN_JS . "alertify.min.js",array('jquery'));
            wp_enqueue_style("alertify", MA_OFF_MAIN_CSS . "alertify.css");
            wp_enqueue_style("alertify_def", MA_OFF_MAIN_CSS . "alertify.default.css");
            if($view !== '')
            {
                wp_enqueue_script("bootstrap", MA_OFF_MAIN_JS . "bootstrap.js",array('jquery'));
                wp_enqueue_style("bootstrap", MA_OFF_MAIN_CSS . "bootstrap.css");
            }
            $tab = (isset($_GET['tab']) ? $_GET['tab'] : 'available_offers');
            if($tab === "new_offer" || $edit !=='')
            {
                wp_enqueue_media();
                if($edit === '')
                {
                    wp_enqueue_script("ma_new_offers", MA_OFF_MAIN_JS . "ma_new_offers.js",array('jquery'));
                }
                else
                {
                    wp_enqueue_script("ma_edit_offers", MA_OFF_MAIN_JS . "ma_edit_offers.js",array('jquery'));
                }
                wp_enqueue_style("ma_new_offers", MA_OFF_MAIN_CSS . "ma_new_offers.css");
                wp_enqueue_script("moment", MA_OFF_MAIN_JS . "moment.min.js",array('jquery'));
                wp_enqueue_script("bootstrap", MA_OFF_MAIN_JS . "bootstrap.js",array('jquery'));
                wp_enqueue_script("daterangepicker", MA_OFF_MAIN_JS . "daterangepicker.js",array('jquery'));
                wp_enqueue_style("daterangepicker", MA_OFF_MAIN_CSS . "daterangepicker.css");
                wp_enqueue_style("bootstrap", MA_OFF_MAIN_CSS . "bootstrap.css");
            }
            if($tab === "available_offers")
            {
                wp_enqueue_script("ma_available_offers", MA_OFF_MAIN_JS . "ma_available_offers.js",array('jquery'));
                wp_enqueue_style("ma_available_offers", MA_OFF_MAIN_CSS . "ma_available_offers.css");
            }
        }
        if($page === "ma_offers_pro_features")
        {
            wp_enqueue_style("bootstrap", MA_OFF_MAIN_CSS . "bootstrap.css");
        }
    }
    
    function ma_offers_products_tab() {
        $tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'available_offers';
        $edit= (!empty($_GET['edit']))? esc_attr($_GET['edit']) : '';
        $delete= (!empty($_GET['delete']))? esc_attr($_GET['delete']) : '';
        $view= (!empty($_GET['view']))? esc_attr($_GET['view']) : '';
        if($delete!=='')
        {
            $data = get_option("ma_fest_offers");
            if(isset($data[$delete]))
            {
                unset($data[$delete]);
                update_option("ma_fest_offers", $data);
            }
        }
        if($edit==='' && $view === '')
        {
            echo '
                <div class="wrap">
                    <h1 class="wp-heading-inline">Deals and Discounts</h1>
                    <a href="'. admin_url("admin.php?page=wc-settings&tab=ma_offers_settings").'" class="page-title-action" target="_blank">Deal Settings</a>
                    <hr class="wp-header-end">';
                $this->ma_offers_product_page_tabs($tab);
                switch($tab)
                {
                    case "available_offers":
                        echo '<div class="table-box table-box-main" id="available_offers_section" style="margin-top: 10px;">';
                            echo $this->menu_settings->ma_avilable_offers_tab_callback(); 
                        echo '</div>';
                        break;
                    case "new_offer":
                        echo '<div class="table-box table-box-main" id="new_offer_section" style="margin-top: 10px;">';
                            echo $this->menu_settings->ma_new_offer_tab_callback(); 
                        echo '</div>';
                        break;
                }
            echo '
                </div>';
        }
        else
        {
            if($edit!=='' && $view === "")
            {
                echo $this->menu_settings->ma_offer_edit_callback($edit);
            }
            if($view!=='' && $edit === "")
            {
                echo $this->menu_settings->ma_offer_view_callback($view);
            }
        }
    }
    
    function ma_offers_pro_features_tab()
    {
        include(MA_OFF_MAIN_VIEWS . "upgrade_premium.php");
    }
    
    function ma_offers_product_page_tabs($current = 'available_offers') {
        $tabs = array(
            'available_offers'   => __("Available Deals", 'ma_offers_zone'), 
            'new_offer'  => __("New Deal", 'ma_offers_zone')
        );
        $html =  '<h2 class="nav-tab-wrapper">';
        foreach( $tabs as $tab => $name ){
            $class = ($tab == $current) ? 'nav-tab-active' : '';
            $style = ($tab == $current) ? 'border-bottom: 1px solid transparent !important;' : '';
            $html .=  '<a style="text-decoration:none !important;'.$style.'" class="nav-tab ' . $class . '" href="?page=ma_offers_products&tab=' . $tab . '">' . $name . '</a>';
        }
        $html .= '</h2>';
        echo $html;
    }
    
}
