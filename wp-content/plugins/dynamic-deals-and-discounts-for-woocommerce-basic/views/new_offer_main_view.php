<div class="col-lg-12" style="border-bottom: 1px solid lightgray">
    <h4>
        General Details
    </h4>
    <table class="form-table col-lg-8" style="border-top: 1px solid lightgray">
        <tr valign="top">
            <th scope="row">
                Offer Name
            </th>
            <td colspan="2">
                <input type="text" id="offer_name" class="form-control" style="padding:3px 12px !important;height: 30px !important;"/>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Starting date
                <i class="glyphicon glyphicon-calendar" style="float: right;margin-top: 2px;"></i>
            </th>
            <td colspan="2">
                <input type="text" id="starting_date" class="form-control" style="padding:3px 12px !important;height: 30px !important;"/>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Ending date
                <i class="glyphicon glyphicon-calendar" style="float: right;margin-top: 2px;"></i>
            </th>
            <td colspan="2">
                <input type="text" id="ending_date" class="form-control" style="padding:3px 12px !important;height: 30px !important;"/>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Ad Image ( 831 x 315 )px
                <i class="glyphicon glyphicon-picture" style="float: right;margin-top: 2px;"></i>
            </th>
            <td>
                <input type="text" id="ad_image_url_text" class="form-control" style="padding:3px 12px !important;height: 30px !important;"/>
            </td>
            <td>
                <button class="button button-default" id="ad_image_url_chooser">Choose from Library</button>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                Image Badge Text
                <i class="glyphicon glyphicon-tags" style="float: right;margin-top: 2px;"></i>
            </th>
            <td colspan="2">
                <input type="text" id="image_tag_text" value="Great Deal" class="form-control" style="padding:3px 12px !important;height: 30px !important;"/>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                Discount Badge Text
                <i class="glyphicon glyphicon-tags" style="float: right;margin-top: 2px;"></i>
            </th>
            <td colspan="2">
                <input type="text" id="discount_tag_text" value="Cart Discount - {ma_discount}" class="form-control" style="padding:3px 12px !important;height: 30px !important;"/>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                Discount Badge Position
                <i class="glyphicon glyphicon-tasks" style="float: right;margin-top: 2px;"></i>
            </th>
            <td colspan="2">
                <select class="wc-enhanced-select" id="discount_tag_position" style="width: 100%;">
                    <option value="up_price">Above Price</option>
                    <option value="down_price">Below Price</option>
                </select>
            </td>
        </tr>
    </table>
    <div class="col-lg-4" style="text-align: center;display: none" id="preview_ad_image_div">
        <h4>Preview</h4>
        <img src="" id="preview_ad_image" style="width: 100%;height: 175px;">
    </div>
</div>
<div class="col-lg-12" style="border-bottom: 1px solid lightgray;margin-top: 10px;" id="offer_details_div">
    <h4>
        Cart Offer Details
    </h4>
    <table class="form-table col-lg-8" style="border-top: 1px solid lightgray">
        <tr valign="top">
            <th scope="row">
                Cart Offer
            </th>
            <td>
                <input type="radio" checked name="offer_on" id="offer_on" class="form-control" value="products" /> On Products
                <input type="radio" style="margin-left: 5px;" name="offer_on" id="offer_on" class="form-control" value="categories"/> On Categories
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Choose Products
                <i class="glyphicon glyphicon-th" style="float: right;margin-top: 2px;"></i>
            </th>
            <td>
                <?php
                    if(WC()->version<"2.7.0")
                    {
                        ?>
                            <input type="hidden" class="wc-product-search" data-multiple="true"  id="offer_product_id" name="offer_product_id" data-placeholder="Search for a product" data-action="woocommerce_json_search_products" style="width: 100%;" />
                        <?php
                    }
                    else
                    {
                        ?>
                            <select id="offer_product_id" class="wc-product-search" name="offer_product_id" multiple="multiple" style="width: 100%;" data-placeholder="Search for a product" data-action="woocommerce_json_search_products"></select>
                        <?php
                    }
                ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                Choose Categories
                <i class="glyphicon glyphicon-tasks" style="float: right;margin-top: 2px;"></i>
            </th>
            <td>
                <select multiple class="wc-enhanced-select" id="offer_category_id" data-placeholder="Search for a category" style="width: 100%;">
                <?php
                    $product_category=get_terms( 'product_cat', array('hide_empty'=>false,'orderby' => 'title', 'order' => 'ASC'));
                    if ($product_category)
                    {
                        foreach ( $product_category as $category=>$current) {
                            echo '<option value="' . $current->slug .'">' .$current->name. '</option>';
                        }
                    }
                ?>
                </select>
            </td>
        </tr>
    </table>
</div>
<div class="col-lg-12" style="border-bottom: 1px solid lightgray;margin-top: 10px;" id="discount_details_div">
    <h4>
        Cart Discount Details
    </h4>
    <table class="form-table col-lg-8" style="border-top: 1px solid lightgray">
        <tr valign="top">
            <th scope="row">
                Cart Discount
            </th>
            <td>
                <input type="radio" checked name="discount_by" id="discount_by" class="form-control" value="price" /> By Flat Price
                <input type="radio" style="margin-left: 5px;" name="discount_by" id="discount_by" class="form-control" value="percentage"/> By Percentage
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                Discount Price
                <i class="glyphicon" style="float: right;margin-top: 2px;"><?php echo get_woocommerce_currency_symbol();?></i>
            </th>
            <td>
                <input type="number" id="discount_flat_price" class="form-control" style="padding:3px 12px !important;height: 30px !important;"/>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                Discount Percentage
                <i class="glyphicon" style="float: right;margin-top: 2px;">%</i>
            </th>
            <td>
                <input type="number" id="discount_percentage" class="form-control" style="padding:3px 12px !important;height: 30px !important;"/>
            </td>
        </tr>
    </table>
</div>
<div class="col-lg-12" style="margin-top: 10px;">
    <button class="button button-primary" id="new_offer_add_button">Add Offer</button>
</div>