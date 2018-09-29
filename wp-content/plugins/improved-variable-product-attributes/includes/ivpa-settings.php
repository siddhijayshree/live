<?php

	class WC_Ivpa_Settings {

		public static function init() {
			add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::ivpa_add_settings_tab', 50 );
			add_action( 'woocommerce_settings_tabs_ivpawoo', __CLASS__ . '::ivpa_settings_tab' );
			add_action( 'woocommerce_update_options_ivpawoo', __CLASS__ . '::ivpa_save_settings' );
			add_action( 'admin_enqueue_scripts', __CLASS__ . '::ivpa_settings_scripts' );
			add_action( 'wp_ajax_ivpa_get_fields', __CLASS__ . '::ivpa_get_fields' );
			add_action( 'wp_ajax_ivpa_get_terms', __CLASS__ . '::ivpa_get_terms' );
		
			if ( get_option( 'wc_settings_ivpa_use_caching', 'no' ) == 'yes' ) {
				add_action( 'save_post', __CLASS__ . '::delete_caches', 10, 3 );
			}
		}

		public static function ivpa_settings_scripts( $settings_tabs ) {
			if ( isset($_GET['page'], $_GET['tab']) && ($_GET['page'] == 'wc-settings' || $_GET['page'] == 'woocommerce_settings') && $_GET['tab'] == 'ivpawoo' ) {
				wp_enqueue_style( 'ivpa-style', Wcmnivpa()->plugin_url() . '/assets/css/admin.css', false, WC_Improved_Variable_Product_Attributes_Init::$version );
				wp_enqueue_script( 'ivpa-admin', Wcmnivpa()->plugin_url() . '/assets/js/admin.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable' ), WC_Improved_Variable_Product_Attributes_Init::$version, true );
				$curr_args = array(
					'ajax' => admin_url( 'admin-ajax.php' ),
				);
				wp_localize_script( 'ivpa-admin', 'ivpa', $curr_args );

				if ( function_exists( 'wp_enqueue_media' ) ) {
					wp_enqueue_media();
				}

				wp_enqueue_style('wp-color-picker');
				wp_enqueue_script('wp-color-picker');
			}
		}

		public static function ivpa_add_settings_tab( $settings_tabs ) {
			$settings_tabs['ivpawoo'] = __( 'Improved Variable Product Attributes', 'ivpawoo' );
			return $settings_tabs;
		}

		public static function ivpa_settings_tab() {
			woocommerce_admin_fields( self::ivpa_get_settings( 'get' ) );
		}

		public static function ivpa_save_settings() {

			if ( isset ( $_POST['ivpa_attr'] ) ) {

				$ivpa_attrs = array();

				for ( $i = 0; $i < count( $_POST['ivpa_attr'] ); $i++ ) {

					if ( $_POST['ivpa_attr'][$i] !== '' ) {

						$ivpa_attrs['ivpa_attr'][$i] = $_POST['ivpa_attr'][$i];
						$ivpa_attrs['ivpa_title'][$i] = !empty( $_POST['ivpa_title'][$i] ) ? stripslashes( $_POST['ivpa_title'][$i] ) : __( 'Option Name', 'ivpawoo' );
						$ivpa_attrs['ivpa_desc'][$i] = stripslashes($_POST['ivpa_desc'][$i]);
						$ivpa_attrs['ivpa_style'][$i] = $_POST['ivpa_style'][$i];
						$ivpa_attrs['ivpa_archive_include'][$i] = isset( $_POST['ivpa_archive_include'][$i] ) ? 'yes' : 'no';
						$ivpa_attrs['ivpa_svariation'][$i] = isset( $_POST['ivpa_svariation'][$i] ) ? 'yes' : 'no';
						$ivpa_attrs['ivpa_multiselect'][$i] = isset( $_POST['ivpa_multiselect'][$i] ) ? 'yes' : 'no';

						if ( $_POST['ivpa_attr'][$i] == 'ivpa_custom' ) {

							$ivpa_attrs['ivpa_addprice'][$i] = isset( $_POST['ivpa_addprice'][$i] ) ? $_POST['ivpa_addprice'][$i] : '';
							$ivpa_attrs['ivpa_limit_type'][$i] = isset( $_POST['ivpa_limit_type'][$i] ) ? $_POST['ivpa_limit_type'][$i] : '';
							$ivpa_attrs['ivpa_limit_category'][$i] = isset( $_POST['ivpa_limit_category'][$i] ) ? $_POST['ivpa_limit_category'][$i] : '';
							$ivpa_attrs['ivpa_limit_product'][$i] = isset( $_POST['ivpa_limit_product'][$i] ) ? $_POST['ivpa_limit_product'][$i] : '';

							if ( isset( $_POST['ivpa_name'][$i] ) && is_array( $_POST['ivpa_name'][$i] ) ) {
								foreach ( $_POST['ivpa_name'][$i] as $k => $v ) {
									$ivpa_attrs['ivpa_name'][$i][$k] = $v;
								}
							}

							if ( isset( $_POST['ivpa_price'][$i] ) && is_array( $_POST['ivpa_price'][$i] ) ) {
								foreach ( $_POST['ivpa_price'][$i] as $k => $v ) {
									$ivpa_attrs['ivpa_price'][$i][$k] = $v;
								}
							}

						}

						if ( isset( $_POST['ivpa_size'][$i] ) ) {
							$ivpa_attrs['ivpa_size'][$i] = intval( $_POST['ivpa_size'][$i] );
						}

						switch ( $ivpa_attrs['ivpa_style'][$i] ) {

							case 'ivpa_text' :
								$ivpa_attrs['ivpa_custom'][$i]['style'] = $_POST['ivpa_term'][$i]['style'];
								$ivpa_attrs['ivpa_custom'][$i]['normal'] = $_POST['ivpa_term'][$i]['normal'];
								$ivpa_attrs['ivpa_custom'][$i]['active'] = $_POST['ivpa_term'][$i]['active'];
								$ivpa_attrs['ivpa_custom'][$i]['disabled'] = $_POST['ivpa_term'][$i]['disabled'];
								$ivpa_attrs['ivpa_custom'][$i]['outofstock'] = $_POST['ivpa_term'][$i]['outofstock'];
								foreach ( $_POST['ivpa_tooltip'][$i] as $k => $v ) {
									$ivpa_attrs['ivpa_tooltip'][$i][$k] = $v;
								}
							break;

							case 'ivpa_color' :
								foreach ( $_POST['ivpa_term'][$i] as $k => $v ) {
									$ivpa_attrs['ivpa_custom'][$i][$k] = $v;
								}
								foreach ( $_POST['ivpa_tooltip'][$i] as $k => $v ) {
									$ivpa_attrs['ivpa_tooltip'][$i][$k] = $v;
								}
							break;

							case 'ivpa_image' :
								foreach ( $_POST['ivpa_term'][$i] as $k => $v ) {
									$ivpa_attrs['ivpa_custom'][$i][$k] = $v;
								}
								foreach ( $_POST['ivpa_tooltip'][$i] as $k => $v ) {
									$ivpa_attrs['ivpa_tooltip'][$i][$k] = $v;
								}
							break;

							case 'ivpa_html' :
								foreach ( $_POST['ivpa_term'][$i] as $k => $v ) {
									$ivpa_attrs['ivpa_custom'][$i][$k] = stripslashes($v);
								}
								foreach ( $_POST['ivpa_tooltip'][$i] as $k => $v ) {
									$ivpa_attrs['ivpa_tooltip'][$i][$k] = $v;
								}
							break;

							default :
							break;

						}
					}
				}
			}
			else {
				$ivpa_attrs = array();
			}

			global $wpdb;
			$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta WHERE meta.meta_key LIKE '_ivpa_cached_%';" );

			$get_language = self::ivpa_wpml_language();

			if ( $get_language === false ) {
				update_option( 'wc_ivpa_attribute_customization', $ivpa_attrs );
			}
			else {
				update_option( 'wc_ivpa_attribute_customization_' . $get_language, $ivpa_attrs );
			}

			woocommerce_update_options( self::ivpa_get_settings( 'update' ) );

		}

		public static function ivpa_get_settings( $action = 'get' ) {

			$settings = array();

			if ( $action == 'get' ) {

		?>
			<div id="ivpa_manager" class="ivpa_manager">
				<h3><?php _e( 'Attribute Customization Manager', 'ivpawoo' ); ?></h3>
				<p><?php _e( 'Use the manager to customize your attributes! Click the Add Attribute Customization button to start customizing!', 'ivpawoo' ); ?></p>
				<div class="ivpa_fields">
					<a href="#" class="ivpa_add_customization button-primary"><?php _e( 'Add Attribute Customization', 'ivpawoo' ); ?></a>
					<a href="#" class="ivpa_add_custom_option button-primary"><?php _e( 'Add Custom Option', 'ivpawoo' ); ?></a>
					<button type="submit" class="button"><?php _e( 'Save changes', 'ivpawoo' ); ?></button>
				</div>
				<div class="ivpa_customizations">
			<?php

				$curr_language = self::ivpa_wpml_language();

				if ( isset( $_POST['ivpa_attr'] ) && 1==0 ) {

					$ivpa_attrs = array();

					for ( $i = 0; $i < count($_POST['ivpa_attr']); $i++ ) {

						if ( $_POST['ivpa_attr'][$i] !== '' ) {

							$ivpa_attrs['ivpa_attr'][$i] = $_POST['ivpa_attr'][$i];
							$ivpa_attrs['ivpa_title'][$i] = stripslashes($_POST['ivpa_title'][$i]);
							$ivpa_attrs['ivpa_desc'][$i] = stripslashes($_POST['ivpa_desc'][$i]);
							$ivpa_attrs['ivpa_style'][$i] = $_POST['ivpa_style'][$i];
							$ivpa_attrs['ivpa_archive_include'][$i] = isset( $_POST['ivpa_archive_include'][$i] ) ? 'yes' : 'no';
							$ivpa_attrs['ivpa_svariation'][$i] = isset( $_POST['ivpa_svariation'][$i] ) ? 'yes' : 'no';
							$ivpa_attrs['ivpa_multiselect'][$i] = isset( $_POST['ivpa_multiselect'][$i] ) ? 'yes' : 'no';

							if ( $_POST['ivpa_attr'][$i] == 'ivpa_custom' ) {

								$ivpa_attrs['ivpa_addprice'][$i] = isset( $_POST['ivpa_addprice'][$i] ) ? $_POST['ivpa_addprice'][$i] : '';
								$ivpa_attrs['ivpa_limit_type'][$i] = isset( $_POST['ivpa_limit_type'][$i] ) ? $_POST['ivpa_limit_type'][$i] : '';
								$ivpa_attrs['ivpa_limit_category'][$i] = isset( $_POST['ivpa_limit_category'][$i] ) ? $_POST['ivpa_limit_category'][$i] : '';
								$ivpa_attrs['ivpa_limit_product'][$i] = isset( $_POST['ivpa_limit_product'][$i] ) ? $_POST['ivpa_limit_product'][$i] : '';

								if ( isset( $_POST['ivpa_name'][$i] ) && is_array( $_POST['ivpa_name'][$i] ) ) {
									foreach ( $_POST['ivpa_name'][$i] as $k => $v ) {
										$ivpa_attrs['ivpa_name'][$i][sanitize_title( $v )] = $v;
									}
								}

								if ( isset( $_POST['ivpa_price'][$i] ) && is_array( $_POST['ivpa_price'][$i] ) ) {
									foreach ( $_POST['ivpa_price'][$i] as $k => $v ) {
										$ivpa_attrs['ivpa_price'][$i][sanitize_title( $v )] = $v;
									}
								}

							}

							if ( isset( $_POST['ivpa_size'][$i] ) ) {
								$ivpa_attrs['ivpa_size'][$i] = intval( $_POST['ivpa_size'][$i] );
							}

							switch ( $ivpa_attrs['ivpa_style'][$i] ) {

								case 'ivpa_text' :
									$ivpa_attrs['ivpa_custom'][$i]['style'] = $_POST['ivpa_term'][$i]['style'];
									$ivpa_attrs['ivpa_custom'][$i]['normal'] = $_POST['ivpa_term'][$i]['normal'];
									$ivpa_attrs['ivpa_custom'][$i]['active'] = $_POST['ivpa_term'][$i]['active'];
									$ivpa_attrs['ivpa_custom'][$i]['disabled'] = $_POST['ivpa_term'][$i]['disabled'];
									$ivpa_attrs['ivpa_custom'][$i]['outofstock'] = $_POST['ivpa_term'][$i]['outofstock'];
									foreach ( $_POST['ivpa_tooltip'][$i] as $k => $v ) {
										$ivpa_attrs['ivpa_tooltip'][$i][$k] = $v;
									}
								break;

								case 'ivpa_color' :
									foreach ( $_POST['ivpa_term'][$i] as $k => $v ) {
										$ivpa_attrs['ivpa_custom'][$i][$k] = $v;
									}
									foreach ( $_POST['ivpa_tooltip'][$i] as $k => $v ) {
										$ivpa_attrs['ivpa_tooltip'][$i][$k] = $v;
									}
								break;

								case 'ivpa_image' :
									foreach ( $_POST['ivpa_term'][$i] as $k => $v ) {
										$ivpa_attrs['ivpa_custom'][$i][$k] = $v;
									}
									foreach ( $_POST['ivpa_tooltip'][$i] as $k => $v ) {
										$ivpa_attrs['ivpa_tooltip'][$i][$k] = $v;
									}
								break;

								case 'ivpa_html' :
									foreach ( $_POST['ivpa_term'][$i] as $k => $v ) {
										$ivpa_attrs['ivpa_custom'][$i][$k] = stripslashes($v);
									}
									foreach ( $_POST['ivpa_tooltip'][$i] as $k => $v ) {
										$ivpa_attrs['ivpa_tooltip'][$i][$k] = $v;
									}
								break;

								default :
								break;

							}
						}
					}

					$curr_customizations = $ivpa_attrs;

				}
				else {
					if ( $curr_language === false ) {
						$curr_customizations = get_option( 'wc_ivpa_attribute_customization', '' );
					}
					else {
						$curr_customizations = get_option( 'wc_ivpa_attribute_customization_' . $curr_language, '' );
					}
				}

				if ( $curr_customizations == '' ) {
					$curr_customizations = array();
				}

				$attributes = self::ivpa_get_attributes();

				$select_attributes = array();

				foreach( $attributes as $attribute ) {
					$select_attributes[$attribute] = wc_attribute_label( $attribute );
				}

				if ( !empty($curr_customizations) ) {

					for ( $i = 0; $i < count( $curr_customizations['ivpa_attr'] ); $i++ ) {

						$type = $curr_customizations['ivpa_attr'][$i] == 'ivpa_custom' ? 'custom_option' : 'attr';

						$ctrl = '';
						$html = '<div class="ivpa_element' . ( $type == 'custom_option' ? ' ivpa_custom' : '' ) . '" data-id="' . $i . '"><div class="ivpa_manipulate"><a href="#" class="ivpa_attribute_title">' . ( $type =='attr' ? wc_attribute_label( $curr_customizations['ivpa_attr'][$i] ) : esc_attr( stripslashes( $curr_customizations['ivpa_title'][$i] ) ) ) . '</a><a href="#" class="ivpa_remove"><i class="ivpa-remove"></i></a><a href="#" class="ivpa_reorder"><i class="ivpa-reorder"></i></a><a href="#" class="ivpa_slidedown"><i class="ivpa-slidedown"></i></a><div class="ivpa_clear"></div></div><div class="ivpa_holder">';

			switch ( $type ) {
				case 'custom_option' :

					$texts = array(
						'ivpa_title' => __( 'Custom Option Name', 'ivpawoo' ),
						'ivpa_desc' => __( 'Custom Option Description' ,'ivpawoo' ),
						'ivpa_style' => __( 'Appearance', 'ivpawoo' )
					);

					$styles = array(
						'' => __( 'None', 'ivpawoo' ),
						'ivpa_text' => __( 'Plain Text', 'ivpawoo' ),
						'ivpa_color' => __( 'Color', 'ivpawoo' ),
						'ivpa_image' => __( 'Thumbnail', 'ivpawoo' ),
						'ivpa_selectbox' => __( 'Select Box', 'ivpawoo' ),
						'ivpa_html' => __( 'HTML', 'ivpawoo' ),
						'ivpac_input' => __( 'Input Field', 'ivpawoo' ),
						'ivpac_checkbox' => __( 'Checkbox', 'ivpawoo' ),
						'ivpac_textarea' => __( 'Textarea', 'ivpawoo' ),
						'ivpac_system' => __( 'System Select', 'ivpawoo' )
					);

					$ctrl .= '<label><span>' . __( 'Add Price', 'ivpawoo' ) . '</span> <input type="text" name="ivpa_addprice[' . $i . ']" value="' . $curr_customizations['ivpa_addprice'][$i] . '" />
					<small>' . __( 'Add-on price if option is used by customer', 'ivpawoo' ) . '</small><br/>
					</label>';

					$ctrl .= '<label><span>' . __( 'Limit to Product Type', 'ivpawoo' ) . '</span> <input type="text" name="ivpa_limit_type[' . $i . ']" value="' . $curr_customizations['ivpa_limit_type'][$i] . '" />
					<small>' . __( 'Enter product types separated by |. Sample:', 'ivpawoo' ) . ' &rarr; <code>simple|variable</code></small><br/>
					</label>';

					$ctrl .= '<label><span>' . __( 'Limit to Product Category', 'ivpawoo' ) . '</span> <input type="text" name="ivpa_limit_category[' . $i . ']" value="' . $curr_customizations['ivpa_limit_category'][$i] . '" />
					<small>' . __( 'Enter product category IDs separated by |. Sample:', 'ivpawoo' ) . ' &rarr; <code>7|55</code></small><br/>
					</label>';

					$ctrl .= '<label><span>' . __( 'Limit to Products', 'ivpawoo' ) . '</span> <input type="text" name="ivpa_limit_product[' . $i . ']" value="' . $curr_customizations['ivpa_limit_product'][$i] . '" />
					<small>' . __( 'Enter product IDs separated by |. Sample:', 'ivpawoo' ) . ' &rarr; <code>155|222|333</code></small><br/>
					</label>';

					$disabled = in_array( $curr_customizations['ivpa_style'][$i], array( 'ivpac_input', 'ivpac_checkbox', 'ivpac_textarea', 'ivpac_system', 'ivpa_selectbox' ) ) ? ' disabled' : '';

					$ctrl .= '<label><input type="checkbox" name="ivpa_multiselect[' . $i . ']" ' . ( isset( $curr_customizations['ivpa_multiselect'][$i] ) && $curr_customizations['ivpa_multiselect'][$i] == 'yes' ? ' checked="checked"' : '' ) . $disabled . ' /> <span class="ivpa_checkbox_desc">' . __( 'Enable Multi Select', 'ivpawoo' ) . '</span></label>';

					$ctrl .= '<input type="hidden" name="ivpa_attr[' . $i . ']" value="ivpa_custom" />';

					$readyAtts = array();
					foreach ( $curr_customizations['ivpa_name'][$i] as $k => $v ) {
						$price = isset( $curr_customizations['ivpa_price'][$i][$k] ) ? array( 'price' => $curr_customizations['ivpa_price'][$i][$k] ) : array();
						$readyAtts[] = array_merge( array(
							'slug' => $k,
							'name' => $v
						), $price );
					}

				break;
				default :

					$texts = array(
						'ivpa_title' => __( 'Override Attribute Name', 'ivpawoo' ),
						'ivpa_desc' => __( 'Add Attribute Description' ,'ivpawoo' ),
						'ivpa_style' => __( 'Select Attribute Style', 'ivpawoo' )
					);

					$styles = array(
						'ivpa_text' => __( 'Plain Text', 'ivpawoo' ),
						'ivpa_color' => __( 'Color', 'ivpawoo' ),
						'ivpa_image' => __( 'Thumbnail', 'ivpawoo' ),
						'ivpa_selectbox' => __( 'Select Box', 'ivpawoo' ),
						'ivpa_html' => __( 'HTML', 'ivpawoo' )
					);

					$html .= '<label><span>' . __( 'Select Attribute', 'ivpawoo' ) . '</span> <select class="ivpa_attr_select ivpa_s_attribute" name="ivpa_attr[' . $i . ']">';

					$html .= '<option value="">' . __('Select Attribute', 'ivpawoo') . '</option>';

					foreach ( $attributes as $k => $v ) {
						if ( wp_count_terms( $v, array( 'hide_empty' => false ) ) < 1 ) {
							continue;
						}

						$curr_label = wc_attribute_label( $v );
						$html .= '<option value="' . $v . '"' . ( $curr_customizations['ivpa_attr'][$i] == $v ? ' selected="selected"' : '' ) . '>' . $curr_label . '</option>';
					}

					$html .= '</select></label>';

					$ctrl .= '<label><input type="checkbox" name="ivpa_svariation[' . $i . ']"' . ( isset( $curr_customizations['ivpa_svariation'][$i] ) && $curr_customizations['ivpa_svariation'][$i] == 'yes' ? ' checked="checked"' : '' ) . ' /> <span class="ivpa_checkbox_desc">' . __( 'Enable attribute selection for all product types (Works with Support Attribute Selection on All Product Types Option)', 'ivpawoo' ) . '</span></label>';

					$curr_tax = $curr_customizations['ivpa_attr'][$i];
					$catalog_attrs = get_terms( $curr_tax, array( 'hide_empty' => false ) );

					$readyAtts = array();
					if ( !is_wp_error( $catalog_attrs ) ) {
						foreach ( $catalog_attrs as $term ) {
							$readyAtts[] = array(
								'slug' => $term->slug,
								'name' => $term->name
							);
						}
					}

				break;

			}

						$html .= '<label><span>' . $texts['ivpa_title'] . '</span> <input type="text" name="ivpa_title[' . $i . ']" value="' . esc_attr( stripslashes( $curr_customizations['ivpa_title'][$i] ) ) . '" /></label>';

						$html .= '<label><span>' . $texts['ivpa_desc'] . '</span> <textarea name="ivpa_desc[' . $i . ']">' . stripslashes( $curr_customizations['ivpa_desc'][$i] ) . '</textarea></label>';

						$html .= $ctrl;

						$html .= '<label><input type="checkbox" name="ivpa_archive_include[' . $i . ']"' . ( isset( $curr_customizations['ivpa_archive_include'][$i] ) && $curr_customizations['ivpa_archive_include'][$i] == 'yes' ? ' checked="checked"' : '' ) . ' /> <span class="ivpa_checkbox_desc">' . __( 'Show on Shop/Archives (This only works if the Shop/Archive mode is set to Show Only)', 'ivpawoo' ) . '</span></label>';

						$html .= '<label><span>' . $texts['ivpa_style'] . '</span> <select class="ivpa_attr_select ivpa_s_style" name="ivpa_style[' . $i . ']">';

						foreach ( $styles as $k => $v ) {
							$html .= '<option value="' . $k . '"' . ( $curr_customizations['ivpa_style'][$i] == $k ? ' selected="selected"' : '' ) . '>' . $v . '</option>';
						}

						$html .= '</select></label>';

						$html .= '<div class="ivpa_terms">';

						$curr_style = $curr_customizations['ivpa_style'][$i];
						if ( $type == 'custom_option' ) {
							$html .= '<span class="ivpa-terms-ui-wrap"><span class="button ivpa-add-custom-term">' . __( 'Add New Term', 'ivpawoo' ) . '</span></span>';
						}

						if ( !empty( $readyAtts ) ){

							ob_start();

							switch ( $curr_style ) {

								case 'ivpa_text' :

									?>
										<div class="ivpa_term_style">
											<span class="ivpa_option">
												<?php _e('CSS', 'ivpawoo'); ?>
												<select name="ivpa_term[<?php echo $i; ?>][style]">
											<?php
												$styles = array(
													'ivpa_border' => __( 'Border', 'ivpawoo' ),
													'ivpa_background' => __( 'Background', 'ivpawoo' ),
													'ivpa_round' => __( 'Round', 'ivpawoo' )
												);

												foreach ( $styles as $k => $v ) {
											?>
													<option value="<?php echo $k; ?>"<?php echo ( $curr_customizations['ivpa_custom'][$i]['style'] == $k ? ' selected="selected"' : '' ); ?>><?php echo $v; ?></option>
											<?php
												}
											?>
												</select>
											</span>
											<span class="ivpa_option">
												<?php _e('Normal', 'ivpawoo'); ?> <input class="ivpa_color" type="text" name="ivpa_term[<?php echo $i; ?>][normal]" value="<?php echo $curr_customizations['ivpa_custom'][$i]['normal']; ?>"/>
											</span>
											<span class="ivpa_option">
												<?php _e('Active', 'ivpawoo'); ?> <input class="ivpa_color" type="text" name="ivpa_term[<?php echo $i; ?>][active]" value="<?php echo $curr_customizations['ivpa_custom'][$i]['active']; ?>"/>
											</span>
											<span class="ivpa_option">
												<?php _e('Disabled', 'ivpawoo'); ?> <input class="ivpa_color" type="text" name="ivpa_term[<?php echo $i; ?>][disabled]" value="<?php echo $curr_customizations['ivpa_custom'][$i]['disabled']; ?>"/>
											</span>
											<span class="ivpa_option">
												<?php _e('Out of stock', 'ivpawoo'); ?> <input class="ivpa_color" type="text" name="ivpa_term[<?php echo $i; ?>][outofstock]" value="<?php echo $curr_customizations['ivpa_custom'][$i]['outofstock']; ?>"/>
											</span>

										</div>
									<?php

									foreach ( $readyAtts as $term ) {

									?>
										<div class="ivpa_term" data-term="<?php echo $term['slug']; ?>">
										<?php
											if ( $type == 'custom_option' ) {
												self::get_custom_option_inputs( $i, $term );
											}
										?>
											<span class="ivpa_option ivpa_option_plaintext">
												<em><?php echo $term['name'] . ' ' . __('Tooltip', 'ivpawoo'); ?></em> <textarea name="ivpa_tooltip[<?php echo $i; ?>][<?php echo $term['slug']; ?>]"><?php echo ( isset( $curr_customizations['ivpa_tooltip'][$i][$term['slug']] ) ? stripslashes( $curr_customizations['ivpa_tooltip'][$i][$term['slug']] ) : '' ); ?></textarea>
											</span>
										</div>
									<?php
									}

								break;

								case 'ivpa_color' :
								?>
									<div class="ivpa_term_style">
										<span class="ivpa_option">
											<?php _e('Size', 'ivpawoo'); ?> <input class="ivpa_size" type="number" name="ivpa_size[<?php echo $i; ?>]" value="<?php echo isset( $curr_customizations['ivpa_size'][$i] ) ? intval( $curr_customizations['ivpa_size'][$i] ) : 36 ; ?>" min="10" max="120" />
										</span>
									</div>
								<?php

									foreach ( $readyAtts as $term ) {

									?>
										<div class="ivpa_term" data-term="<?php echo $term['slug']; ?>">
										<?php
											if ( $type == 'custom_option' ) {
												self::get_custom_option_inputs( $i, $term );
											}
										?>
											<span class="ivpa_option ivpa_option_color">
												<em><?php echo $term['name'] . ' ' . __('Color', 'ivpawoo'); ?></em> <input class="ivpa_color" type="text" name="ivpa_term[<?php echo $i; ?>][<?php echo $term['slug']; ?>]" value="<?php echo $curr_customizations['ivpa_custom'][$i][$term['slug']]; ?>" />
											</span>
											<span class="ivpa_option">
												<em><?php echo $term['name'] . ' ' . __('Tooltip', 'ivpawoo'); ?></em> <textarea name="ivpa_tooltip[<?php echo $i; ?>][<?php echo $term['slug']; ?>]"><?php echo ( isset( $curr_customizations['ivpa_tooltip'][$i][$term['slug']] ) ? stripslashes( $curr_customizations['ivpa_tooltip'][$i][$term['slug']] ) : '' ); ?></textarea>
											</span>
										</div>
									<?php
									}

								break;

								case 'ivpa_image' :
								?>
									<div class="ivpa_term_style">
										<span class="ivpa_option">
											<?php _e('Size', 'ivpawoo'); ?> <input class="ivpa_size" type="number" name="ivpa_size[<?php echo $i; ?>]" value="<?php echo isset( $curr_customizations['ivpa_size'][$i] ) ? intval( $curr_customizations['ivpa_size'][$i] ) : 36 ; ?>" min="10" max="120" />
										</span>
									</div>
								<?php

									foreach ( $readyAtts as $term ) {

									?>
										<div class="ivpa_term" data-term="<?php echo $term['slug']; ?>">
										<?php
											if ( $type == 'custom_option' ) {
												self::get_custom_option_inputs( $i, $term );
											}
										?>
											<span class="ivpa_option">
												<em><?php echo $term['name'] . ' ' . __('Image URL', 'ivpawoo'); ?></em> <input type="text" name="ivpa_term[<?php echo $i; ?>][<?php echo $term['slug']; ?>]" value="<?php echo $curr_customizations['ivpa_custom'][$i][$term['slug']]; ?>"/>
											</span>
											<span class="ivpa_option ivpa_option_button">
												<em><?php _e( 'Add/Upload image', 'ivpawoo' ); ?></em> <a href="#" class="ivpa_upload_media button"><?php _e('Image Gallery', 'ivpawoo'); ?></a>
											</span>
											<span class="ivpa_option">
												<em><?php echo $term['name'] . ' ' . __('Tooltip', 'ivpawoo'); ?></em> <textarea name="ivpa_tooltip[<?php echo $i; ?>][<?php echo $term['slug']; ?>]"><?php echo ( isset( $curr_customizations['ivpa_tooltip'][$i][$term['slug']] ) ? stripslashes( $curr_customizations['ivpa_tooltip'][$i][$term['slug']] ) : '' ); ?></textarea>
											</span>
										</div>
									<?php
									}

								break;

								case 'ivpa_html' :

									foreach ( $readyAtts as $term ) {

									?>
										<div class="ivpa_term" data-term="<?php echo $term['slug']; ?>">
										<?php
											if ( $type == 'custom_option' ) {
												self::get_custom_option_inputs( $i, $term );
											}
										?>
											<span class="ivpa_option ivpa_option_text">
												<em><?php echo $term['name'] . ' ' . __('HTML', 'ivpawoo'); ?></em> <textarea name="ivpa_term[<?php echo $i; ?>][<?php echo $term['slug']; ?>]"><?php echo stripslashes( $curr_customizations['ivpa_custom'][$i][$term['slug']] ); ?></textarea>
											</span>
											<span class="ivpa_option">
												<em><?php echo $term['name'] . ' ' . __('Tooltip', 'ivpawoo'); ?></em> <textarea name="ivpa_tooltip[<?php echo $i; ?>][<?php echo $term['slug']; ?>]"><?php echo ( isset( $curr_customizations['ivpa_tooltip'][$i][$term['slug']] ) ? stripslashes( $curr_customizations['ivpa_tooltip'][$i][$term['slug']] ) : '' ); ?></textarea>
											</span>
										</div>
									<?php
									}

								break;

								case 'ivpa_selectbox' :
									if ( $type !== 'custom_option' ) {
									?>
										<div class="ivpa_selectbox"><i class="ivpa-warning"></i> <span><?php _e( 'This style has no extra settings!', 'ivpawoo' ); ?></span></div>
									<?php
									}
									else {
										foreach ( $readyAtts as $term ) {
										?>
											<div class="ivpa_term" data-term="<?php echo $term['slug']; ?>">
											<?php
												if ( $type == 'custom_option' ) {
													self::get_custom_option_inputs( $i, $term );
												}
											?>
											</div>
										<?php
										}
									}
								break;

								case 'ivpac_input' :
								case 'ivpac_checkbox' :
								case 'ivpac_textarea' :
								case 'ivpac_system' :

									foreach ( $readyAtts as $term ) {

									?>
										<div class="ivpa_term" data-term="<?php echo $term['slug']; ?>">
										<?php
											if ( $type == 'custom_option' ) {
												self::get_custom_option_inputs( $i, $term );
											}
											/*if ( $curr_style !== 'ivpac_system' ) {
											?>
												<span class="ivpa_option ivpa_option_plaintext">
													<em><?php echo $term['name'] . ' ' . __('Tooltip', 'ivpawoo'); ?></em> <textarea name="ivpa_tooltip[<?php $i; ?>][<?php echo $term['slug']; ?>]"></textarea>
												</span>
											<?php
											}*/
										?>
										</div>
									<?php

									}

								break;

								default :
								break;

							}

							$html .= ob_get_clean();

						}

						$html .= '</div>';

						$html .= '</div></div>';

						echo $html;

					}

				}
			?>
				</div>
			</div>
		<?php
			}

/*			$choices_image_size = array(
				'full' => 'full'
			);

			$image_sizes = get_intermediate_image_sizes();

			foreach ( $image_sizes as $image_size ) {
				$choices_image_size[$image_size] = $image_size;
			}*/

			$settings = array(
				'section_single_title' => array(
					'name' => __( 'Single Product Page Settings', 'ivpawoo' ),
					'type' => 'title',
					'desc' => __( 'General plugin settings when used in Single Product pages.', 'ivpawoo' )
				),
				'ivpa_single_enable' => array(
					'name' => __( 'Enable/Disable Attributes In Single Product Pages', 'ivpawoo' ),
					'type' => 'checkbox',
					'desc' => __( 'Check this option to enable attribute selection in single product pages.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_single_enable',
					'default' => 'yes'
				),
				'ivpa_single_selectbox' => array(
					'name' => __( 'Hide Default Select Boxes', 'ivpawoo' ),
					'type' => 'checkbox',
					'desc' => __( 'Check this option to hide default select boxes in single product pages.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_single_selectbox',
					'default' => 'yes'
				),
				'ivpa_single_addtocart' => array(
					'name' => __( 'Hide Add to Cart Before Selection', 'ivpawoo' ),
					'type' => 'checkbox',
					'desc' => __( 'Check this option to hide the Add to cart button in single product pages before the selection is made.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_single_addtocart',
					'default' => 'yes'
				),
				'ivpa_single_desc' => array(
					'name' => __( 'Select Descriptions Position', 'ivpawoo' ),
					'type' => 'select',
					'desc' => __( 'Select where to show descriptions.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_single_desc',
					'options' => array(
						'ivpa_aftertitle' => __( 'After Title', 'ivpawoo' ),
						'ivpa_afterattribute' => __( 'After Attributes', 'ivpawoo' )
					),
					'default' => 'ivpa_afterattribute',
					'css' => 'width:300px;margin-right:12px;'
				),
				'ivpa_single_image' => array(
					'name' => __( 'Use IVPA Image Switcher', 'ivpawoo' ),
					'type' => 'checkbox',
					'desc' => __( 'Check this option to enable IVPA image switcher in single product pages.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_single_image',
					'default' => 'yes'
				),
				'ivpa_single_ajax' => array(
					'name' => __( 'Enable/Disable AJAX Add To Cart', 'ivpawoo' ),
					'type' => 'checkbox',
					'desc' => __( 'Check this option to enable AJAX add to cart in single product pages.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_single_ajax',
					'default' => 'no'
				),
/*				'ivpa_single_image_size' => array(
					'name' => __( 'Select Single Image Size', 'ivpawoo' ),
					'type' => 'select',
					'desc' => __( 'If the default setting in single products returns a false image upon selecting or deselecting please use this setting to override image size. Default: full (works almost anywhere), but usually: shop_single or set your own image size.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_single_image_size',
					'options' =>$choices_image_size,
					'default' => 'full',
					'css' => 'width:300px;margin-right:12px;'
				),*/
				'ivpa_single_action' => array(
					'name' => __( 'Override Default Single Product Action', 'ivpawoo' ),
					'type' => 'text',
					'desc' => __( 'Change default init action in single product pages. Use actions initiated in your content-single-product.php file. Please enter action in the following format action_name:priority.', 'ivpawoo' ) . ' ( default: woocommerce_before_add_to_cart_button )',
					'id'   => 'wc_settings_ivpa_single_action',
					'default' => '',
					'css' => 'width:300px;margin-right:12px;'
				),
				'section_single_end' => array(
					'type' => 'sectionend'
				),
				'section_archive_title' => array(
					'name' => __( 'Shop/Product Archive Settings', 'ivpawoo' ),
					'type' => 'title',
					'desc' => __( 'General plugin settings when used in Shop and Product Archive pages.', 'ivpawoo' )
				),
				'ivpa_archive_enable' => array(
					'name' => __( 'Enable/Disable Attributes In Archives', 'ivpawoo' ),
					'type' => 'checkbox',
					'desc' => __( 'Check this option to enable attribute selection in shop and product archive pages.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_archive_enable',
					'default' => 'no'
				),
				'ivpa_archive_quantity' => array(
					'name' => __( 'Show Quantities In Archives', 'ivpawoo' ),
					'type' => 'checkbox',
					'desc' => __( 'Check this option to enable product quantity in shop and product archive pages.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_archive_quantity',
					'default' => 'no'
				),
				'ivpa_archive_mode' => array(
					'name' => __( 'Select Archive Display Mode', 'ivpawoo' ),
					'type' => 'select',
					'desc' => __( 'Select style to use with the attributes in shop and product archive pages.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_archive_mode',
					'options' => array(
						'ivpa_showonly' => __( 'Show Only', 'ivpawoo' ),
						'ivpa_selection' => __( 'Enable Selection and Add to Cart', 'ivpawoo' )
					),
					'default' => 'ivpa_selection',
					'css' => 'width:300px;margin-right:12px;'
				),
				'ivpa_archive_align' => array(
					'name' => __( 'Attribute Alignment in Archives', 'ivpawoo' ),
					'type' => 'select',
					'desc' => __( 'Select attribute selectors alignment in shop and product archive pages.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_archive_align',
					'options' => array(
						'ivpa_align_left' => __( 'Left', 'ivpawoo' ),
						'ivpa_align_right' => __( 'Right', 'ivpawoo' ),
						'ivpa_align_center' => __( 'Center', 'ivpawoo' )
					),
					'default' => 'ivpa_align_left',
					'css' => 'width:300px;margin-right:12px;'
				),
/*				'ivpa_archive_image_size' => array(
					'name' => __( 'Select Archive Image Size', 'ivpawoo' ),
					'type' => 'select',
					'desc' => __( 'If the default setting in archives returns a false image upon selecting or deselecting please use this setting to override image size. Default: full (works almost anywhere), but usually: shop_catalog, or set your own image size.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_archive_image_size',
					'options' =>$choices_image_size,
					'default' => 'full',
					'css' => 'width:300px;margin-right:12px;'
				),*/
				'ivpa_archive_action' => array(
					'name' => __( 'Override Default Product Archive Action', 'ivpawoo' ),
					'type' => 'text',
					'desc' => __( 'Change default init action in product archives. Use actions initiated in your content-product.php file. Please enter action in the following format action_name:priority.', 'ivpawoo' ) . ' ( default: woocommerce_after_shop_loop_item:999 )',
					'id'   => 'wc_settings_ivpa_archive_action',
					'default' => '',
					'css' => 'width:300px;margin-right:12px;'
				),
				'section_archive_end' => array(
					'type' => 'sectionend'
				),
				'section_selectors_title' => array(
					'name' => __( 'jQuery Selector Settings', 'ivpawoo' ),
					'type' => 'title',
					'desc' => __( 'Sometimes your theme will not have the default classes for these elements. If this is the case use these options to override default jQuery selectors.', 'ivpawoo' )
				),
				'ivpa_single_selector' => array(
					'name' => __( 'Single Product Image Selector', 'ivpawoo' ),
					'type' => 'text',
					'desc' => __( 'Change default image wrapper selector in single product pages.', 'ivpawoo' ) . ' (default: .type-product .images )',
					'id'   => 'wc_settings_ivpa_single_selector',
					'default' => '',
					'css' => 'width:300px;margin-right:12px;'
				),
				'ivpa_archive_selector' => array(
					'name' => __( 'Shop/Archive Product Selector', 'ivpawoo' ),
					'type' => 'text',
					'desc' => __( 'Change default product selector in shop and product archives. Use the product class from your product archive pages.', 'ivpawoo' ) . ' (default: .type-product )',
					'id'   => 'wc_settings_ivpa_archive_selector',
					'default' => '',
					'css' => 'width:300px;margin-right:12px;'
				),
				'ivpa_addcart_selector' => array(
					'name' => __( 'Shop/Archive Add To Cart Selector', 'ivpawoo' ),
					'type' => 'text',
					'desc' => __( 'Change default add to cart selector in shop and product archives. Use the product class from your product archive pages.', 'ivpawoo' ) . ' (default: .add_to_cart_button )',
					'id'   => 'wc_settings_ivpa_addcart_selector',
					'default' => '',
					'css' => 'width:300px;margin-right:12px;'
				),
				'ivpa_price_selector' => array(
					'name' => __( 'Shop/Archive Price Selector', 'ivpawoo' ),
					'type' => 'text',
					'desc' => __( 'Change default price selector in shop and product archives. Use the price class from your product archive pages.', 'ivpawoo' ) . ' (default: .price )',
					'id'   => 'wc_settings_ivpa_price_selector',
					'default' => '',
					'css' => 'width:300px;margin-right:12px;'
				),
				'section_selectors_end' => array(
					'type' => 'sectionend'
				),
				'section_outofstock_title' => array(
					'name' => __( 'Out Of Stock Display Settings', 'ivpawoo' ),
					'type' => 'title',
					'desc' => __( 'Setup your out of stock selectors appearance.', 'ivpawoo' )
				),
				'ivpa_outofstock_mode' => array(
					'name' => __( 'Select Out Of Stock Mode', 'ivpawoo' ),
					'type' => 'select',
					'desc' => __( 'Select how the Out of Stock selectors will appear.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_outofstock_mode',
					'options' => array(
						'default' => __( 'Shown but not clickable', 'ivpawoo' ),
						'clickable' => __( 'Shown and clickable', 'ivpawoo' ),
						'hidden' => __( 'Hidden from pages', 'ivpawoo' )
					),
					'default' => 'default',
					'css' => 'width:300px;margin-right:12px;'
				),
				'section_outofstock_end' => array(
					'type' => 'sectionend'
				),
				'section_advanced_title' => array(
					'name' => __( 'Advanced Settings', 'ivpawoo' ),
					'type' => 'title',
					'desc' => __( 'Miscellaneous advanced settings.', 'ivpawoo' )
				),
				'ivpa_image_attributes' => array(
					'name' => __( 'Image Changing Attributes', 'ivpawoo' ),
					'type' => 'multiselect',
					'desc' => __( 'Select attributes that will change the product image.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_image_attributes',
					'options' => ( isset( $select_attributes ) ? $select_attributes : array() ),
					'default' => '',
					'css' => 'width:300px;margin-right:12px;'
				),
				'ivpa_simple_support' => array(
					'name' => __( 'Support Attribute Selection on All Product Types', 'ivpawoo' ),
					'type' => 'select',
					'desc' => __( 'Set this option to enable selection of attributes for products that are not variable.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_simple_support',
					'options' => array(
						'none' => __( 'No Support', 'ivpawoo' ),
						'registered' => __( 'Registered Attributes', 'ivpawoo' ),
						'full' => __( 'Registered Attributes and Custom Attributes', 'ivpawoo' )
					),
					'default' => 'none',
					'css' => 'width:300px;margin-right:12px;'
				),
				'ivpa_step_selection' => array(
					'name' => __( 'Step Attribute Selection', 'ivpawoo' ),
					'type' => 'checkbox',
					'desc' => __( 'Check this option to enable stepped attribute selection.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_step_selection',
					'default' => 'no'
				),
				'ivpa_disable_unclick' => array(
					'name' => __( 'Disable Attribute Deselection', 'ivpawoo' ),
					'type' => 'checkbox',
					'desc' => __( 'Check this option to disable attribute deselection in IVPA selectors.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_disable_unclick',
					'default' => 'no'
				),
				'ivpa_backorder_support' => array(
					'name' => __( 'Backorder Notification Support', 'ivpawoo' ),
					'type' => 'checkbox',
					'desc' => __( 'Check this option to enable IVPA backorder notification support.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_backorder_support',
					'default' => 'no'
				),
				'ivpa_force_scripts' => array(
					'name' => __( 'Plugin Scripts', 'ivpawoo' ),
					'type' => 'checkbox',
					'desc' => __( 'Check this option to enable plugin scripts in all pages. This option fixes issues in Quick Views.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_force_scripts',
					'default' => 'no'
				),
				'ivpa_use_caching' => array(
					'name' => __( 'Use Caching', 'ivpawoo' ),
					'type' => 'checkbox',
					'desc' => __( 'Check this option to use IVPA product cache for better performance.', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_use_caching',
					'default' => 'no'
				),
				'section_advanced_end' => array(
					'type' => 'sectionend'
				),
				'section_register_title' => array(
					'name' => __( 'Register and Automatic Updates', 'ivpawoo' ),
					'type' => 'title',
					'desc' => __( 'Register your plugin with the purchase code you have got from Codecanyon.net! Get automatic updates!', 'ivpawoo' )
				),
				'ivpa_purchase_code' => array(
					'name' => __( 'Register Improved Variable Product Attributes', 'ivpawoo' ),
					'type' => 'text',
					'desc' => __( 'Enter your purchase code to get instant updated even before the codecanyon.net releases!', 'ivpawoo' ),
					'id'   => 'wc_settings_ivpa_purchase_code',
					'default' => '',
					'css' => 'width:300px;margin-right:12px;'
				),
				'section_register_end' => array(
					'type' => 'sectionend'
				),
			);

			if ( $action == 'update' && get_option( 'wc_settings_ivpa_use_caching', 'no' ) == 'yes' ) {
				global $wpdb;
				$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta WHERE meta.meta_key LIKE '_ivpa_cached_%';" );
			}

			return apply_filters( 'wc_ivpa_settings', $settings );

		}

		public static function ivpa_get_fields() {
		
			if ( ( isset( $_POST['type'] ) && in_array( $_POST['type'], array( 'default', 'custom_option' ) ) ) === false ) {
				die(0);
				exit;
			}

			$type = $_POST['type'];

			$html = '';
			$ctrl = '';

			switch ( $type ) {
				case 'custom_option' :

					$texts = array(
						'ivpa_title' => __( 'Custom Option Name', 'ivpawoo' ),
						'ivpa_desc' => __( 'Custom Option Description' ,'ivpawoo' ),
						'ivpa_style' => __( 'Appearance', 'ivpawoo' )
					);

					$styles = array(
						'' => __( 'None', 'ivpawoo' ),
						'ivpa_text' => __( 'Plain Text', 'ivpawoo' ),
						'ivpa_color' => __( 'Color', 'ivpawoo' ),
						'ivpa_image' => __( 'Thumbnail', 'ivpawoo' ),
						'ivpa_selectbox' => __( 'Select Box', 'ivpawoo' ),
						'ivpa_html' => __( 'HTML', 'ivpawoo' ),
						'ivpac_input' => __( 'Input Field', 'ivpawoo' ),
						'ivpac_checkbox' => __( 'Checkbox', 'ivpawoo' ),
						'ivpac_textarea' => __( 'Textarea', 'ivpawoo' ),
						'ivpac_system' => __( 'System Select', 'ivpawoo' )
					);

					$ctrl .= '<label><span>' . __( 'Add Price', 'ivpawoo' ) . '</span> <input type="text" name="ivpa_addprice[%%]" />
					<small>' . __( 'Add-on price if option is used by customer', 'ivpawoo' ) . '</small><br/>
					</label>';

					$ctrl .= '<label><span>' . __( 'Limit to Product Type', 'ivpawoo' ) . '</span> <input type="text" name="ivpa_limit_type[%%]" />
					<small>' . __( 'Enter product types separated by |. Sample:', 'ivpawoo' ) . ' &rarr; <code>simple|variable</code></small><br/>
					</label>';

					$ctrl .= '<label><span>' . __( 'Limit to Product Category', 'ivpawoo' ) . '</span> <input type="text" name="ivpa_limit_category[%%]" />
					<small>' . __( 'Enter product category IDs separated by |. Sample:', 'ivpawoo' ) . ' &rarr; <code>7|55</code></small><br/>
					</label>';

					$ctrl .= '<label><span>' . __( 'Limit to Products', 'ivpawoo' ) . '</span> <input type="text" name="ivpa_limit_product[%%]" />
					<small>' . __( 'Enter product IDs separated by |. Sample:', 'ivpawoo' ) . ' &rarr; <code>155|222|333</code></small><br/>
					</label>';

					$ctrl .= '<label><input type="checkbox" name="ivpa_multiselect[%%]" checked="checked" /> <span class="ivpa_checkbox_desc">' . __( 'Enable Multi Select', 'ivpawoo' ) . '</span></label>';

					$ctrl .= '<input type="hidden" name="ivpa_attr[%%]" value="ivpa_custom" />';

				break;
				default :

					$texts = array(
						'ivpa_title' => __( 'Override Attribute Name', 'ivpawoo' ),
						'ivpa_desc' => __( 'Add Attribute Description' ,'ivpawoo' ),
						'ivpa_style' => __( 'Select Attribute Style', 'ivpawoo' )
					);

					$styles = array(
						'ivpa_text' => __( 'Plain Text', 'ivpawoo' ),
						'ivpa_color' => __( 'Color', 'ivpawoo' ),
						'ivpa_image' => __( 'Thumbnail', 'ivpawoo' ),
						'ivpa_selectbox' => __( 'Select Box', 'ivpawoo' ),
						'ivpa_html' => __( 'HTML', 'ivpawoo' )
					);

					$attributes = self::ivpa_get_attributes();

					$html .= '<label><span>' . __( 'Select Attribute', 'ivpawoo' ) . '</span> <select class="ivpa_attr_select ivpa_s_attribute" name="ivpa_attr[%%]">';

					$html .= '<option value="">' . __('Select Attribute', 'ivpawoo') . '</option>';

					foreach ( $attributes as $k => $v ) {
						if ( wp_count_terms( $v, array( 'hide_empty' => false ) ) < 1 ) {
							continue;
						}

						$curr_label = wc_attribute_label( $v );
						$html .= '<option value="' . $v . '">' . $curr_label . '</option>';
					}

					$html .= '</select></label>';

					$ctrl .= '<label><input type="checkbox" name="ivpa_svariation[%%]" /> <span class="ivpa_checkbox_desc">' . __( 'Enable attribute selection for all product types (Works with Support Attribute Selection on All Product Types Option)', 'ivpawoo' ) . '</span></label>';

				break;
			}

			$html .= '<label><span>' . $texts['ivpa_title'] . '</span> <input type="text" name="ivpa_title[%%]" /></label>';

			$html .= '<label><span>' . $texts['ivpa_desc'] . '</span> <textarea name="ivpa_desc[%%]"></textarea></label>';
			
			$html .= $ctrl;

			$html .= '<label><input type="checkbox" name="ivpa_archive_include[%%]" checked="checked" /> <span class="ivpa_checkbox_desc">' . __( 'Show on Shop/Archives (This only works if the Shop/Archive mode is set to Show Only)', 'ivpawoo' ) . '</span></label>';

			$html .= '<label><span>' . $texts['ivpa_style'] . '</span> <select class="' . ( $type !== 'custom_option' ? 'ivpa_attr_select' : 'ivpa_custom_select' ) . ' ivpa_s_style" name="ivpa_style[%%]">';

			$c=0;
			foreach ( $styles as $k => $v ) {
				$html .= '<option value="' . $k . '" ' . ($c==0?' selected="selected"':'') . '>' . $v . '</option>';
				$c++;
			}

			$html .= '</select></label>';

			switch ( $type ) {
				case 'custom_option' :

					$html .= '<div class="ivpa_terms">';

					$html .= '</div>';

				break;
				default :

					$html .= '<div class="ivpa_terms">';

					$html .= '</div>';

				break;
			}

			die($html);
			exit;

		}

		public static function get_custom_option_inputs( $n, $term ) {
		?>
			<span class="ivpa-term-ctrl"><?php echo $term['name'];?> <a class="ivpa-term-remove"><i class="ivpa-remove"></i></a><a class="ivpa-term-move"><i class="ivpa-reorder"></i></a></span>
			<span class="ivpa_option ivpa_option_plaintext">
				<em><?php echo $term['name'] . ' ' . __( 'Name', 'ivpawoo'); ?></em> <input type="text" name="ivpa_name[<?php echo $n; ?>][<?php echo $term['slug']; ?>]"<?php echo ( !empty( $term['name'] ) ? ' value="' . $term['name'] . '"' : '' ); ?> /> <input type="hidden" disabled />
			</span>
			<span class="ivpa_option ivpa_option_plaintext">
				<em><?php echo $term['name'] . ' ' . __( 'Add Price', 'ivpawoo'); ?></em> <input type="text" name="ivpa_price[<?php echo $n; ?>][<?php echo $term['slug']; ?>]"<?php echo ( !empty( $term['price'] ) ? ' value="' . $term['price'] . '"' : '' ); ?> />
			</span>
		<?php
		}

		public static function ivpa_get_terms() {

			if ( ( isset( $_POST['type'] ) && in_array( $_POST['type'], array( 'get_terms', 'get_custom_option' ) ) ) === false ) {
				die(0);
				exit;
			}

			$html = '';
			$type = $_POST['type'];
			$curr_style = ( isset( $_POST['style'] ) ? $_POST['style'] : '' );

			switch ( $type ) {
				case 'get_custom_option' :

					$html .= '<span class="ivpa-terms-ui-wrap"><span class="button ivpa-add-custom-term">' . __( 'Add New Term', 'ivpawoo' ) . '</span></span>';

					$readyAtts = array( array(
						'slug' => '',
						'name' => ''
					) );

				break;
				default :

					$curr_tax = ( isset($_POST['taxonomy']) ? $_POST['taxonomy'] : '' );

					if ( $curr_tax == '') {
						die(0);
						exit;
					}

					$catalog_attrs = get_terms( $curr_tax, array( 'hide_empty' => false ) );

					if ( is_wp_error( $catalog_attrs ) ) {
						die(0);
						exit;
					}

					$readyAtts = array();
					foreach ( $catalog_attrs as $term ) {
						$readyAtts[] = array(
							'slug' => $term->slug,
							'name' => $term->name
						);
					}

				break;
			}

			if ( $curr_style == '') {
				die(0);
				exit;
			}

			if ( !empty( $readyAtts ) ) {

				ob_start();

				switch ( $curr_style ) {

					case 'ivpa_text' :

						?>
							<div class="ivpa_term_style">
								<span class="ivpa_option">
									<?php _e('CSS', 'ivpawoo'); ?>
									<select name="ivpa_term[%%][style]">
								<?php
									$styles = array(
										'ivpa_border' => __( 'Border', 'ivpawoo' ),
										'ivpa_background' => __( 'Background', 'ivpawoo' ),
										'ivpa_round' => __( 'Round', 'ivpawoo' )
									);

									$c=0;
									foreach ( $styles as $k => $v ) {
								?>
										<option value="<?php echo $k; ?>"<?php echo ($c==0?' selected="selected"':''); ?>><?php echo $v; ?></option>
								<?php
										$c++;
									}
								?>
									</select>
								</span>
								<span class="ivpa_option">
									<?php _e('Normal', 'ivpawoo'); ?> <input class="ivpa_color" type="text" name="ivpa_term[%%][normal]" value="#bbbbbb"/>
								</span>
								<span class="ivpa_option">
									<?php _e('Active', 'ivpawoo'); ?> <input class="ivpa_color" type="text" name="ivpa_term[%%][active]" value="#333333"/>
								</span>
								<span class="ivpa_option">
									<?php _e('Disabled', 'ivpawoo'); ?> <input class="ivpa_color" type="text" name="ivpa_term[%%][disabled]" value="#e45050"/>
								</span>
								<span class="ivpa_option">
									<?php _e('Out of stock', 'ivpawoo'); ?> <input class="ivpa_color" type="text" name="ivpa_term[%%][outofstock]" value="#e45050"/>
								</span>

							</div>
						<?php

							foreach ( $readyAtts as $term ) {

							?>
								<div class="ivpa_term" data-term="<?php echo $term['slug']; ?>">
								<?php
									if ( $type == 'get_custom_option' ) {
										self::get_custom_option_inputs( '%%', $term );
									}
								?>
									<span class="ivpa_option ivpa_option_plaintext">
										<em><?php echo $term['name'] . ' ' . __('Tooltip', 'ivpawoo'); ?></em> <textarea name="ivpa_tooltip[%%][<?php echo $term['slug']; ?>]"></textarea>
									</span>
								</div>
							<?php
							}

					break;


					case 'ivpa_color' :
					?>
						<div class="ivpa_term_style">
							<span class="ivpa_option">
								<?php _e('Size', 'ivpawoo'); ?> <input class="ivpa_size" type="number" name="ivpa_size[%%]" value="36" min="10" max="120" />
							</span>
						</div>
					<?php

						foreach ( $readyAtts as $term ) {

						?>
							<div class="ivpa_term" data-term="<?php echo $term['slug']; ?>">
							<?php
								if ( $type == 'get_custom_option' ) {
									self::get_custom_option_inputs( '%%', $term );
								}
							?>
								<span class="ivpa_option ivpa_option_color">
									<em><?php echo $term['name'] . ' ' . __('Color', 'ivpawoo'); ?></em> <input class="ivpa_color" type="text" name="ivpa_term[%%][<?php echo $term['slug']; ?>]" value="#cccccc" />
								</span>
								<span class="ivpa_option">
									<em><?php echo $term['name'] . ' ' . __('Tooltip', 'ivpawoo'); ?></em> <textarea name="ivpa_tooltip[%%][<?php echo $term['slug']; ?>]"></textarea>
								</span>
							</div>
						<?php
						}

					break;


					case 'ivpa_image' :
					?>
						<div class="ivpa_term_style">
							<span class="ivpa_option">
								<?php _e('Size', 'ivpawoo'); ?> <input class="ivpa_size" type="number" name="ivpa_size[%%]" value="36" min="10" max="120" />
							</span>
						</div>
					<?php

						foreach ( $readyAtts as $term ) {

						?>
							<div class="ivpa_term" data-term="<?php echo $term['slug']; ?>">
							<?php
								if ( $type == 'get_custom_option' ) {
									self::get_custom_option_inputs( '%%', $term );
								}
							?>
								<span class="ivpa_option">
									<em><?php echo $term['name'] . ' ' . __('Image URL', 'ivpawoo'); ?></em> <input type="text" name="ivpa_term[%%][<?php echo $term['slug']; ?>]" />
								</span>
								<span class="ivpa_option ivpa_option_button">
									<em><?php _e( 'Add/Upload image', 'ivpawoo' ); ?></em> <a href="#" class="ivpa_upload_media button"><?php _e('Image Gallery', 'ivpawoo'); ?></a>
								</span>
								<span class="ivpa_option">
									<em><?php echo $term['name'] . ' ' . __('Tooltip', 'ivpawoo'); ?></em> <textarea name="ivpa_tooltip[%%][<?php echo $term['slug']; ?>]"></textarea>
								</span>
							</div>
						<?php
						}

					break;


					case 'ivpa_html' :

						foreach ( $readyAtts as $term ) {

						?>
							<div class="ivpa_term" data-term="<?php echo $term['slug']; ?>">
							<?php
								if ( $type == 'get_custom_option' ) {
									self::get_custom_option_inputs( '%%', $term );
								}
							?>
								<span class="ivpa_option ivpa_option_text">
									<em><?php echo $term['name'] . ' ' . __('HTML', 'ivpawoo'); ?></em> <textarea name="ivpa_term[%%][<?php echo $term['slug']; ?>]"></textarea>
								</span>
								<span class="ivpa_option">
									<em><?php echo $term['name'] . ' ' . __('Tooltip', 'ivpawoo'); ?></em> <textarea name="ivpa_tooltip[%%][<?php echo $term['slug']; ?>]"></textarea>
								</span>
							</div>
						<?php
						}

					break;

					case 'ivpa_selectbox' :
						if ( $type !== 'get_custom_option' ) {
						?>
							<div class="ivpa_selectbox"><i class="ivpa-warning"></i> <span><?php _e( 'This style has no extra settings!', 'ivpawoo' ); ?></span></div>
						<?php
						}
						else {

							foreach ( $readyAtts as $term ) {

							?>
								<div class="ivpa_term" data-term="<?php echo $term['slug']; ?>">
								<?php
									if ( $type == 'get_custom_option' ) {
										self::get_custom_option_inputs( '%%', $term );
									}
								?>
								</div>
							<?php

							}

						}
					break;

					case 'ivpac_input' :
					case 'ivpac_checkbox' :
					case 'ivpac_textarea' :
					case 'ivpac_system' :

						foreach ( $readyAtts as $term ) {

						?>
							<div class="ivpa_term" data-term="<?php echo $term['slug']; ?>">
							<?php
								if ( $type == 'get_custom_option' ) {
									self::get_custom_option_inputs( '%%', $term );
								}
/*								if ( $curr_style !== 'ivpac_system' ) {
								?>
									<span class="ivpa_option ivpa_option_plaintext">
										<em><?php echo $term['name'] . ' ' . __('Tooltip', 'ivpawoo'); ?></em> <textarea name="ivpa_tooltip[%%][<?php echo $term['slug']; ?>]"></textarea>
									</span>
								<?php
								}*/
							?>
							</div>
						<?php

						}

					break;

					default :
					break;

				}

				$html .= ob_get_clean();

				die($html);
				exit;

			}
			else {
				die();
				exit;
			}

		}

		public static function ivpa_wpml_language() {

			if( class_exists( 'SitePress' ) ) {
				global $sitepress;

				if ( method_exists( $sitepress, 'get_default_language' ) ) {

					$default_language = $sitepress->get_default_language();
					$current_language = $sitepress->get_current_language();

					if ( $default_language != $current_language ) {
						return sanitize_title( $current_language );
					}
				}
			}
			return false;

		}

		public static function ivpa_get_attributes() {
			$attributes = get_object_taxonomies( 'product' );
			$ready_attributes = array();

			if ( !empty( $attributes ) ) {

				foreach( $attributes as $k ) {

					if ( substr($k, 0, 3) == 'pa_' ) {
						$ready_attributes[] = $k;
					}

				}

			}

			return $ready_attributes;
		}


		public static function delete_caches( $post_ID, $post, $update ) {

			$slug = 'product';

			if ( $slug != $post->post_type ) {
				return;
			}

			global $wpdb;

			$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID = {$post_ID} AND meta.meta_key LIKE '_ivpa_cached_%';" );

		}

	}

	add_action( 'init', 'WC_Ivpa_Settings::init');

?>