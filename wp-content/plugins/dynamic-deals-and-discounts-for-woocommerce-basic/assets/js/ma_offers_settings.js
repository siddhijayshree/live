jQuery( document ).ready( function() {
    jQuery('.description').css({'font-style':'normal'});
    jQuery('#ma_offers_zone_settings_ad_image').on( 'change', function() {
        var ad_section    = jQuery( '#ma_offers_zone_settings_show_ad_image_url, #ma_offers_zone_settings_show_ad_image_shop, #ma_offers_zone_settings_show_ad_image_cart, #ma_offers_zone_settings_show_ad_image_checkout, #ma_offers_zone_settings_show_ad_image_position' ).closest( 'tr' );
        if ( jQuery( this ).is( ':checked' ) ) {
                ad_section.show();
        } else {
                ad_section.hide();
        }
    }).change();
});