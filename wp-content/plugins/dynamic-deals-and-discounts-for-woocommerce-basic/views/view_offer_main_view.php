<?php
$data = ma_get_offer_data($id);
$html = '';
if($data['by']==="price")
{
    $html=get_woocommerce_currency_symbol().$data['unit'];
}
else
{
    $html=$data['unit']."%";
}
?>
<div class="wrap" id="edit_offer_section">
    <h1 class="wp-heading-inline">View : <?php echo stripslashes($data['name'])." ( ".$id." )"; ?></h1>
    <a href="<?php echo admin_url("admin.php?page=ma_offers_products"); ?>" class="page-title-action" >Back</a>
    <hr class="wp-header-end">
    <div class="col-lg-12" style="border-bottom: 1px solid lightgray">
        <table class="form-table col-lg-12" style="border-top: 1px solid lightgray">
            <tr valign="top">
                <td>
                    Offer Name
                </td>
                <td>-</td>
                <td>
                    <?php echo stripslashes($data['name']); ?>s
                </td>
                <td>
                    Discount Text
                </td>
                <td><i class="glyphicon glyphicon-calendar" /></td>
                <td>
                    <?php echo str_replace('{ma_discount}', $html, $data['discount_badge']); ?>
                </td>
            </tr>
            <tr valign="top">
                <td>
                    Starting date
                </td>
                <td><i class="glyphicon glyphicon-calendar" /></td>
                <td>
                    <?php echo $data['start']; ?>
                </td>
                <td>
                    Ending date
                </td>
                <td><i class="glyphicon glyphicon-calendar" /></td>
                <td>
                    <?php echo $data['end']; ?>
                </td>
            </tr>
            <tr valign="top">
                <td>
                    Ad Image ( 831 x 315 )px
                </td>
                <td><i class="glyphicon glyphicon-picture"/></td>
                <td>
                    <a href="<?php echo $data['banner'];?>" target="_blank">View Image</a>
                </td>
                <td>
                    Image Badge Text
                </td>
                <td><i class="glyphicon glyphicon-tags"/></td>
                <td>
                    <?php echo stripslashes($data['tag']); ?>
                </td>
            </tr>
        </table>
        <div class="col-lg-4 col-lg-offset-4" style="text-align: center;<?php echo ($data['banner']!==''?'':'display: none'); ?>" id="preview_ad_image_div">
            <img src="<?php echo $data['banner']; ?>" id="preview_ad_image" style="width: 100%;height: 175px;">
        </div>
    </div>
    <div class="col-lg-12" style="border-bottom: 1px solid lightgray;margin-top: 10px;" id="offer_details_div">
        <h4>
            Cart Offer Details
        </h4>
        <table class="form-table col-lg-12">
            <tr valign="top">
                <td>
                    Offer on
                </td>
                <td>-</td>
                <td>
                    <?php echo ucfirst($data['on']); ?>
                </td>
            </tr>
            <tr valign="top">
                <td>
                    Applied <?php echo ucfirst($data['on']); ?>
                </td>
                <td>
                    <?php
                        if($data['on'] === 'products')
                        {
                            echo '<i class="glyphicon glyphicon-th"/>';
                        }
                        else
                        {
                            echo '<i class="glyphicon glyphicon-tasks"/>';
                        }
                    ?>
                </td>
                <td>
                    <?php
                        $on_ids = explode(',', $data['ids']);
                        if($data['on'] === 'products')
                        {
                            foreach ( $on_ids as $product_id ) {
                                $product = wc_get_product( $product_id );
                                if ( is_object( $product ) ) {
                                        echo wp_kses_post( $product->get_formatted_name() ).", ";
                                }
                            }
                        }
                        else
                        {
                            $cat =array();
                            $product_category=get_terms( 'product_cat', array('hide_empty'=>false,'orderby' => 'title', 'order' => 'ASC',));
                            if ($product_category) 
                            {
                                foreach ( $product_category as $category=>$current) {
                                    if((in_array($current->slug, $on_ids)&& $data['on'] === 'categories'))
                                    {
                                        array_push($cat, $current->name);
                                    }
                                }
                                echo implode(", ", $cat);
                            }
                        }
                    ?>
                </td>
            </tr>
            
            <tr valign="top">
                <td>
                    Discount
                </td>
                <td>-</td>
                <td>
                    <?php echo ucfirst($data['by']); ?>
                </td>
            </tr>

            <tr valign="top">
                <td>
                    Applied <?php echo ucfirst($data['by']); ?>
                </td>
                <td>
                    <?php
                        if($data['by'] === 'price')
                        {
                            echo get_woocommerce_currency_symbol();
                        }
                        else
                        {
                            echo '%';
                        }
                    ?>
                </td>
                <td>
                    <?php
                        if($data['by']==="price")
                        {
                            echo get_woocommerce_currency_symbol().$data['unit'];
                        }
                        else
                        {
                            echo $data['unit']."%";
                        }
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>