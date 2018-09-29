<div class="wrap" id="ma_fest_offers_wrap">
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="get" action="<?php echo admin_url("admin.php"); ?>">
                        <input type="hidden" name="page" value="ma_offers_products">
                        <?php
                        $table = new MA_Available_Offers();
                        $table->prepare_items();
                        $table->display();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>