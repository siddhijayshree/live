<?php if ( ! defined( 'ABSPATH' ) ) exit;
$settings = get_option('fmaoptions');
$path = str_replace('\\','/', ABSPATH)
?>
<input type="hidden" name="_fmakey" id="fmakey" value="<?php echo wp_create_nonce( 'fmaskey' ); ?>">
<input type="hidden" name="fma_locale" id="fma_locale" value="<?php echo isset($settings['fma_locale']) ? $settings['fma_locale'] : 'en';?>">
<div class="wrap fma">
<h2><?php _e('File Manager Advanced','file-manager-advanced')?> <?php if(!class_exists('file_manager_advanced_shortcode')) { ?><a href="http://modalwebstore.com/product/file-manager-advanced-shortcode/" class="button button-primary" target="_blank"><?php _e('Buy Shortcode Addon','file-manager-advanced')?></a><?php } ?></h2>
<hr>
<div id="file_manager_advanced"><center><img src="<?php echo plugins_url( 'images/wait.gif', __FILE__ );?>"></center></div>
<p style="width:100%; text-align:right;" class="description">
<span id="thankyou"><?php _e('Thank you for using <a href="https://wordpress.org/plugins/file-manager-advanced/">File Manager Advanced</a>. If happy then ','file-manager-advanced')?>
<a href="https://wordpress.org/support/plugin/file-manager-advanced/reviews/?filter=5"><?php _e('Rate Us','file-manager-advanced')?> <img src="<?php echo plugins_url( 'images/5stars.png', __FILE__ );?>" style="width:100px; top: 11px; position: relative;"></a></span>
</p>
<hr>
 <table>
 <tr>
 <th><a href="http://modalwebstore.com/file-manager-wordress/" class="button" target="_blank"><?php _e('Documentation','file-manager-advanced')?></a></th>
 <th><a href="http://modalwebstore.com/contact-us/" class="button" target="_blank"><?php _e('Support','file-manager-advanced')?></a></th>
 <th><a href="http://modalwebstore.com/product/file-manager-advanced-shortcode/" class="button button-primary" target="_blank"><?php _e('Shortcodes','file-manager-advanced')?></a></th>
 </tr>
</table>
</div>