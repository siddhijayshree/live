jQuery(function () {
    jQuery('#ma_fest_offers_wrap').on('click',".ma_deactive_offer",function(){
        var btn = jQuery(this);
        btn.prop("disabled","disabled");
        var id = jQuery(btn).prop('id');
        alertify.confirm("Do you want to deactivate the Offer?", function (e) {
            if (e) {
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl,
                    data: 
                    {
                        action  : 'ma_offer_edit_status',
                        id      : id,
                        status  : 'deactive'
                    },
                    success: function (data) {
                        btn.removeProp("disabled");
                        window.location.href = data;
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                    }
                });
            } else {
                alertify.error("Offer Status Update Cancelled!");
            }
        });
        btn.removeProp("disabled");
    });
    jQuery('#ma_fest_offers_wrap').on('click',".ma_active_offer",function(){
        var btn = jQuery(this);
        btn.prop("disabled","disabled");
        var id = jQuery(btn).prop('id');
        alertify.confirm("Do you want to activate the Offer?", function (e) {
            if (e) {
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl,
                    data: 
                    {
                        action  : 'ma_offer_edit_status',
                        id      : id,
                        status  : 'active'
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
            } else {
                alertify.error("Offer Status Update Cancelled!");
            }
        });
        btn.removeProp("disabled");
    });
    jQuery('#ma_fest_offers_wrap').on('click',".edit_offer",function(){
        var btn = jQuery(this);
        btn.prop("disabled","disabled");
        var id = jQuery(btn).prop('id');
        alertify.confirm("Do you want to activate the Offer?", function (e) {
            if (e) {
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl,
                    data: 
                    {
                        action  : 'ma_offer_edit_status',
                        id      : id,
                        status  : 'active'
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
            } else {
                alertify.error("Offer Status Update Cancelled!");
            }
        });
        btn.removeProp("disabled");
    });
});