jQuery(function () {
    jQuery("#offer_product_id").trigger("wc-enhanced-select-init");
    offer_details_div_change();
    discount_details_div_change();
    jQuery('#starting_date').daterangepicker({
        singleDatePicker: true,
        timePicker: true,
    //   minDate: moment(),
        locale: 
        {
            format: 'MM/DD/YYYY h:mm A'
        }
    });
    jQuery('#ending_date').daterangepicker({
        singleDatePicker: true,
        timePicker: true,
      //  minDate: moment(),
        locale: 
        {
            format: 'MM/DD/YYYY h:mm A'
        }
    });
    jQuery('#offer_details_div').on('change',"#offer_on",offer_details_div_change);
    jQuery('#discount_details_div').on('change',"#discount_by",discount_details_div_change);
    jQuery('#edit_offer_section').on('click',"#edit_offer_button",function(){
        var btn = jQuery("#edit_offer_button");
        btn.prop("disabled","disabled");
        var id = name = start = end = banner = tag = offer_on = ids = discount_by = unit = discount_text = discount_text_p = "";
        id          = jQuery("#offer_id").val();
        name        = jQuery("#offer_name").val();
        start       = jQuery("#starting_date").val();
        end         = jQuery("#ending_date").val();
        banner      = jQuery("#ad_image_url_text").val();
        tag         = jQuery("#image_tag_text").val();
        discount_text   = jQuery("#discount_tag_text").val();
        discount_text_p = jQuery("#discount_tag_position").val();
        offer_on    = jQuery("input[name='offer_on']:checked").val();
        if(offer_on === "products")
        {
            ids = jQuery("#offer_product_id").val();
        }
        else
        {
            ids = jQuery("#offer_category_id").val();
        }
        discount_by = jQuery("input[name='discount_by']:checked").val();
        if(discount_by === "price")
        {
            unit = jQuery("#discount_flat_price").val();
        }
        else
        {
            unit = jQuery("#discount_percentage").val();
        }
        if(name === "")
        {
            jQuery("#offer_name").css("border","1px solid red");
            btn.removeProp("disabled");
            jQuery('html, body').animate({
                scrollTop: jQuery(".page-title-action").offset().top
            }, 1000);
            return;
        }
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: 
            {
                action      : 'ma_offer_edit',
                id          : id,
                name        : name,
                start       : start,
                end         : end,
                banner      : banner,
                tag         : tag,
                offer_on    : offer_on,
                ids         : (ids !== null) ? ids.join(",") : "",
                discount_by : discount_by,
                unit        : unit,
                discount_t  : discount_text,
                discount_p  : discount_text_p
            },
            success: function (data) {
                btn.removeProp("disabled");
                var response = jQuery.parseJSON(data);
                if(response.status === 'success')
                {
                    window.location.href = response.url;
                }
                else
                {
                    alertify.error(response.message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
        btn.removeProp("disabled");
    });
});
jQuery( document ).ready( function( $ ) {
    var file_frame;
    jQuery('#ad_image_url_chooser').on('click', function( event ){
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select a image to set Ad image for Offer',
            button: {
                    text: 'Use this image',
            },
            multiple: false
        });
        file_frame.on( 'select', function() {
            attachment = file_frame.state().get('selection').first().toJSON();
            $('#ad_image_url_text').val( attachment.url );
            jQuery('#preview_ad_image_div').css({'display':'block'});
            jQuery('#preview_ad_image').prop("src",attachment.url);
        });
        file_frame.open();
    });
});
function offer_details_div_change()
{
    var val = jQuery("input[name='offer_on']:checked").val();
    if(val==="products")
    {
        jQuery( '#offer_product_id').closest( 'tr' ).show();
        jQuery( '#offer_category_id').closest( 'tr' ).hide();
    }
    else
    {
        jQuery( '#offer_product_id').closest( 'tr' ).hide();
        jQuery( '#offer_category_id').closest( 'tr' ).show();
    }
}
function discount_details_div_change()
{
    var val = jQuery("input[name='discount_by']:checked").val();
    if(val==="price")
    {
        jQuery( '#discount_flat_price').closest( 'tr' ).show();
        jQuery( '#discount_percentage').closest( 'tr' ).hide();
    }
    else
    {
        jQuery( '#discount_flat_price').closest( 'tr' ).hide();
        jQuery( '#discount_percentage').closest( 'tr' ).show();
    }
}