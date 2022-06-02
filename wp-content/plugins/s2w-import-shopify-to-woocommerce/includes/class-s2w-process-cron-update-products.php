<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_Process_Cron_Update_Products extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 's2w_process_cron_update_products';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		$product_id = isset( $item['product_id'] ) ? $item['product_id'] : '';
		if ( isset( $item['product_data'] ) ) {
			$product_data = vi_s2w_json_decode( $item['product_data'] );
			$shopify_id   = isset( $product_data['shopify_id'] ) ? $product_data['shopify_id'] : '';
			$variants     = isset( $product_data['variants'] ) ? $product_data['variants'] : array();
		} else {
			$shopify_id   = isset( $item['shopify_id'] ) ? $item['shopify_id'] : '';
			$variants     = isset( $item['variants'] ) ? vi_s2w_json_decode( $item['variants'] ) : array();
			$product_data = array(
				'id'       => $shopify_id,
				'variants' => $variants,
			);
		}

		if ( $product_id && is_array( $variants ) && count( $variants ) ) {
			try {
				$settings     = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::get_instance();
				$domain       = $settings->get_params( 'domain' );
				$access_token = $settings->get_params( 'access_token' );
				$api_key      = $settings->get_params( 'api_key' );
				$api_secret   = $settings->get_params( 'api_secret' );
				if ( $domain && $api_key && $api_secret ) {
					$path                   = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::get_cache_path( $domain, $access_token, $api_key, $api_secret ) . '/';
					$log_file               = $path . 'cron_update_products_logs.txt';
					$update_product_options = $settings->get_params( 'cron_update_products_options' );
					if ( is_array( $update_product_options ) ) {
						$product = wc_get_product( $product_id );
						if ( $product ) {
							VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::log( $log_file, "Cron update product '{$product->get_title()}'." );
							if ( isset( $item['product_data'] ) ) {
								$update_which = array();
								$all_options  = S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_ADMIN_Update_Products::get_supported_options();
								foreach ( $all_options as $all_option_k => $all_option ) {
									$update_which[ $all_option_k ] = in_array( $all_option_k, $update_product_options );
								}
								S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_ADMIN_Update_Products::sync_product_variation_data( $product, $shopify_id, $update_which, $product_data );
							} else {
								/*backward compatibility with queued items from previous versions(before 1.1.5)*/
								$manage_stock = ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) ? true : false;
								if ( count( $update_product_options ) == 2 ) {
									if ( $product->is_type( 'variable' ) ) {
										$variations = $product->get_children();
										if ( count( $variations ) ) {
											foreach ( $variations as $variation_k => $variation_id ) {
												vi_s2w_set_time_limit();
												$shopify_variation_id = get_post_meta( $variation_id, '_shopify_variation_id', true );
												if ( $shopify_variation_id ) {
													foreach ( $variants as $variant_k => $variant_v ) {
														vi_s2w_set_time_limit();
														if ( $variant_v['id'] == $shopify_variation_id ) {
															$inventory     = $variant_v['inventory_quantity'];
															$regular_price = $variant_v['compare_at_price'];
															$sale_price    = $variant_v['price'];
															if ( ! floatval( $regular_price ) || floatval( $regular_price ) == floatval( $sale_price ) ) {
																$regular_price = $sale_price;
																$sale_price    = '';
															}
															$variation = wc_get_product( $variation_id );
															if ( $manage_stock ) {
																$variation->set_manage_stock( 'yes' );
																$variation->set_stock_quantity( $inventory );
																if ( $variant_v['inventory_policy'] === 'continue' ) {
																	$variation->set_backorders( 'yes' );
																} else {
																	$variation->set_backorders( 'no' );
																}
															} else {
																$variation->set_manage_stock( 'no' );
																delete_post_meta( $variation_id, '_stock' );
																$variation->set_stock_status( 'instock' );
															}
															$variation->set_regular_price( $regular_price );
															$variation->set_sale_price( $sale_price );
															$variation->save();
															break;
														}
													}
												}
											}
										}
									} else {
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
										if ( $manage_stock ) {
											$product->set_manage_stock( 'yes' );
											$inventory = $variants[0]['inventory_quantity'];
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
								} elseif ( in_array( 'price', $update_product_options ) ) {
									if ( $product->is_type( 'variable' ) ) {
										$variations = $product->get_children();
										if ( count( $variations ) ) {
											foreach ( $variations as $variation_k => $variation_id ) {
												vi_s2w_set_time_limit();
												$shopify_variation_id = get_post_meta( $variation_id, '_shopify_variation_id', true );
												if ( $shopify_variation_id ) {
													foreach ( $variants as $variant_k => $variant_v ) {
														vi_s2w_set_time_limit();
														if ( $variant_v['id'] == $shopify_variation_id ) {
															$regular_price = $variant_v['compare_at_price'];
															$sale_price    = $variant_v['price'];
															if ( ! floatval( $regular_price ) || floatval( $regular_price ) == floatval( $sale_price ) ) {
																$regular_price = $sale_price;
																$sale_price    = '';
															}
															$variation = wc_get_product( $variation_id );
															$variation->set_regular_price( $regular_price );
															$variation->set_sale_price( $sale_price );
															$variation->save();
															break;
														}
													}
												}
											}
										}
									} else {
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
								} elseif ( in_array( 'inventory', $update_product_options ) ) {
									if ( $product->is_type( 'variable' ) ) {
										$variations = $product->get_children();
										if ( count( $variations ) ) {
											foreach ( $variations as $variation_k => $variation_id ) {
												vi_s2w_set_time_limit();
												$shopify_variation_id = get_post_meta( $variation_id, '_shopify_variation_id', true );
												if ( $shopify_variation_id ) {
													foreach ( $variants as $variant_k => $variant_v ) {
														vi_s2w_set_time_limit();
														if ( $variant_v['id'] == $shopify_variation_id ) {
															$inventory = $variant_v['inventory_quantity'];
															$variation = wc_get_product( $variation_id );
															if ( $manage_stock ) {
																$variation->set_manage_stock( 'yes' );
																$variation->set_stock_quantity( $inventory );
																if ( $variant_v['inventory_policy'] === 'continue' ) {
																	$variation->set_backorders( 'yes' );
																} else {
																	$variation->set_backorders( 'no' );
																}
															} else {
																$variation->set_manage_stock( 'no' );
																delete_post_meta( $variation_id, '_stock' );
																$variation->set_stock_status( 'instock' );
															}
															$variation->save();
															break;
														}
													}
												}
											}
										}
									} else {
										if ( $manage_stock ) {
											$product->set_manage_stock( 'yes' );
											$inventory = $variants[0]['inventory_quantity'];
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
								}
							}
						}
					}
				}
			} catch ( Error $e ) {
				error_log( 'S2W error log - cron update products: ' . $e->getMessage() );

				return false;
			} catch ( Exception $e ) {
				error_log( 'S2W error log - cron update products: ' . $e->getMessage() );

				return false;
			}
		}

		return false;
	}

	/**
	 * Is the updater running?
	 *
	 * @return boolean
	 */
	public function is_downloading() {
		return $this->is_process_running();
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		if ( ! $this->is_process_running() && $this->is_queue_empty() ) {
			$settings     = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::get_instance();
			$domain       = $settings->get_params( 'domain' );
			$access_token = $settings->get_params( 'access_token' );
			$api_key      = $settings->get_params( 'api_key' );
			$api_secret   = $settings->get_params( 'api_secret' );
			if ( $domain && $api_key && $api_secret ) {
				$path     = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::get_cache_path( $domain, $access_token, $api_key, $api_secret ) . '/';
				$log_file = $path . 'cron_update_products_logs.txt';
				VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::log( $log_file, 'Cron update products finished.' . PHP_EOL );
				set_transient( 's2w_background_processing_cron_update_products_complete', time() );
			}
		}
		// Show notice to user or perform some other arbitrary task...
		parent::complete();
	}

	/**
	 * Delete all batches.
	 *
	 * @return WP_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_Process_Cron_Update_Products
	 */
	public function delete_all_batches() {
		global $wpdb;

		$table  = $wpdb->options;
		$column = 'option_name';

		if ( is_multisite() ) {
			$table  = $wpdb->sitemeta;
			$column = 'meta_key';
		}

		$key = $wpdb->esc_like( $this->identifier . '_batch_' ) . '%';

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE {$column} LIKE %s", $key ) ); // @codingStandardsIgnoreLine.

		return $this;
	}

	/**
	 * Kill process.
	 *
	 * Stop processing queue items, clear cronjob and delete all batches.
	 */
	public function kill_process() {
		if ( ! $this->is_queue_empty() ) {
			$this->delete_all_batches();
			wp_clear_scheduled_hook( $this->cron_hook_identifier );
		}
	}
}