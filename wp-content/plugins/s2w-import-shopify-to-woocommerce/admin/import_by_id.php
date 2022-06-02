<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_ADMIN_Import_By_Id' ) ) {
	class S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_ADMIN_Import_By_Id {
		protected $settings;
		protected $is_page;
		protected $request;
		protected $process;
		protected $process_for_update;
		protected $process_single_new;
		protected $process_post_image;
		protected $my_options;
		protected $gmt_offset;

		public function __construct() {
			$this->settings = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::get_instance();
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 18 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 1 );
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
			add_action( 'wp_ajax_s2w_import_shopify_to_woocommerce_by_id', array( $this, 'import_by_id' ) );
		}

		public function admin_enqueue_scripts() {
			wp_enqueue_script( 's2w-import-shopify-to-woocommerce-cancel-download-images', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_JS . 'cancel-download-images.js', array( 'jquery' ), VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_VERSION );
			global $pagenow;
			$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
			if ( $pagenow === 'admin.php' && $page === 's2w-import-shopify-to-woocommerce-import-by-id' ) {
				wp_dequeue_style( 'eopa-admin-css' );
				wp_enqueue_style( 's2w-import-shopify-to-woocommerce-form', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_CSS . 'form.min.css' );
				wp_enqueue_style( 's2w-import-shopify-to-woocommerce-button', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_CSS . 'button.min.css' );
				wp_enqueue_style( 's2w-import-shopify-to-woocommerce-icon', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_CSS . 'icon.min.css' );
				wp_enqueue_style( 's2w-import-shopify-to-woocommerce-dropdown', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_CSS . 'dropdown.min.css' );
				wp_enqueue_style( 's2w-import-shopify-to-woocommerce-checkbox', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_CSS . 'checkbox.min.css' );
				wp_enqueue_style( 's2w-import-shopify-to-woocommerce-transition', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_CSS . 'transition.min.css' );
				wp_enqueue_style( 's2w-import-shopify-to-woocommerce-segment', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_CSS . 'segment.min.css' );
				wp_enqueue_style( 's2w-import-shopify-to-woocommerce-menu', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_CSS . 'menu.min.css' );
				wp_enqueue_style( 's2w-import-shopify-to-woocommerce-progress', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_CSS . 'progress.min.css' );
				wp_enqueue_style( 's2w-import-shopify-to-woocommerce-accordion', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_CSS . 'accordion.min.css' );
				wp_enqueue_style( 's2w-import-shopify-to-woocommerce-table', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_CSS . 'table.min.css' );
				wp_enqueue_style( 's2w-import-shopify-to-woocommerce-select2', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_CSS . 'select2.min.css' );
				wp_enqueue_script( 's2w-import-shopify-to-woocommerce-admin', VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_JS . 'admin-import-by-id.js', array( 'jquery' ), VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_VERSION );
				wp_localize_script( 's2w-import-shopify-to-woocommerce-admin', 's2w_params_admin_import_by_id', array(
					'url'        => admin_url( 'admin-ajax.php' ),
					'_s2w_nonce' => wp_create_nonce( 's2w_action_nonce' ),
				) );
			}
		}

		protected static function set( $name, $set_name = false ) {
			return VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::set( $name, $set_name );
		}

		public function plugins_loaded() {
			$this->process_single_new = new WP_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_Process_Single_New();
			if ( ! empty( $_REQUEST['s2w_cancel_download_image_single'] ) ) {
				$this->process_single_new->kill_process();
				wp_safe_redirect( @remove_query_arg( 's2w_cancel_download_image_single' ) );
				exit;
			}
		}

		public function admin_menu() {
			$menu_slug = 's2w-import-shopify-to-woocommerce-import-by-id';
			add_submenu_page(
				's2w-import-shopify-to-woocommerce',
				esc_html__( 'Import by ID', 's2w-import-shopify-to-woocommerce' ),
				esc_html__( 'Import by ID', 's2w-import-shopify-to-woocommerce' ),
				apply_filters( 'vi_s2w_admin_sub_menu_capability', 'manage_woocommerce', $menu_slug ),
				$menu_slug,
				array( $this, 'page_callback_import_by_id' )
			);
		}

		public function page_callback_import_by_id() {
			?>
            <div class="wrap">
                <h2><?php esc_html_e( 'Import products by ID', 's2w-import-shopify-to-woocommerce' ) ?></h2>
                <div class="vi-ui form">
                    <div class="vi-ui segment">
                        <input type="text" id="<?php echo esc_attr( self::set( 'shopify_product_id' ) ) ?>">
                        <p><?php esc_html_e( 'Enter ids of Shopify products separated by "," to import.', 's2w-import-shopify-to-woocommerce' ) ?></p>
                        <p>
                            <span class="vi-ui labeled icon positive button tiny <?php echo esc_attr( self::set( 'button-import' ) ) ?>"><i
                                        class="icon cloud download"></i><?php esc_html_e( 'Import', 's2w-import-shopify-to-woocommerce' ) ?></span>
                        </p>
                    </div>
                    <div class="vi-ui segment <?php echo esc_attr( self::set( 'import-message' ) ) ?>">
                    </div>
                </div>
            </div>
			<?php
		}

		public function import_by_id() {
			check_ajax_referer( 's2w_action_nonce', '_s2w_nonce' );
			if ( ! current_user_can( apply_filters( 'vi_s2w_admin_sub_menu_capability', 'manage_woocommerce', 's2w-import-shopify-to-woocommerce-import-by-id' ) ) ) {
				wp_die();
			}
			$products_to_import = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
			$product_ids        = array();
			if ( $products_to_import ) {
				$product_ids = explode( ',', $products_to_import );
			}
			$product_ids                 = array_map( 'floatval', $product_ids );
			$product_ids                 = array_filter( $product_ids );
			$domain                      = $this->settings->get_params( 'domain' );
			$access_token                = $this->settings->get_params( 'access_token' );
			$api_key                     = $this->settings->get_params( 'api_key' );
			$api_secret                  = $this->settings->get_params( 'api_secret' );
			$download_images             = $this->settings->get_params( 'download_images' );
			$disable_background_process  = $this->settings->get_params( 'disable_background_process' );
			$download_description_images = $this->settings->get_params( 'download_description_images' );
			$keep_slug                   = $this->settings->get_params( 'keep_slug' );
			$product_status              = $this->settings->get_params( 'product_status' );
			$product_status_mapping      = $this->settings->get_params( 'product_status_mapping' );
			$product_categories          = $this->settings->get_params( 'product_categories' );
			$global_attributes           = $this->settings->get_params( 'global_attributes' );
			$variable_sku                = $this->settings->get_params( 'variable_sku' );
			$path                        = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::get_cache_path( $domain, $access_token, $api_key, $api_secret ) . '/';
			$log_file                    = $path . 'import_by_id_logs.txt';
			VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::create_cache_folder( $path );
			$placeholder_image_id = s2w_get_placeholder_image();
			$message              = '';
			if ( is_array( $product_ids ) && count( $product_ids ) ) {
				foreach ( $product_ids as $current_import_id ) {
					$log                 = array( 'shopify_id' => $current_import_id );
					$imported_product_id = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::product_get_woo_id_by_shopify_id( $current_import_id );
					if ( $imported_product_id ) {
						$message            .= '<p>' . esc_html__( 'Product exists ', 's2w-import-shopify-to-woocommerce' ) . '<strong>' . $current_import_id . '</strong><a target="_blank" href="' . esc_url( admin_url( 'post.php?post=' . $imported_product_id . '&action=edit' ) ) . '">' . esc_html__( ' View', 's2w-import-shopify-to-woocommerce' ) . '</a></p>';
						$log['woo_id']      = $imported_product_id;
						$log['message']     = esc_html__( 'Skip because product exists', 's2w-import-shopify-to-woocommerce' );
						$log['title']       = get_the_title( $imported_product_id );
						$log['product_url'] = admin_url( 'post.php?post=' . $log['woo_id'] . '&action=edit' );
						$logs_content       = $log['title'] . ": " . $log['message'] . ", Shopify product ID: " . $log['shopify_id'] . ", WC product ID: " . $log['woo_id'];
						VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::log( $log_file, $logs_content );
						continue;
					}
					$request = VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::wp_remote_get( $domain, $access_token, $api_key, $api_secret, 'products', false, array( 'ids' => $current_import_id ) );
					if ( $request['status'] === 'success' ) {
						$product_data = $request['data'];
						$manage_stock = ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) ? true : false;
						if ( count( $product_data ) ) {
							if ( ! empty( $product_status_mapping[ $product_data['status'] ] ) ) {
								$product_status = $product_status_mapping[ $product_data['status'] ];
							}
							if ( $product_status === 'not_import' ) {
								continue;
							}
							$variants     = isset( $product_data['variants'] ) ? $product_data['variants'] : array();
							$vendor       = apply_filters( 's2w_import_product_vendor', isset( $product_data['vendor'] ) ? $product_data['vendor'] : '', $product_data );
							$product_type = apply_filters( 's2w_import_product_type', isset( $product_data['product_type'] ) ? $product_data['product_type'] : '', $product_data );
							$sku          = str_replace( array(
								'{shopify_product_id}',
								'{product_slug}'
							), array( $current_import_id, $product_data['handle'] ), $variable_sku );
							$sku          = str_replace( ' ', '', $sku );
							$attr_data    = array();
							$options      = isset( $product_data['options'] ) ? $product_data['options'] : array();

							if ( VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::sku_exists( $sku ) ) {
								$sku = '';
							}
							if ( $download_images ) {
								if ( is_array( $options ) && count( $options ) ) {
									if ( count( $options ) == 1 && count( $options[0]['values'] ) == 1 ) {
										$regular_price = $variants[0]['compare_at_price'];
										$sale_price    = $variants[0]['price'];
										if ( ! floatval( $regular_price ) || floatval( $regular_price ) == floatval( $sale_price ) ) {
											$regular_price = $sale_price;
											$sale_price    = '';
										}
										$description = isset( $product_data['body_html'] ) ? html_entity_decode( $product_data['body_html'], ENT_QUOTES | ENT_XML1, 'UTF-8' ) : '';
										$simple_sku  = apply_filters( 's2w_simple_product_sku', $variants[0]['sku'], $current_import_id, $product_data['handle'] );
										$barcode     = apply_filters( 's2w_import_product_barcode', isset( $variants[0]['barcode'] ) ? $variants[0]['barcode'] : '', $variants[0] );
										if ( $options[0]['name'] !== 'Title' && $options[0]['values'][0] !== 'Default Title' ) {
											if ( $global_attributes ) {
												S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_product_global_attribute( $options[0], $attr_data );
											} else {
												S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_product_custom_attribute( $options[0], $attr_data );
											}
										}
										$data = array( // Set up the basic post data to insert for our product
											'post_type'    => 'product',
											'post_excerpt' => '',
											'post_content' => $description,
											'post_title'   => isset( $product_data['title'] ) ? $product_data['title'] : '',
											'post_status'  => $product_status,
											'post_parent'  => '',
											'meta_input'   => array(
												'_sku'                => VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::sku_exists( $simple_sku ) ? '' : $simple_sku,
												'_visibility'         => 'visible',
												'_shopify_product_id' => $current_import_id,
												'_regular_price'      => $regular_price,
												'_price'              => $regular_price,
											)
										);
										if ( $keep_slug && $product_data['handle'] ) {
											$data['post_name'] = $product_data['handle'];
										}
										if ( $variants[0]['weight'] ) {
											$data['meta_input']['_weight'] = $variants[0]['weight'];
										}

										if ( $sale_price ) {
											$data['meta_input']['_sale_price'] = $sale_price;
											$data['meta_input']['_price']      = $sale_price;
										}
										$product_id = wp_insert_post( $data );
										if ( ! is_wp_error( $product_id ) ) {
											$dispatch = false;
											if ( $description && $download_description_images ) {
												preg_match_all( '/src="([\s\S]*?)"/im', preg_replace( '/<script\>[\s\S]*?<\/script>/im', '', $description ), $matches );
												if ( isset( $matches[1] ) && is_array( $matches[1] ) && count( $matches[1] ) ) {
													$description_images = array_unique( $matches[1] );
													if ( $disable_background_process ) {
														foreach ( $description_images as $description_image ) {
															S2W_Error_Images_Table::insert( $product_id, '', $description_image, '', 2, '' );
														}
													} else {
														$dispatch = true;
														foreach ( $description_images as $description_image ) {
															$images_data = array(
																'id'          => '',
																'src'         => $description_image,
																'alt'         => '',
																'parent_id'   => $product_id,
																'product_ids' => array(),
																'set_gallery' => 2,
															);
															$this->process_single_new->push_to_queue( $images_data );
														}
													}
												}
											}
											$log['woo_id'] = $product_id;
											$images_d      = array();
											$images        = isset( $product_data['images'] ) ? $product_data['images'] : array();
											if ( count( $images ) ) {
												foreach ( $images as $image ) {
													$images_d[] = array(
														'id'          => $image['id'],
														'src'         => $image['src'],
														'alt'         => $image['alt'],
														'parent_id'   => $product_id,
														'product_ids' => array(),
														'set_gallery' => 1,
													);
												}
												$images_d[0]['product_ids'][] = $product_id;
												$images_d[0]['set_gallery']   = 0;
												if ( $placeholder_image_id ) {
													update_post_meta( $product_id, '_thumbnail_id', $placeholder_image_id );
												}
											}
											wp_set_object_terms( $product_id, 'simple', 'product_type' );
											if ( ! empty( $product_data['product_type'] ) ) {
												wp_set_object_terms( $product_id, $product_data['product_type'], 'product_cat', true );
											}
											if ( is_array( $product_categories ) && count( $product_categories ) ) {
												wp_set_post_terms( $product_id, $product_categories, 'product_cat', true );
											}

											$tags = isset( $product_data['tags'] ) ? $product_data['tags'] : '';
											if ( $tags ) {
												wp_set_object_terms( $product_id, explode( ',', $product_data['tags'] ), 'product_tag' );
											}
											S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::import_product_vendor( $product_id, $vendor );
											S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::import_product_type( $product_id, $product_type );
											S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::import_product_barcode( $product_id, $barcode );
											$product_obj = wc_get_product( $product_id );
											if ( $product_obj ) {
												if ( count( $attr_data ) ) {
													$product_obj->set_attributes( $attr_data );
												}
												if ( $manage_stock ) {
													$product_obj->set_manage_stock( 'yes' );
													$product_obj->set_stock_quantity( $variants[0]['inventory_quantity'] );
													if ( $variants[0]['inventory_quantity'] ) {
														$product_obj->set_stock_status( 'instock' );
													} else {
														$product_obj->set_stock_status( 'outofstock' );
													}
													if ( $variants[0]['inventory_policy'] === 'continue' ) {
														$product_obj->set_backorders( 'yes' );
													} else {
														$product_obj->set_backorders( 'no' );
													}
												} else {
													$product_obj->set_manage_stock( 'no' );
													$product_obj->set_stock_status( 'instock' );
												}
												$product_obj->save();
											}
											if ( count( $images_d ) ) {
												if ( $disable_background_process ) {
													foreach ( $images_d as $images_d_k => $images_d_v ) {
														S2W_Error_Images_Table::insert( $product_id, implode( ',', $images_d_v['product_ids'] ), $images_d_v['src'], $images_d_v['alt'], intval( $images_d_v['set_gallery'] ), $images_d_v['id'] );
													}
												} else {
													$dispatch = true;
													foreach ( $images_d as $images_d_k => $images_d_v ) {
														$this->process_single_new->push_to_queue( $images_d_v );
													}
												}
											}
											if ( $dispatch ) {
												$this->process_single_new->save()->dispatch();
											}
											$history['last_product_error'] = '';
											$message                       .= '<p>' . esc_html__( 'Successfully import ', 's2w-import-shopify-to-woocommerce' ) . '<strong>' . $current_import_id . '</strong>: <a target="_blank" href="' . esc_url( admin_url( 'post.php?post=' . $product_id . '&action=edit' ) ) . '">' . esc_html__( ' View', 's2w-import-shopify-to-woocommerce' ) . '</a></p>';
											$log['woo_id']                 = $product_id;
											$log['message']                = esc_html__( 'Import successfully', 's2w-import-shopify-to-woocommerce' );
											$log['title']                  = $product_data['title'];
											$log['product_url']            = admin_url( 'post.php?post=' . $log['woo_id'] . '&action=edit' );
											$logs_content                  = $log['title'] . ": " . $log['message'] . ", Shopify product ID: " . $log['shopify_id'] . ", WC product ID: " . $log['woo_id'];
											VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::log( $log_file, $logs_content );
											do_action( 's2w_bulk_import_item_successfully', $product_id, 'import_by_id', $product_data );
										}
									} else {
										if ( $global_attributes ) {
											foreach ( $options as $option_k => $option_v ) {
												S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_product_global_attribute( $option_v, $attr_data );
											}
										} else {
											foreach ( $options as $option_k => $option_v ) {
												S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_product_custom_attribute( $option_v, $attr_data );
											}
										}
										$description = isset( $product_data['body_html'] ) ? html_entity_decode( $product_data['body_html'], ENT_QUOTES | ENT_XML1, 'UTF-8' ) : '';
										$data        = array( // Set up the basic post data to insert for our product
											'post_type'    => 'product',
											'post_excerpt' => '',
											'post_content' => $description,
											'post_title'   => isset( $product_data['title'] ) ? $product_data['title'] : '',
											'post_status'  => $product_status,
											'post_parent'  => '',
											'meta_input'   => array(
												'_sku'                => $sku,
												'_visibility'         => 'visible',
												'_shopify_product_id' => $current_import_id,
												'_manage_stock'       => 'no',
											)
										);
										if ( $keep_slug && $product_data['handle'] ) {
											$data['post_name'] = $product_data['handle'];
										}
										$product_id = wp_insert_post( $data );
										if ( ! is_wp_error( $product_id ) ) {
											$dispatch = false;
											if ( $description && $download_description_images ) {

												preg_match_all( '/src="([\s\S]*?)"/im', preg_replace( '/<script\>[\s\S]*?<\/script>/im', '', $description ), $matches );
												if ( isset( $matches[1] ) && is_array( $matches[1] ) && count( $matches[1] ) ) {
													$description_images = array_unique( $matches[1] );
													if ( $disable_background_process ) {
														foreach ( $description_images as $description_image ) {
															S2W_Error_Images_Table::insert( $product_id, '', $description_image, '', 2, '' );
														}
													} else {
														$dispatch = true;
														foreach ( $description_images as $description_image ) {
															$images_data = array(
																'id'          => '',
																'src'         => $description_image,
																'alt'         => '',
																'parent_id'   => $product_id,
																'product_ids' => array(),
																'set_gallery' => 2,
															);
															$this->process_single_new->push_to_queue( $images_data );
														}
													}
												}
											}
											wp_set_object_terms( $product_id, 'variable', 'product_type' );
											if ( count( $attr_data ) ) {
												$product_obj = wc_get_product( $product_id );
												if ( $product_obj ) {
													$product_obj->set_attributes( $attr_data );
													$product_obj->save();
													wp_set_object_terms( $product_id, 'variable', 'product_type' );
												}
											}
											$log['woo_id'] = $product_id;
											$images_d      = array();
											$images        = isset( $product_data['images'] ) ? $product_data['images'] : array();
											if ( count( $images ) ) {
												foreach ( $images as $image ) {
													$images_d[] = array(
														'id'          => $image['id'],
														'src'         => $image['src'],
														'alt'         => $image['alt'],
														'parent_id'   => $product_id,
														'product_ids' => array(),
														'set_gallery' => 1,
													);
												}
												$images_d[0]['product_ids'][] = $product_id;
												$images_d[0]['set_gallery']   = 0;
												if ( $placeholder_image_id ) {
													update_post_meta( $product_id, '_thumbnail_id', $placeholder_image_id );
												}
											}

											if ( ! empty( $product_data['product_type'] ) ) {
												wp_set_object_terms( $product_id, $product_data['product_type'], 'product_cat', true );
											}
											if ( is_array( $product_categories ) && count( $product_categories ) ) {
												wp_set_post_terms( $product_id, $product_categories, 'product_cat', true );
											}
											$tags = isset( $product_data['tags'] ) ? $product_data['tags'] : '';
											if ( $tags ) {
												wp_set_object_terms( $product_id, explode( ',', $product_data['tags'] ), 'product_tag' );
											}
											S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::import_product_vendor( $product_id, $vendor );
											S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::import_product_type( $product_id, $product_type );
											if ( is_array( $variants ) && count( $variants ) ) {
												foreach ( $variants as $variant ) {
													$regular_price = $variant['compare_at_price'];
													$sale_price    = $variant['price'];
													if ( ! floatval( $regular_price ) || floatval( $regular_price ) == floatval( $sale_price ) ) {
														$regular_price = $sale_price;
														$sale_price    = '';
													}
													$variation_obj = new WC_Product_Variation();
													$variation_obj->set_parent_id( $product_id );
													$attributes = array();
													if ( $global_attributes ) {
														foreach ( $options as $option_k => $option_v ) {
															$j = $option_k + 1;
															if ( isset( $variant[ 'option' . $j ] ) && $variant[ 'option' . $j ] ) {
																$attribute_id  = wc_attribute_taxonomy_id_by_name( $option_v['name'] );
																$attribute_obj = wc_get_attribute( $attribute_id );
																if ( $attribute_obj ) {
																	$attribute_value = get_term_by( 'name', $variant[ 'option' . $j ], $attribute_obj->slug );
																	if ( $attribute_value ) {
																		$attributes[ VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::sanitize_taxonomy_name( $attribute_obj->slug ) ] = $attribute_value->slug;
																	}
																}
															}
														}
													} else {
														foreach ( $options as $option_k => $option_v ) {
															$j = $option_k + 1;
															if ( isset( $variant[ 'option' . $j ] ) && $variant[ 'option' . $j ] ) {
																$attributes[ VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::sanitize_taxonomy_name( $option_v['name'] ) ] = $variant[ 'option' . $j ];
															}
														}
													}
													$variation_obj->set_attributes( $attributes );
													$fields = array(
														'sku'           => VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::sku_exists( $variant['sku'] ) ? '' : $variant['sku'],
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
														$fields['weight'] = $variant['weight'];
													}
													if ( $sale_price ) {
														$fields['sale_price'] = $sale_price;
													}
													foreach ( $fields as $field => $field_v ) {
														$variation_obj->{"set_$field"}( wc_clean( $field_v ) );
													}
													do_action( 'product_variation_linked', $variation_obj->save() );
													$variation_obj_id = $variation_obj->get_id();
													if ( count( $images ) ) {
														foreach ( $images as $image_k => $image_v ) {
															if ( in_array( $variant['id'], $image_v['variant_ids'] ) ) {
																$images_d[ $image_k ]['product_ids'][] = $variation_obj_id;
																if ( $placeholder_image_id ) {
																	update_post_meta( $variation_obj_id, '_thumbnail_id', $placeholder_image_id );
																}
															}
														}
													}
													update_post_meta( $variation_obj_id, '_shopify_variation_id', $variant['id'] );
													$barcode = apply_filters( 's2w_import_product_barcode', isset( $variant['barcode'] ) ? $variant['barcode'] : '', $variant );
													S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::import_product_barcode( $variation_obj_id, $barcode );
												}
											}
											if ( count( $images_d ) ) {
												if ( $disable_background_process ) {
													foreach ( $images_d as $images_d_k => $images_d_v ) {
														S2W_Error_Images_Table::insert( $product_id, implode( ',', $images_d_v['product_ids'] ), $images_d_v['src'], $images_d_v['alt'], intval( $images_d_v['set_gallery'] ), $images_d_v['id'] );
													}
												} else {
													$dispatch = true;
													foreach ( $images_d as $images_d_k => $images_d_v ) {
														$this->process_single_new->push_to_queue( $images_d_v );
													}
												}
											}
											if ( $dispatch ) {
												$this->process_single_new->save()->dispatch();
											}
											$history['last_product_error'] = '';
											$message                       .= '<p>' . esc_html__( 'Successfully import ', 's2w-import-shopify-to-woocommerce' ) . '<strong>' . $current_import_id . '</strong>: <a target="_blank" href="' . esc_url( admin_url( 'post.php?post=' . $product_id . '&action=edit' ) ) . '">' . esc_html__( ' View', 's2w-import-shopify-to-woocommerce' ) . '</a></p>';
											$log['woo_id']                 = $product_id;
											$log['message']                = esc_html__( 'Import successfully', 's2w-import-shopify-to-woocommerce' );
											$log['title']                  = $product_data['title'];
											$log['product_url']            = admin_url( 'post.php?post=' . $log['woo_id'] . '&action=edit' );
											$logs_content                  = $log['title'] . ": " . $log['message'] . ", Shopify product ID: " . $log['shopify_id'] . ", WC product ID: " . $log['woo_id'];
											VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::log( $log_file, $logs_content );
											do_action( 's2w_bulk_import_item_successfully', $product_id, 'import_by_id', $product_data );
										}
									}
								}
							} else {
								if ( is_array( $options ) && count( $options ) ) {
									if ( count( $options ) == 1 && count( $options[0]['values'] ) == 1 ) {
										$regular_price = $variants[0]['compare_at_price'];
										$sale_price    = $variants[0]['price'];
										if ( ! floatval( $regular_price ) || floatval( $regular_price ) == floatval( $sale_price ) ) {
											$regular_price = $sale_price;
											$sale_price    = '';
										}
										$description = isset( $product_data['body_html'] ) ? html_entity_decode( $product_data['body_html'], ENT_QUOTES | ENT_XML1, 'UTF-8' ) : '';
										$simple_sku  = apply_filters( 's2w_simple_product_sku', $variants[0]['sku'], $current_import_id, $product_data['handle'] );
										$barcode     = apply_filters( 's2w_import_product_barcode', isset( $variants[0]['barcode'] ) ? $variants[0]['barcode'] : '', $variants[0] );
										if ( $options[0]['name'] !== 'Title' && $options[0]['values'][0] !== 'Default Title' ) {
											if ( $global_attributes ) {
												S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_product_global_attribute( $options[0], $attr_data );
											} else {
												S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_product_custom_attribute( $options[0], $attr_data );
											}
										}
										$data = array( // Set up the basic post data to insert for our product
											'post_type'    => 'product',
											'post_excerpt' => '',
											'post_content' => $description,
											'post_title'   => isset( $product_data['title'] ) ? $product_data['title'] : '',
											'post_status'  => $product_status,
											'post_parent'  => '',

											'meta_input' => array(
												'_sku'                => VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::sku_exists( $simple_sku ) ? '' : $simple_sku,
												'_visibility'         => 'visible',
												'_shopify_product_id' => $current_import_id,
												'_regular_price'      => $regular_price,
												'_price'              => $regular_price,
											)
										);
										if ( $keep_slug && $product_data['handle'] ) {
											$data['post_name'] = $product_data['handle'];
										}
										if ( $variants[0]['weight'] ) {
											$data['meta_input']['_weight'] = $variants[0]['weight'];
										}

										if ( $sale_price ) {
											$data['meta_input']['_sale_price'] = $sale_price;
											$data['meta_input']['_price']      = $sale_price;
										}
										$product_id = wp_insert_post( $data );
										if ( ! is_wp_error( $product_id ) ) {
											if ( $description && $download_description_images ) {
												preg_match_all( '/src="([\s\S]*?)"/im', preg_replace( '/<script\>[\s\S]*?<\/script>/im', '', $description ), $matches );
												if ( isset( $matches[1] ) && is_array( $matches[1] ) && count( $matches[1] ) ) {
													$description_images = array_unique( $matches[1] );
													if ( $disable_background_process ) {
														foreach ( $description_images as $description_image ) {
															S2W_Error_Images_Table::insert( $product_id, '', $description_image, '', 2, '' );
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
															$this->process_single_new->push_to_queue( $images_data );
														}
														$this->process_single_new->save()->dispatch();
													}
												}
											}
											$log['woo_id'] = $product_id;
											wp_set_object_terms( $product_id, 'simple', 'product_type' );
											if ( ! empty( $product_data['product_type'] ) ) {
												wp_set_object_terms( $product_id, $product_data['product_type'], 'product_cat', true );
											}
											if ( is_array( $product_categories ) && count( $product_categories ) ) {
												wp_set_post_terms( $product_id, $product_categories, 'product_cat', true );
											}
											$tags = isset( $product_data['tags'] ) ? $product_data['tags'] : '';
											if ( $tags ) {
												wp_set_object_terms( $product_id, explode( ',', $product_data['tags'] ), 'product_tag' );
											}
											S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::import_product_vendor( $product_id, $vendor );
											S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::import_product_type( $product_id, $product_type );
											S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::import_product_barcode( $product_id, $barcode );
											$product_obj = wc_get_product( $product_id );
											if ( $product_obj ) {
												if ( $manage_stock ) {
													$product_obj->set_manage_stock( 'yes' );
													$product_obj->set_stock_quantity( $variants[0]['inventory_quantity'] );
													if ( $variants[0]['inventory_quantity'] ) {
														$product_obj->set_stock_status( 'instock' );
													} else {
														$product_obj->set_stock_status( 'outofstock' );
													}
													if ( $variants[0]['inventory_policy'] === 'continue' ) {
														$product_obj->set_backorders( 'yes' );
													} else {
														$product_obj->set_backorders( 'no' );
													}
												} else {
													$product_obj->set_manage_stock( 'no' );
													$product_obj->set_stock_status( 'instock' );
												}
												$product_obj->save();
											}
											$message            .= '<p>' . esc_html__( 'Successfully import ', 's2w-import-shopify-to-woocommerce' ) . '<strong>' . $current_import_id . '</strong>: <a target="_blank" href="' . esc_url( admin_url( 'post.php?post=' . $product_id . '&action=edit' ) ) . '">' . esc_html__( ' View', 's2w-import-shopify-to-woocommerce' ) . '</a></p>';
											$log['woo_id']      = $product_id;
											$log['message']     = esc_html__( 'Import successfully', 's2w-import-shopify-to-woocommerce' );
											$log['title']       = $product_data['title'];
											$log['product_url'] = admin_url( 'post.php?post=' . $log['woo_id'] . '&action=edit' );
											$logs_content       = $log['title'] . ": " . $log['message'] . ", Shopify product ID: " . $log['shopify_id'] . ", WC product ID: " . $log['woo_id'];
											VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::log( $log_file, $logs_content );
											do_action( 's2w_bulk_import_item_successfully', $product_id, 'import_by_id', $product_data );
										}
									} else {
										if ( $global_attributes ) {
											foreach ( $options as $option_k => $option_v ) {
												S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_product_global_attribute( $option_v, $attr_data );
											}
										} else {
											foreach ( $options as $option_k => $option_v ) {
												S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::create_product_custom_attribute( $option_v, $attr_data );
											}
										}
										$description = isset( $product_data['body_html'] ) ? html_entity_decode( $product_data['body_html'], ENT_QUOTES | ENT_XML1, 'UTF-8' ) : '';
										$data        = array( // Set up the basic post data to insert for our product
											'post_type'    => 'product',
											'post_excerpt' => '',
											'post_content' => $description,
											'post_title'   => isset( $product_data['title'] ) ? $product_data['title'] : '',
											'post_status'  => $product_status,
											'post_parent'  => '',

											'meta_input' => array(
												'_sku'                => $sku,
												'_visibility'         => 'visible',
												'_shopify_product_id' => $current_import_id,
												'_manage_stock'       => 'no',
											)
										);
										if ( $keep_slug && $product_data['handle'] ) {
											$data['post_name'] = $product_data['handle'];
										}
										$product_id = wp_insert_post( $data );
										if ( ! is_wp_error( $product_id ) ) {
											if ( $description && $download_description_images ) {
												preg_match_all( '/src="([\s\S]*?)"/im', preg_replace( '/<script\>[\s\S]*?<\/script>/im', '', $description ), $matches );
												if ( isset( $matches[1] ) && is_array( $matches[1] ) && count( $matches[1] ) ) {
													$description_images = array_unique( $matches[1] );
													if ( $disable_background_process ) {
														foreach ( $description_images as $description_image ) {
															S2W_Error_Images_Table::insert( $product_id, '', $description_image, '', 2, '' );
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
															$this->process_single_new->push_to_queue( $images_data );
														}
														$this->process_single_new->save()->dispatch();
													}
												}
											}
											wp_set_object_terms( $product_id, 'variable', 'product_type' );
											if ( count( $attr_data ) ) {
												$product_obj = wc_get_product( $product_id );
												if ( $product_obj ) {
													$product_obj->set_attributes( $attr_data );
													$product_obj->save();
													wp_set_object_terms( $product_id, 'variable', 'product_type' );
												}
											}
											$log['woo_id'] = $product_id;
											if ( ! empty( $product_data['product_type'] ) ) {
												wp_set_object_terms( $product_id, $product_data['product_type'], 'product_cat', true );
											}
											if ( is_array( $product_categories ) && count( $product_categories ) ) {
												wp_set_post_terms( $product_id, $product_categories, 'product_cat', true );
											}
											$tags = isset( $product_data['tags'] ) ? $product_data['tags'] : '';
											if ( $tags ) {
												wp_set_object_terms( $product_id, explode( ',', $product_data['tags'] ), 'product_tag' );
											}
											S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::import_product_vendor( $product_id, $vendor );
											S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::import_product_type( $product_id, $product_type );
											if ( is_array( $variants ) && count( $variants ) ) {
												foreach ( $variants as $variant ) {
													$regular_price = $variant['compare_at_price'];
													$sale_price    = $variant['price'];
													if ( ! floatval( $regular_price ) || floatval( $regular_price ) == floatval( $sale_price ) ) {
														$regular_price = $sale_price;
														$sale_price    = '';
													}
													$variation_obj = new WC_Product_Variation();
													$variation_obj->set_parent_id( $product_id );
													$attributes = array();
													if ( $global_attributes ) {
														foreach ( $options as $option_k => $option_v ) {
															$j = $option_k + 1;
															if ( isset( $variant[ 'option' . $j ] ) && $variant[ 'option' . $j ] ) {
																$attribute_id  = wc_attribute_taxonomy_id_by_name( $option_v['name'] );
																$attribute_obj = wc_get_attribute( $attribute_id );
																if ( $attribute_obj ) {
																	$attribute_value = get_term_by( 'name', $variant[ 'option' . $j ], $attribute_obj->slug );
																	if ( $attribute_value ) {
																		$attributes[ VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::sanitize_taxonomy_name( $attribute_obj->slug ) ] = $attribute_value->slug;
																	}
																}
															}
														}
													} else {
														foreach ( $options as $option_k => $option_v ) {
															$j = $option_k + 1;
															if ( isset( $variant[ 'option' . $j ] ) && $variant[ 'option' . $j ] ) {
																$attributes[ VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::sanitize_taxonomy_name( $option_v['name'] ) ] = $variant[ 'option' . $j ];
															}
														}
													}
													$variation_obj->set_attributes( $attributes );
													$fields = array(
														'sku'           => VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::sku_exists( $variant['sku'] ) ? '' : $variant['sku'],
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
														$fields['weight'] = $variant['weight'];
													}
													if ( $sale_price ) {
														$fields['sale_price'] = $sale_price;
													}
													foreach ( $fields as $field => $field_v ) {
														$variation_obj->{"set_$field"}( wc_clean( $field_v ) );
													}
													do_action( 'product_variation_linked', $variation_obj->save() );
													update_post_meta( $variation_obj->get_id(), '_shopify_variation_id', $variant['id'] );
													$barcode = apply_filters( 's2w_import_product_barcode', isset( $variant['barcode'] ) ? $variant['barcode'] : '', $variant );
													S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE::import_product_barcode( $variation_obj->get_id(), $barcode );
												}
											}
											$message            .= '<p>' . esc_html__( 'Successfully import ', 's2w-import-shopify-to-woocommerce' ) . '<strong>' . $current_import_id . '</strong>: <a target="_blank" href="' . esc_url( admin_url( 'post.php?post=' . $product_id . '&action=edit' ) ) . '">' . esc_html__( ' View', 's2w-import-shopify-to-woocommerce' ) . '</a></p>';
											$log['woo_id']      = $product_id;
											$log['message']     = esc_html__( 'Import successfully', 's2w-import-shopify-to-woocommerce' );
											$log['title']       = $product_data['title'];
											$log['product_url'] = admin_url( 'post.php?post=' . $log['woo_id'] . '&action=edit' );
											$logs_content       = $log['title'] . ": " . $log['message'] . ", Shopify product ID: " . $log['shopify_id'] . ", WC product ID: " . $log['woo_id'];
											VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::log( $log_file, $logs_content );
											do_action( 's2w_bulk_import_item_successfully', $product_id, 'import_by_id', $product_data );
										}
									}
								}
							}

						} else {
							$message            .= '<p>No data<strong> ' . $current_import_id . '</strong></p>';
							$log['woo_id']      = '';
							$log['message']     = 'No data';
							$log['title']       = '';
							$log['product_url'] = '';
							$logs_content       = "Error: " . $log['message'] . ", Shopify product ID: " . $log['shopify_id'];
							VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::log( $log_file, $logs_content );
							continue;
						}
					} else {
						$message            .= '<p>' . $request['data'] . ' <strong>' . $current_import_id . '</strong></p>';
						$log['woo_id']      = '';
						$log['message']     = $request['data'];
						$log['title']       = '';
						$log['product_url'] = '';
						$logs_content       = "Error: " . $log['message'] . ", Shopify product ID: " . $log['shopify_id'];
						VI_S2W_IMPORT_SHOPIFY_TO_WOOCOMMERCE_DATA::log( $log_file, $logs_content );
						continue;
					}
				}
				wp_send_json( array(
					'status'  => 'success',
					'message' => $message,
				) );
			} else {
				wp_send_json( array(
					'status'  => 'error',
					'message' => '<p>' . esc_html__( 'Please enter valid Shopify product id', 's2w-import-shopify-to-woocommerce' ) . '</p>',
				) );
			}

		}
	}
}
