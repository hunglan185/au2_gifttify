<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_ADMIN_Update_Products' ) ) {
	class S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_ADMIN_Update_Products {
		protected static $settings;
		protected $is_page;
		protected $request;
		protected $process;
		protected $process_for_update;
		protected $process_single;
		protected $process_post_image;
		protected $my_options;
		protected $gmt_offset;

		public function __construct() {
			self::$settings = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::get_instance();
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ) );
			add_filter( 'manage_edit-product_columns', array( $this, 'button_update_from_shopify' ) );
			add_action( 'manage_product_posts_custom_column', array( $this, 'column_callback_product' ) );
			add_action( 'wp_ajax_s2w_update_products', array( $this, 'update_products' ) );
			add_action( 'wp_ajax_s2w_update_product_options_save', array( $this, 'save_options' ) );
//			add_filter('s2w_process_for_update_cron_interval',array( $this, 'cron_interval' ));
		}

		public function cron_interval( $interval ) {
			return 1;
		}

		public static function set( $name, $set_name = false ) {
			return VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::set( $name, $set_name );
		}

		public function admin_notices() {
			if ( $this->process_for_update->is_downloading() ) {
				?>
                <div class="updated">
                    <h4>
						<?php esc_html_e( 'S2W - Sync product images: Product images are being imported in the background.', 's2w-import-shopify-to-woocommerce' ) ?>
                    </h4>
                    <div>
						<?php printf( __( 'Please goto <a target="_blank" href="%s">Media</a> and view downloaded product images. If <strong>some images are downloaded repeatedly and no new images are downloaded</strong>, please:', 's2w-import-shopify-to-woocommerce' ), esc_url( admin_url( 'upload.php' ) ) ) ?>
                        <ol>
                            <li><?php printf( __( '<strong>Stop updating products immediately</strong>', 's2w-import-shopify-to-woocommerce' ) ) ?></li>
                            <li><?php printf( __( '<a class="s2w-cancel-download-images-button" href="%s">Cancel downloading</a></strong>', 's2w-import-shopify-to-woocommerce' ), esc_url( add_query_arg( array( 's2w_cancel_download_image_for_update' => '1', ), $_SERVER['REQUEST_URI'] ) ) ) ?></li>
                            <li><?php printf( __( 'Contact <strong>support@villatheme.com</strong> or create your ticket at <a target="_blank" href="https://villatheme.com/supports/forum/plugins/import-shopify-to-woocommerce/">https://villatheme.com/supports/forum/plugins/import-shopify-to-woocommerce/</a>', 's2w-import-shopify-to-woocommerce' ) ) ?></li>
                        </ol>
                    </div>
                </div>
				<?php
			} elseif ( ! $this->process_for_update->is_queue_empty() ) {
				?>
                <div class="updated">
                    <h4>
						<?php esc_html_e( 'S2W - Sync product images: There are products images in the queue.', 's2w-import-shopify-to-woocommerce' ) ?>
                    </h4>
                    <ol>
                        <li>
							<?php printf( __( 'If the same images are downloaded again and again, please <strong><a class="s2w-empty-queue-images-button" href="%s">Empty queue</a></strong> and go to Products to update missing images for your products.', 's2w-import-shopify-to-woocommerce' ), esc_url( add_query_arg( array( 's2w_cancel_download_image_for_update' => '1', ), $_SERVER['REQUEST_URI'] ) ) ) ?>
                        </li>
                        <li>
							<?php printf( __( 'If products images were downloading normally before, please <strong><a class="s2w-start-download-images-button" href="%s">Resume download</a></strong>', 's2w-import-shopify-to-woocommerce' ), add_query_arg( array( 's2w_start_download_image_for_update' => '1', ), esc_url( $_SERVER['REQUEST_URI'] ) ) ) ?>
                        </li>
                    </ol>
                </div>
				<?php
			} elseif ( get_transient( 's2w_background_processing_complete_for_update' ) ) {
				delete_transient( 's2w_background_processing_complete_for_update' );
				?>
                <div class="updated">
                    <p>
						<?php esc_html_e( 'S2W - Sync product images: Product images are migrated successfully.', 's2w-import-shopify-to-woocommerce' ) ?>
                    </p>
                </div>
				<?php
			}

		}

		public function plugins_loaded() {
			$this->process_for_update = new WP_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_Process_For_Update();
			if ( ! empty( $_REQUEST['s2w_cancel_download_image_for_update'] ) ) {
				$this->process_for_update->kill_process();
				wp_safe_redirect( @remove_query_arg( 's2w_cancel_download_image_for_update' ) );
				exit;
			} elseif ( ! empty( $_REQUEST['s2w_start_download_image_for_update'] ) ) {
				if ( ! $this->process_for_update->is_queue_empty() ) {
					$this->process_for_update->dispatch();
				}
				wp_safe_redirect( @remove_query_arg( 's2w_start_download_image_for_update' ) );
				exit;
			}
		}

		public function button_update_from_shopify( $cols ) {
			$cols['s2w_update_from_shopify'] = '<span class="s2w-button ' . self::set( 'shopify-update-product' ) . '">' . __( 'Shopify sync', 's2w-import-shopify-to-woocommerce' ) . '</span>';

			return $cols;
		}

		public function column_callback_product( $col ) {
			global $post;
			if ( $col === 's2w_update_from_shopify' ) {
				if ( null === $this->gmt_offset ) {
					$this->gmt_offset = get_option( 'gmt_offset' );
				}
				$all_options    = self::get_supported_options();
				$post_id        = $post->ID;
				$shopify_id     = get_post_meta( $post_id, '_shopify_product_id', true );
				$update_history = get_post_meta( $post_id, '_s2w_update_history', true );
				if ( $shopify_id ) {
					?>
                    <div class="<?php echo esc_attr( self::set( 'update-from-shopify-history' ) ) ?>">
						<?php
						if ( $update_history ) {
							$update_time        = isset( $update_history['time'] ) ? $update_history['time'] : '';
							$update_status      = isset( $update_history['status'] ) ? $update_history['status'] : '';
							$update_fields      = isset( $update_history['fields'] ) ? $update_history['fields'] : array();
							$update_fields_html = array();
							if ( is_array( $update_fields ) && count( $update_fields ) ) {
								foreach ( $update_fields as $update_field ) {
									$update_fields_html[] = $all_options[ $update_field ];
								}
								$update_fields_html = implode( ', ', $update_fields_html );
							}
							?>
                            <p><?php esc_html_e( 'Last sync: ', 's2w-import-shopify-to-woocommerce' ) ?>
                                <strong><span
                                            class="<?php echo esc_attr( self::set( 'update-from-shopify-history-time' ) ) ?>"><?php echo esc_html( date_i18n( 'F d, Y H:i:s', $update_time + $this->gmt_offset * 3600 ) ) ?></span></strong>
                            </p>
                            <p><?php esc_html_e( 'Status: ', 's2w-import-shopify-to-woocommerce' ) ?><strong><span
                                            class="<?php echo esc_attr( self::set( array(
												'update-from-shopify-history-status',
												'update-from-shopify-history-status-' . $update_status
											) ) ) ?>"><?php echo esc_html( ucwords( $update_status ) ) ?></span></strong>
                            </p>
                            <p><?php esc_html_e( 'Synced field(s): ', 's2w-import-shopify-to-woocommerce' ) ?>
                                <strong><span
                                            class="<?php echo esc_attr( self::set( 'update-from-shopify-history-fields' ) ) ?>"><?php echo $update_fields_html ?></span></strong>
                            </p>
							<?php

						} else {
							?>
                            <p><?php esc_html_e( 'Last sync: ', 's2w-import-shopify-to-woocommerce' ) ?>
                                <strong><span
                                            class="<?php echo esc_attr( self::set( 'update-from-shopify-history-time' ) ) ?>"></span></strong>
                            </p>
                            <p><?php esc_html_e( 'Status: ', 's2w-import-shopify-to-woocommerce' ) ?><strong><span
                                            class="<?php echo esc_attr( self::set( 'update-from-shopify-history-status' ) ) ?>"></span></strong>
                            </p>
                            <p><?php esc_html_e( 'Synced field(s): ', 's2w-import-shopify-to-woocommerce' ) ?>
                                <strong><span
                                            class="<?php echo esc_attr( self::set( 'update-from-shopify-history-fields' ) ) ?>"></span></strong>
                            </p>
							<?php
						}
						?>
                    </div>
                    <span class="s2w-button <?php echo esc_attr( self::set( 'shopify-product-id' ) ) ?>"
                          data-product_id="<?php echo esc_attr( $post_id ) ?>"
                          data-shopify_product_id="<?php echo esc_attr( $shopify_id ) ?>"><?php esc_html_e( 'Sync', 's2w-import-shopify-to-woocommerce' ) ?>
                        </span>
					<?php
				}
			}
		}

		public function save_options() {
			check_ajax_referer( 's2w_action_nonce', '_s2w_nonce' );
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die();
			}
			$update_product_options                  = isset( $_POST['update_product_options'] ) ? stripslashes_deep( $_POST['update_product_options'] ) : array();
			$update_product_options_show             = isset( $_POST['update_product_options_show'] ) ? sanitize_text_field( $_POST['update_product_options_show'] ) : '';
			$update_product_metafields               = isset( $_POST['update_product_metafields'] ) ? stripslashes_deep( $_POST['update_product_metafields'] ) : array();
			$settings                                = self::$settings->get_params();
			$settings['update_product_options']      = $update_product_options;
			$settings['update_product_options_show'] = $update_product_options_show;
			if ( count( $update_product_metafields ) ) {
				foreach ( $update_product_metafields['to'] as $key => $value ) {
					if ( strpos( $value, 's2w' ) === 0 || strpos( $value, '_s2w' ) === 0 ) {
						$update_product_metafields['to'][ $key ] = '';
					}
					if ( ! $update_product_metafields['to'][ $key ] && ! $update_product_metafields['from'][ $key ] ) {
						unset( $update_product_metafields['from'][ $key ] );
						unset( $update_product_metafields['to'][ $key ] );
					}
				}
				$update_product_metafields['from'] = array_values( $update_product_metafields['from'] );
				$update_product_metafields['to']   = array_values( $update_product_metafields['to'] );
			}
			$settings['update_product_metafields'] = $update_product_metafields;
			VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::update_option( 's2w_params', $settings );
			wp_send_json( array(
				'status' => 'success',
			) );
		}

		public function update_products() {
			global $s2w_settings;
			check_ajax_referer( 's2w_action_nonce', '_s2w_nonce' );
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die();
			}
			ignore_user_abort( true );
			$gmt_offset             = get_option( 'gmt_offset' );
			$product_id             = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
			$update_product_options = self::$settings->get_params( 'update_product_options' );
			if ( isset( $_POST['update_product_options'] ) ) {
				$update_product_options    = stripslashes_deep( $_POST['update_product_options'] );
				$update_product_metafields = isset( $_POST['update_product_metafields'] ) ? stripslashes_deep( $_POST['update_product_metafields'] ) : array();
				$settings                  = self::$settings->get_params();
				if ( count( $update_product_metafields ) ) {
					foreach ( $update_product_metafields['to'] as $key => $value ) {
						if ( strpos( $value, 's2w' ) === 0 || strpos( $value, '_s2w' ) === 0 ) {
							$update_product_metafields['to'][ $key ] = '';
						}
						if ( ! $update_product_metafields['to'][ $key ] && ! $update_product_metafields['from'][ $key ] ) {
							unset( $update_product_metafields['from'][ $key ] );
							unset( $update_product_metafields['to'][ $key ] );
						}
					}
					$update_product_metafields['from'] = array_values( $update_product_metafields['from'] );
					$update_product_metafields['to']   = array_values( $update_product_metafields['to'] );
				}
				$settings['update_product_metafields']   = $update_product_metafields;
				$settings['update_product_options']      = $update_product_options;
				$settings['update_product_options_show'] = isset( $_POST['update_product_options_show'] ) ? sanitize_text_field( $_POST['update_product_options_show'] ) : '';
				VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::update_option( 's2w_params', $settings );
				$s2w_settings   = $settings;
				self::$settings = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::get_instance( true );
			}
			$disable_background_process = self::$settings->get_params( 'disable_background_process' );
			$update_history             = array(
				'time'    => current_time( 'timestamp', true ),
				'status'  => 'error',
				'fields'  => $update_product_options,
				'message' => '',
			);
			$all_options                = self::get_supported_options();
			$fields                     = array();
			foreach ( $update_product_options as $update_field ) {
				$fields[] = $all_options[ $update_field ];
			}
			$fields = implode( ', ', $fields );
			if ( $product_id ) {
				$domain       = self::$settings->get_params( 'domain' );
				$access_token = self::$settings->get_params( 'access_token' );
				$api_key      = self::$settings->get_params( 'api_key' );
				$api_secret   = self::$settings->get_params( 'api_secret' );
				$product      = wc_get_product( $product_id );
				if ( $product ) {
					$shopify_id = get_post_meta( $product_id, '_shopify_product_id', true );
					if ( $shopify_id ) {
						add_filter( 'http_request_timeout', array( $this, 'bump_request_timeout' ) );
						if ( in_array( 'metafields', $update_product_options ) ) {
							$update_product_metafields = self::$settings->get_params( 'update_product_metafields' );
							$from                      = isset( $update_product_metafields['from'] ) ? $update_product_metafields['from'] : array();
							$to                        = isset( $update_product_metafields['to'] ) ? $update_product_metafields['to'] : array();
							if ( is_array( $from ) && is_array( $to ) && count( $from ) > 0 && count( $from ) === count( $to ) ) {
								if ( count( array_intersect( array_keys( array_filter( $from ) ), array_keys( array_filter( $to ) ) ) ) ) {
									$request = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::wp_remote_get_metafields( $domain, $access_token, $api_key, $api_secret, $shopify_id, 'products', false, array(), 300, true );
									if ( $request['status'] === 'success' ) {
										self::handle_metafields( $product_id, $request['data'], $from, $to );
										if ( $product->is_type( 'variable' ) ) {
											$variations = $product->get_children();
											if ( count( $variations ) ) {
												foreach ( $variations as $v_id ) {
													$shopify_v_id = get_post_meta( $v_id, '_shopify_variation_id', true );
													if ( $shopify_v_id ) {
														$request = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::wp_remote_get_metafields( $domain, $access_token, $api_key, $api_secret, "{$shopify_id},{$shopify_v_id}", 'variants', false, array(), 300, true );
														if ( $request['status'] === 'success' ) {
															self::handle_metafields( $v_id, $request['data'], $from, $to );
														}
													}
												}
											}
										}
										$update_history['status']  = 'success';
										$update_history['message'] = '';
										$response                  = $update_history;
										$response['time']          = date_i18n( 'F d, Y H:i:s', $response['time'] + $gmt_offset * 3600 );
										$response['fields']        = $fields;
									} else {
										$update_history['status']  = 'error';
										$update_history['message'] = $request['data'];
										$response                  = $update_history;
										$response['time']          = date_i18n( 'F d, Y H:i:s', $response['time'] + $gmt_offset * 3600 );
										$response['fields']        = $fields;
									}
								}
							}
						}
						if ( count( array_diff( $update_product_options, array( 'metafields' ) ) ) ) {
							$request = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::wp_remote_get( $domain, $access_token, $api_key, $api_secret, 'products', false, array( 'ids' => $shopify_id ) );
							if ( $request['status'] === 'success' ) {
								$product_data = $request['data'];
								if ( count( $product_data ) ) {
									$variants = isset( $product_data['variants'] ) ? $product_data['variants'] : array();
									$options  = isset( $product_data['options'] ) ? $product_data['options'] : array();
//								if ( count( $options ) > 1 ) {
//									foreach ( $options as $option_k => $option_v ) {
//										if ( ! isset( $option_v['values'] ) || ! is_array( $option_v['values'] ) || count( $option_v['values'] ) < 2 ) {
//											unset( $options[ $option_k ] );
//										}
//									}
//								}
									$new_data = array();
									if ( ! count( $options ) || ! count( $variants ) ) {
										$update_history['status']  = 'error';
										$update_history['message'] = esc_html__( 'Invalid data', 's2w-import-shopify-to-woocommerce' );
										update_post_meta( $product_id, '_s2w_update_history', $update_history );
										$response           = $update_history;
										$response['time']   = date_i18n( 'F d, Y H:i:s', $response['time'] + $gmt_offset * 3600 );
										$response['fields'] = $fields;
										wp_send_json( $response );
									}
									$update_which = array();
									foreach ( $all_options as $all_option_k => $all_option ) {
										$update_which[ $all_option_k ] = in_array( $all_option_k, $update_product_options );
									}
									if ( count( array_intersect( $update_product_options, array(
										'price',
										'inventory',
										'variations',
										'variation_attributes',
										'variation_sku',
									) ) )
									) {
										self::sync_product_variation_data( $product, $shopify_id, $update_which, $product_data );
										$new_data['price'] = $product->get_price_html();
									}

									if ( $update_which['product_url'] ) {
										$handle = isset( $product_data['handle'] ) ? $product_data['handle'] : '';
										if ( $handle ) {
											$product->set_slug( $handle );
											$product->save();
										}
									}
									if ( $update_which['tags'] ) {
										$tags = isset( $product_data['tags'] ) ? $product_data['tags'] : '';
										if ( $tags ) {
											$tags = explode( ',', $tags );
											wp_set_object_terms( $product_id, $tags, 'product_tag' );
											$display_tags = array();
											foreach ( $tags as $tag ) {
												$display_tags[] = '<a href="' . admin_url( 'edit.php?product_tag=' . $tag . '&post_type=product' ) . '">' . $tag . '</a>';
											}
											$new_data['tags'] = implode( ',', $display_tags );
										} else {
											$new_data['tags'] = '';
											wp_set_object_terms( $product_id, '', 'product_tag' );
										}
									}
									$dispatch = false;
									if ( $update_which['images'] ) {
										$current_product_image = get_post_meta( $product_id, '_thumbnail_id', true );
										$placeholder_image     = s2w_get_placeholder_image();
										$variations            = $product->is_type( 'variable' ) ? $product->get_children() : array();
										$images                = isset( $product_data['images'] ) ? $product_data['images'] : array();
										if ( is_array( $images ) && count( $images ) && ( ! $current_product_image || $current_product_image == $placeholder_image ) ) {
											$product_image = array_shift( $images );
											$variant_ids   = isset( $product_image['variant_ids'] ) ? $product_image['variant_ids'] : array();
											$src           = isset( $product_image['src'] ) ? $product_image['src'] : '';
											$alt           = isset( $product_image['alt'] ) ? $product_image['alt'] : '';
											if ( $src ) {
												$thumb_id = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::download_image( $product_image['id'], $src, $product_id );
												if ( $thumb_id && ! is_wp_error( $thumb_id ) ) {
													update_post_meta( $thumb_id, '_s2w_shopify_image_id', $product_image['id'] );
													if ( $alt ) {
														update_post_meta( $thumb_id, '_wp_attachment_image_alt', $alt );
													}
													if ( count( $variations ) ) {
														foreach ( $variations as $v_id ) {
															if ( in_array( get_post_meta( $v_id, '_shopify_variation_id', true ), $variant_ids ) ) {
																update_post_meta( $v_id, '_thumbnail_id', $thumb_id );
															}
														}
													}
													update_post_meta( $product_id, '_thumbnail_id', $thumb_id );
													$new_data['images'] = wp_get_attachment_image( $thumb_id, 'woocommerce_thumbnail' );
												} else {
													$update_history['status']  = 'error';
													$update_history['message'] = $thumb_id->get_error_message();
													update_post_meta( $product_id, '_s2w_update_history', $update_history );
													$response           = $update_history;
													$response['time']   = date_i18n( 'F d, Y H:i:s', $response['time'] + $gmt_offset * 3600 );
													$response['fields'] = $fields;
													wp_send_json( $response );
												}
											}
											if ( count( $images ) ) {
												foreach ( $images as $image_k => $image_v ) {
													$variant_ids = isset( $image_v['variant_ids'] ) ? $image_v['variant_ids'] : array();
													$images_data = array(
														'id'          => $image_v['id'],
														'src'         => $image_v['src'],
														'alt'         => $image_v['alt'],
														'parent_id'   => $product_id,
														'product_ids' => array(),
														'set_gallery' => 1,
													);
													if ( count( $variations ) && count( $variant_ids ) ) {
														foreach ( $variations as $v_id ) {
															if ( in_array( get_post_meta( $v_id, '_shopify_variation_id', true ), $variant_ids ) ) {
																$images_data['product_ids'][] = $v_id;
															}
														}
													}
													if ( $disable_background_process ) {
														S2W_Error_Images_Table::insert( $product_id, implode( ',', $images_data['product_ids'] ), $image_v['src'], $image_v['alt'], intval( $images_data['set_gallery'] ), $image_v['id'] );
													} else {
														$this->process_for_update->push_to_queue( $images_data );
														$dispatch = true;
													}
												}
											}
										}
									}
									$update_data = array();
									if ( $update_which['published_date'] ) {
										$published_at = isset( $product_data['published_at'] ) ? $product_data['published_at'] : '';
										if ( $published_at ) {
											$published_at_gmt             = strtotime( $published_at );
											$date_gmt                     = date( 'Y-m-d H:i:s', $published_at_gmt );
											$date                         = date( 'Y-m-d H:i:s', ( $published_at_gmt + $gmt_offset * 3600 ) );
											$update_data['post_date']     = $date;
											$update_data['post_date_gmt'] = $date_gmt;
											$new_data['post_date']        = sprintf( __( 'Published<br>%s', 's2w-import-shopify-to-woocommerce' ), date_i18n( 'Y/m/d h:i a', ( $published_at_gmt + $gmt_offset * 3600 ) ) );
										}
										$updated_at = isset( $product_data['updated_at'] ) ? $product_data['updated_at'] : '';
										if ( $updated_at ) {
											$updated_at_gmt_t                 = strtotime( $updated_at );
											$updated_at_gmt                   = date( 'Y-m-d H:i:s', $updated_at_gmt_t );
											$updated_at                       = date( 'Y-m-d H:i:s', ( $updated_at_gmt_t + $gmt_offset * 3600 ) );
											$update_data['post_modified']     = $updated_at;
											$update_data['post_modified_gmt'] = $updated_at_gmt;
										}
									}
									if ( $update_which['title'] ) {
										$title = isset( $product_data['title'] ) ? $product_data['title'] : '';
										if ( $title ) {
											$update_data['post_title'] = $title;
											$new_data['title']         = $title;
										}
									}
									if ( $update_which['product_status'] ) {
										$product_status         = self::$settings->get_params( 'product_status' );
										$product_status_mapping = self::$settings->get_params( 'product_status_mapping' );
										if ( ! empty( $product_status_mapping[ $product_data['status'] ] ) && $product_status_mapping[ $product_data['status'] ] !== 'not_import' ) {
											$product_status = $product_status_mapping[ $product_data['status'] ];
										}
										$product_status             = apply_filters( 's2w_import_product_status', $product_status, $product_data );
										$update_data['post_status'] = $product_status;
									}
									if ( $update_which['description'] ) {
										$description = isset( $product_data['body_html'] ) ? html_entity_decode( $product_data['body_html'], ENT_QUOTES | ENT_XML1, 'UTF-8' ) : '';
										if ( $description ) {
											if ( self::$settings->get_params( 'download_description_images' ) ) {
												preg_match_all( '/src="([\s\S]*?)"/im', preg_replace( '/<script\>[\s\S]*?<\/script>/im', '', $description ), $matches );
												if ( isset( $matches[1] ) && is_array( $matches[1] ) && count( $matches[1] ) ) {
													$description_images = array_unique( $matches[1] );
													if ( $disable_background_process ) {
														foreach ( $description_images as $description_image ) {
															S2W_Error_Images_Table::insert( $product_id, implode( ',', array() ), $description_image, '', 2, '' );
														}
													} else {
														foreach ( $description_images as $description_image ) {
															$images_data = array(
																'id'          => '',
																'src'         => $description_image,
																'alt'         => '',
																'parent_id'   => $product_id,
																'product_ids' => array(),
																'set_gallery' => 2,
															);
															$this->process_for_update->push_to_queue( $images_data );
														}
														$dispatch = true;
													}
												}
											}
											$update_data['post_content'] = $description;
										}
									}
									if ( count( $update_data ) ) {
										$update_data['ID'] = $product_id;
										wp_update_post( $update_data );
									}
									if ( $dispatch ) {
										$this->process_for_update->save()->dispatch();
									}
									$update_history['status']  = 'success';
									$update_history['message'] = '';
									update_post_meta( $product_id, '_s2w_update_history', $update_history );
									$response           = $update_history;
									$response['time']   = date_i18n( 'F d, Y H:i:s', $response['time'] + $gmt_offset * 3600 );
									$response['fields'] = $fields;
									do_action( 's2w_update_product_data_successfully', $product_id, $product_data );
									wp_send_json( array_merge( $response, $new_data ) );
								} else {
									$update_history['status']  = 'error';
									$update_history['message'] = esc_html__( 'Not found', 's2w-import-shopify-to-woocommerce' );
									update_post_meta( $product_id, '_s2w_update_history', $update_history );
									$response           = $update_history;
									$response['time']   = date_i18n( 'F d, Y H:i:s', $response['time'] + $gmt_offset * 3600 );
									$response['fields'] = $fields;
									wp_send_json( $response );
								}
							} else {
								$update_history['status']  = 'error';
								$update_history['message'] = $request['data'];
								update_post_meta( $product_id, '_s2w_update_history', $update_history );
								$response           = $update_history;
								$response['time']   = date_i18n( 'F d, Y H:i:s', $response['time'] + $gmt_offset * 3600 );
								$response['fields'] = $fields;
								wp_send_json( $response );
							}
						} else {
							update_post_meta( $product_id, '_s2w_update_history', $update_history );
							$response           = $update_history;
							$response['time']   = date_i18n( 'F d, Y H:i:s', $response['time'] + $gmt_offset * 3600 );
							$response['fields'] = $fields;
							wp_send_json( $response );
						}
					}
					$response           = $update_history;
					$response['time']   = date_i18n( 'F d, Y H:i:s', $response['time'] + $gmt_offset * 3600 );
					$response['fields'] = $fields;
					wp_send_json( $response );
				}
			} else {
				wp_send_json( array(
					'status'  => 'error',
					'message' => ''
				) );
			}
		}

		/**
		 * @param $product WC_Product
		 * @param $shopify_id
		 * @param $update_which
		 * @param $product_data
		 */
		public static function sync_product_variation_data( $product, $shopify_id, $update_which, $product_data ) {
			$variants             = isset( $product_data['variants'] ) ? $product_data['variants'] : array();
			$options              = isset( $product_data['options'] ) ? $product_data['options'] : array();
			$images               = isset( $product_data['images'] ) ? $product_data['images'] : array();
			$product_id           = $product->get_id();
			$global_attributes    = self::$settings->get_params( 'global_attributes' );
			$manage_stock         = ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) ? true : false;
			$attr_data            = array();
			$imported_variant_ids = array();
			if ( $update_which['variation_attributes'] || $update_which['variations'] ) {
				if ( $global_attributes ) {
					foreach ( $options as $option_k => $option_v ) {
						S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_product_global_attribute( $option_v, $attr_data );
					}
				} else {
					foreach ( $options as $option_k => $option_v ) {
						S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_product_custom_attribute( $option_v, $attr_data );
					}
				}
				$product->set_attributes( $attr_data );
				$product->save();
			}
			if ( $product->is_type( 'variable' ) ) {
				if ( $update_which['inventory'] ) {
					update_post_meta( $product_id, '_manage_stock', 'no' );
				}
				$variations = $product->get_children();
				if ( count( $variations ) ) {
					foreach ( $variations as $variation_k => $variation_id ) {
						vi_s2w_set_time_limit();
						$shopify_variation_id = get_post_meta( $variation_id, '_shopify_variation_id', true );
						if ( $shopify_variation_id ) {
							$imported_variant_ids[] = $shopify_variation_id;
							foreach ( $variants as $variant_k => $variant ) {
								vi_s2w_set_time_limit();
								if ( $variant['id'] == $shopify_variation_id ) {
									$variation_sku = apply_filters( 's2w_variation_product_sku', $variant['sku'], $variant, $product_data );
									if ( $variation_sku && $update_which['variation_sku'] ) {
										update_post_meta( $variation_id, '_sku', $variation_sku );
									}
									$variation      = wc_get_product( $variation_id );
									$save_variation = false;
									if ( $update_which['price'] ) {
										$regular_price = $variant['compare_at_price'];
										$sale_price    = $variant['price'];
										if ( ! floatval( $regular_price ) || floatval( $regular_price ) == floatval( $sale_price ) ) {
											$regular_price = $sale_price;
											$sale_price    = '';
										}
										$variation->set_regular_price( $regular_price );
										$variation->set_sale_price( $sale_price );
										$save_variation = true;
									}
									if ( $update_which['inventory'] ) {
										$inventory = $variant['inventory_quantity'];
										if ( $manage_stock ) {
											$variation->set_manage_stock( 'yes' );
											$variation->set_stock_quantity( $inventory );
											if ( $variant['inventory_policy'] === 'continue' ) {
												$variation->set_backorders( 'yes' );
											} else {
												$variation->set_backorders( 'no' );
											}
										} else {
											$variation->set_manage_stock( 'no' );
											delete_post_meta( $variation_id, '_stock' );
											$variation->set_stock_status( 'instock' );
										}
										$save_variation = true;
									}

									if ( count( $attr_data ) ) {
										if ( $global_attributes ) {
											$attributes = S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_variation_global_attribute( $options, $variant );
										} else {
											$attributes = S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_variation_custom_attribute( $options, $variant );
										}
										$variation->set_attributes( $attributes );
										$save_variation = true;
									}
									if ( $save_variation ) {
										$variation->save();
									}
									do_action( 's2w_update_variation_data_successfully', $variation->get_id(), $variant, $product_data );
									break;
								}
							}
						}
					}
				}
			} else {
				if ( count( $variants ) === 1 ) {
					if ( $update_which['variation_sku'] ) {
						$sku = apply_filters( 's2w_simple_product_sku', $variants[0]['sku'], $shopify_id, $product_data['handle'] );
						if ( $sku ) {
							update_post_meta( $product_id, '_sku', $sku );
						}
					}
					$imported_variant_ids[] = $variants[0]['id'];
					if ( $update_which['price'] ) {
						$regular_price = $variants[0]['compare_at_price'];
						$sale_price    = $variants[0]['price'];
						if ( ! floatval( $regular_price ) || floatval( $regular_price ) == floatval( $sale_price ) ) {
							$regular_price = $sale_price;
							$sale_price    = '';
						}
						update_post_meta( $product_id, '_regular_price', $regular_price );
						update_post_meta( $product_id, '_sale_price', $sale_price );
						if ( $sale_price ) {
							update_post_meta( $product_id, '_price', $sale_price );
						} else {
							update_post_meta( $product_id, '_price', $regular_price );
						}
					}
					if ( $update_which['inventory'] ) {
						if ( $manage_stock ) {
							$inventory = $variants[0]['inventory_quantity'];
							$product->set_manage_stock( 'yes' );
							$product->set_stock_quantity( $inventory );
							if ( $variants[0]['inventory_policy'] === 'continue' ) {
								$product->set_backorders( 'yes' );
							} else {
								$product->set_backorders( 'no' );
							}
						} else {
							$product->set_manage_stock( 'no' );
							delete_post_meta( $product_id, '_stock' );
							$product->set_stock_status( 'instock' );
						}
						$product->save();
					}
				} else {

				}
			}
			if ( $update_which['variations'] ) {
				$missing_variant_ids = array_diff( array_column( $variants, 'id' ), $imported_variant_ids );
				if ( count( $missing_variant_ids ) ) {
					foreach ( $variants as $variant_k => $variant ) {
						vi_s2w_set_time_limit();
						if ( in_array( $variant['id'], $missing_variant_ids ) ) {
							$regular_price = $variant['compare_at_price'];
							$sale_price    = $variant['price'];
							if ( ! floatval( $regular_price ) || floatval( $regular_price ) == floatval( $sale_price ) ) {
								$regular_price = $sale_price;
								$sale_price    = '';
							}
							$variation_obj = new WC_Product_Variation();
							$variation_obj->set_parent_id( $product_id );
							if ( $global_attributes ) {
								$attributes = S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_variation_global_attribute( $options, $variant );
							} else {
								$attributes = S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_variation_custom_attribute( $options, $variant );
							}
							$variation_obj->set_attributes( $attributes );
							$variation_sku    = apply_filters( 's2w_variation_product_sku', $variant['sku'], $variant, $product_data );
							$variation_fields = array(
								'sku'           => VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::sku_exists( $variation_sku ) ? '' : $variation_sku,
								'regular_price' => $regular_price,
							);
							if ( $manage_stock ) {
								$variation_obj->set_manage_stock( 'yes' );
								$variation_obj->set_stock_quantity( $variant['inventory_quantity'] );
								if ( $variant['inventory_quantity'] ) {
									$variation_obj->set_stock_status( 'instock' );
								} else {
									$variation_obj->set_stock_status( 'outofstock' );
								}
								if ( $variant['inventory_policy'] === 'continue' ) {
									$variation_obj->set_backorders( 'yes' );
								} else {
									$variation_obj->set_backorders( 'no' );
								}
							} else {
								$variation_obj->set_manage_stock( 'no' );
								$variation_obj->set_stock_status( 'instock' );
							}
							if ( $variant['weight'] ) {
								$variation_fields['weight'] = $variant['weight'];
							}
							if ( $sale_price ) {
								$variation_fields['sale_price'] = $sale_price;
							}
							foreach ( $variation_fields as $field => $field_v ) {
								$variation_obj->{"set_$field"}( wc_clean( $field_v ) );
							}
							do_action( 'product_variation_linked', $variation_obj->save() );
							$variation_obj_id = $variation_obj->get_id();
							if ( count( $images ) ) {
								foreach ( $images as $image_k => $image_v ) {
									if ( in_array( $variant['id'], $image_v['variant_ids'] ) ) {
										$src = isset( $image_v['src'] ) ? $image_v['src'] : '';
										$alt = isset( $image_v['alt'] ) ? $image_v['alt'] : '';
										if ( $src ) {
											$thumb_id = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::download_image( $image_v['id'], $src, $product_id );
											if ( $thumb_id && ! is_wp_error( $thumb_id ) ) {
												update_post_meta( $thumb_id, '_s2w_shopify_image_id', $image_v['id'] );
												if ( $alt ) {
													update_post_meta( $thumb_id, '_wp_attachment_image_alt', $alt );
												}
												update_post_meta( $variation_obj_id, '_thumbnail_id', $thumb_id );
											}
										}
										break;
									}
								}
							}
							update_post_meta( $variation_obj_id, '_shopify_variation_id', $variant['id'] );
							do_action( 's2w_update_variation_data_successfully', $variation_obj_id, $variant, $product_data );
						}
					}
					if ( ! $product->is_type( 'variable' ) ) {
						wp_set_object_terms( $product_id, 'variable', 'product_type' );
					}
				}
			}
		}

		public function bump_request_timeout( $val ) {
			return self::$settings->get_params( 'request_timeout' );
		}

		public function admin_enqueue_script() {
			global $pagenow;
			$post_type = isset( $_REQUEST['post_type'] ) ? sanitize_text_field( $_REQUEST['post_type'] ) : '';
			if ( $pagenow === 'edit.php' && $post_type === 'product' ) {
				wp_enqueue_style( 's2w-import-shopify-to-woocommerce-update-product', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_CSS . 'update-product.css' );
				wp_enqueue_script( 's2w-import-shopify-to-woocommerce-update-product', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_JS . 'update-products.js', array( 'jquery' ), VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_VERSION );
				wp_localize_script( 's2w-import-shopify-to-woocommerce-update-product', 's2w_params_admin_update_products', array(
					'url'                         => admin_url( 'admin-ajax.php' ),
					'update_product_options'      => self::$settings->get_params( 'update_product_options' ),
					'update_product_options_show' => self::$settings->get_params( 'update_product_options_show' ),
					'_s2w_nonce'                  => wp_create_nonce( 's2w_action_nonce' ),
				) );
				add_action( 'admin_footer', array( $this, 'wp_footer' ) );
			}
		}

		public function wp_footer() {
			$all_options    = self::get_supported_options();
			$descriptions   = array(
				'description' => self::$settings->get_params( 'download_description_images' ) ? __( 'Migrate description images is currently <strong>Enabled</strong>', 's2w-import-shopify-to-woocommerce' ) : __( 'Migrate description images is currently <strong>Disabled</strong>', 's2w-import-shopify-to-woocommerce' ),
			);
			$update_options = self::$settings->get_params( 'update_product_options' );
			?>
            <div class="<?php echo esc_attr( self::set( array(
				'update-product-options-container',
				'hidden'
			) ) ) ?>">
				<?php wp_nonce_field( 's2w_update_product_options_action_nonce', '_s2w_update_product_options_nonce' ) ?>
                <div class="<?php echo esc_attr( self::set( 'overlay' ) ) ?>"></div>
                <div class="<?php echo esc_attr( self::set( 'update-product-options-content' ) ) ?>">
                    <div class="<?php echo esc_attr( self::set( 'update-product-options-content-header' ) ) ?>">
                        <h2><?php esc_html_e( 'Sync options', 's2w-import-shopify-to-woocommerce' ) ?></h2>
                        <span class="<?php echo esc_attr( self::set( 'update-product-options-close' ) ) ?>"></span>
                    </div>
                    <div class="<?php echo esc_attr( self::set( 'update-product-options-content-body' ) ) ?>">
						<?php
						foreach ( $all_options as $option_key => $option_value ) {
							?>
                            <div class="<?php echo esc_attr( self::set( 'update-product-options-content-body-row' ) ) ?>">
                                <div class="<?php echo esc_attr( self::set( 'update-product-options-option-wrap' ) ) ?>">
                                    <input type="checkbox" value="1"
                                           data-product_option="<?php echo $option_key ?>"
										<?php if ( in_array( $option_key, $update_options ) ) {
											echo esc_attr( 'checked' );
										} ?>
                                           id="<?php echo esc_attr( self::set( 'update-product-options-' . $option_key ) ) ?>"
                                           class="<?php echo esc_attr( self::set( 'update-product-options-option' ) ) ?>">
                                    <label for="<?php echo esc_attr( self::set( 'update-product-options-' . $option_key ) ) ?>"><?php echo $option_value ?></label>
									<?php
									if ( ! empty( $descriptions[ $option_key ] ) ) {
										?>
                                        <div class="<?php echo esc_attr( self::set( 'option-description' ) ) ?>"><?php echo $descriptions[ $option_key ] ?></div>
										<?php
									}
									?>
                                </div>
                            </div>
							<?php
						}
						$update_product_metafields = self::$settings->get_params( 'update_product_metafields' );
						$from                      = isset( $update_product_metafields['from'] ) ? $update_product_metafields['from'] : array();
						$to                        = isset( $update_product_metafields['to'] ) ? $update_product_metafields['to'] : array();
						if ( ! is_array( $from ) || ! is_array( $to ) || ! count( $from ) || count( $from ) !== count( $to ) ) {
							$from = $to = array( '' );
						}
						?>
                        <table class="<?php echo esc_attr( self::set( 'product-metafields-mapping' ) ) ?> wp-list-table widefat fixed striped">
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'Shopify metafield key', 's2w-import-shopify-to-woocommerce' ) ?></th>
                                <th><?php esc_html_e( 'Woo product meta key', 's2w-import-shopify-to-woocommerce' ) ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							for ( $i = 0; $i < count( $from ); $i ++ ) {
								?>
                                <tr>
                                    <td>
                                        <input type="text"
                                               class="<?php echo esc_attr( self::set( 'update_product_metafields_from' ) ) ?>"
                                               name="<?php echo esc_attr( self::set( 'update_product_metafields[from][]', true ) ) ?>"
                                               value="<?php echo esc_attr( $from[ $i ] ) ?>">
                                    </td>
                                    <td>
                                        <input type="text"
                                               class="<?php echo esc_attr( self::set( 'update_product_metafields_to' ) ) ?>"
                                               name="<?php echo esc_attr( self::set( 'update_product_metafields[to][]', true ) ) ?>"
                                               value="<?php echo esc_attr( $to[ $i ] ) ?>">
                                    </td>
                                    <td>
                                        <div>
                                            <i class="dashicons dashicons-admin-page <?php echo esc_attr( self::set( array(
												'product-metafields-button',
												'product-metafields-duplicate'
											) ) ) ?>"></i>
                                            <i class="dashicons dashicons-trash <?php echo esc_attr( self::set( array(
												'product-metafields-button',
												'product-metafields-remove'
											) ) ) ?>"></i>
                                        </div>
                                    </td>
                                </tr>
								<?php
							}
							?>
                            </tbody>
                        </table>
                    </div>
                    <div class="<?php echo esc_attr( self::set( 'update-product-options-content-body-1' ) ) ?>">
                        <div class="<?php echo esc_attr( self::set( 'update-product-options-content-body-row' ) ) ?>">
                            <input type="checkbox" value="1"
								<?php checked( '1', self::$settings->get_params( 'update_product_options_show' ) ) ?>
                                   id="<?php echo esc_attr( self::set( 'update-product-options-show' ) ) ?>"
                                   class="<?php echo esc_attr( self::set( 'update-product-options-show' ) ) ?>">
                            <label for="<?php echo esc_attr( self::set( 'update-product-options-show' ) ) ?>"><?php esc_html_e( 'Show these options when clicking on "Sync" button on each product', 's2w-import-shopify-to-woocommerce' ) ?></label>
                        </div>
                    </div>
                    <div class="<?php echo esc_attr( self::set( 'update-product-options-content-footer' ) ) ?>">
                        <span class="button-primary <?php echo esc_attr( self::set( array(
	                        'update-product-options-button-save',
	                        'button',
	                        'hidden'
                        ) ) ) ?>">
                            <?php esc_html_e( 'Save', 's2w-import-shopify-to-woocommerce' ) ?>
                        </span>
                        <span class="button-primary <?php echo esc_attr( self::set( array(
							'update-product-options-button-update',
							'button',
							'hidden'
						) ) ) ?>">
                            <?php esc_html_e( 'Sync selected', 's2w-import-shopify-to-woocommerce' ) ?>(<span
                                    class="<?php echo esc_attr( self::set( 'selected-number' ) ) ?>">0</span>)
                        </span>
                        <span class="button-primary <?php echo esc_attr( self::set( array(
							'update-product-options-button-update-single',
							'button',
							'hidden'
						) ) ) ?>" data-update_product_id="">
                            <?php esc_html_e( 'Sync', 's2w-import-shopify-to-woocommerce' ) ?>
                        </span>
                        <span class="<?php echo esc_attr( self::set( array(
							'update-product-options-button-cancel',
							'button'
						) ) ) ?>">
                            <?php esc_html_e( 'Cancel', 's2w-import-shopify-to-woocommerce' ) ?>
                        </span>
                    </div>
                </div>
                <div class="<?php echo esc_attr( self::set( 'saving-overlay' ) ) ?>"></div>
            </div>
			<?php
		}

		public static function get_supported_options() {
			return array(
				'title'                => esc_html__( 'Title', 's2w-import-shopify-to-woocommerce' ),
				'price'                => esc_html__( 'Price', 's2w-import-shopify-to-woocommerce' ),
				'inventory'            => esc_html__( 'Inventory', 's2w-import-shopify-to-woocommerce' ),
				'description'          => esc_html__( 'Description', 's2w-import-shopify-to-woocommerce' ),
				'product_status'       => esc_html__( 'Status', 's2w-import-shopify-to-woocommerce' ),
				'images'               => esc_html__( 'Images', 's2w-import-shopify-to-woocommerce' ),
				'variations'           => esc_html__( 'New variations', 's2w-import-shopify-to-woocommerce' ),
				'variation_attributes' => esc_html__( 'Variation attributes', 's2w-import-shopify-to-woocommerce' ),
				'variation_sku'        => esc_html__( 'Variation SKU', 's2w-import-shopify-to-woocommerce' ),
				'product_url'          => esc_html__( 'Product slug', 's2w-import-shopify-to-woocommerce' ),
				'tags'                 => esc_html__( 'Tags', 's2w-import-shopify-to-woocommerce' ),
				'published_date'       => esc_html__( 'Published date', 's2w-import-shopify-to-woocommerce' ),
				'metafields'           => esc_html__( 'Metafields', 's2w-import-shopify-to-woocommerce' ),
			);
		}

		private static function handle_metafields( $product_id, $metafields, $from, $to ) {
			if ( count( $metafields ) ) {
				foreach ( $metafields as $metafield ) {
					$metafield_key   = isset( $metafield['key'] ) ? $metafield['key'] : '';
					$metafield_value = isset( $metafield['value'] ) ? $metafield['value'] : '';
					foreach ( $from as $key => $value ) {
						if ( $value && $to[ $key ] && $value === $metafield_key ) {
							update_post_meta( $product_id, $to[ $key ], $metafield_value );
							break;
						}
					}
				}
			}
		}
	}
}
