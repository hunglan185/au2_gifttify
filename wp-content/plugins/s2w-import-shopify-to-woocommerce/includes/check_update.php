<?php
/**
 * VillaTheme_Plugin_Check_Update
 */

// no direct access allowed
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'VillaTheme_Plugin_Check_Update' ) ) {

	/**
	 * Class VillaTheme_Plugin_Check_Update
	 * 1.0.1
	 */
	class VillaTheme_Plugin_Check_Update {
		/**
		 * The plugin current version
		 * @var string
		 */
		public $current_version;

		/**
		 * The plugin remote update path
		 * @var string
		 */
		public $update_path;

		/**
		 * Plugin Slug (plugin_directory/plugin_file.php)
		 * @var string
		 */
		public $plugin_slug;

		/**
		 * Plugin name (plugin_file)
		 * @var string
		 */
		public $slug;

		/**
		 * The item name while requesting to update api
		 * @var string
		 */
		public $request_name;

		/**
		 * The item ID in marketplace
		 * @var string
		 */
		public $plugin_id;


		/**
		 * The item name while requesting to update api
		 * @var string
		 */
		public $plugin_file_path;

		/**
		 * The item name while requesting to update api
		 * @var string
		 */
		public $banners;
		/**
		 * To verify on server
		 * @var
		 */
		public $key;
		/**
		 * This is product Id on VillaTheme
		 * @var
		 */
		public $item_id;

		/**
		 * Initialize a new instance of the WordPress Auto-Update class
		 *
		 * @param string $current_version
		 * @param string $update_path
		 * @param string $plugin_slug
		 * @param string $slug
		 */
		function __construct( $current_version, $update_path, $plugin_slug, $slug, $item_id, $key = '', $setting_url = '' ) {

			// Set the class public variables
			$this->current_version = $current_version;
			$this->update_path     = $update_path;
			$this->plugin_slug     = $plugin_slug;
			$this->slug            = $slug;
			$this->key             = $key;
			$this->item_id         = $item_id;
			$this->setting_url     = $setting_url;

			//Action hide notices
			if ( isset( $_GET[ $this->slug . '_dismiss_notices' ] ) && $_GET[ $this->slug . '_dismiss_notices' ] ) {
				update_option( $this->slug . '_' . $this->current_version . '_dismiss_notices', current_time( 'timestamp', true ) );
			}
			if ( isset( $_GET[ $this->slug . '_hide' ] ) && $_GET[ $this->slug . '_hide' ] ) {
				set_transient( $this->slug . '_hide', 1, 2592000 );
			}
			// define the alternative API for checking for updates
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );

			// Define the alternative response for information checking
			add_filter( 'plugins_api', array( $this, 'check_info' ), 10, 3 );
			if ( ! get_option( $this->slug . '_' . $this->current_version . '_dismiss_notices' ) ) {
				if ( ! get_transient( $this->slug . '_hide' ) ) {
					if ( ! $this->key ) {
						add_action( 'admin_notices', array( $this, 'update_key_notice' ) );
					} else {
						$this->messages = get_option( $this->slug . '_messages', array() );
						if ( is_array( $this->messages ) && count( $this->messages ) ) {
							if ( isset( $this->messages['update'] ) && $this->messages['update'] == 2 ) {
								add_action( 'admin_notices', array( $this, 'renew_key_notice' ) );
							}
						}
					}
				}
			}
			add_action( $this->slug . '_key', array( $this, 'key_active' ) );
		}


		/**
		 * Show key is active
		 */
		public function key_active() {
			$has_message = false;
			if ( isset( $this->messages ) ) {
				if ( count( $this->messages ) ) {
					if ( isset( $this->messages['update'] ) ) {
						$has_message = true;
						?>
                        <p class="villatheme-key-active"><?php echo wp_kses_post( $this->messages['message'] ) ?></p>
						<?php
					}
				}
			}
			if ( ! $has_message ) {
				?>
                <p class="villatheme-key-message"><?php echo wp_kses_post( '<strong>*Important:</strong> Please only activate your license if this is the final live site you want to use it on because once activated, your license cannot be moved to another site except for <a target="_blank" href="https://villatheme.com/knowledge-base/plugin-license-and-key-removal-on-a-domain/">these cases</a>' ) ?></p>
				<?php
			}
		}

		/**
		 * Show update key wordpress
		 */
		public function renew_key_notice() {
			$plugin = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->plugin_slug );
			?>
            <div class="villatheme-dashboard notice notice-warning">
                <div class="villatheme-content">
                    <p><?php echo esc_html( 'Hello! Your ' ) . '<strong>' . $plugin['Name'] . '</strong>' . esc_html( ' is expired automatic update and support. Please go to renew now.' ) ?>
                        <a class="button button-primary" target="_blank"
                           href="https://codecanyon.net/downloads"><?php echo esc_html( 'Renew' ) ?></a>
                        <a href="<?php echo esc_url( add_query_arg( array( $this->slug . '_hide' => 1 ) ) ) ?>"
                           class="button"><?php echo esc_html( 'Skip' ) ?></a>
                    </p>
                    <a target="_self"
                       href="<?php echo esc_url( add_query_arg( array( $this->slug . '_dismiss_notices' => 1 ) ) ); ?>"
                       class="button notice-dismiss vi-button-dismiss"><?php echo esc_html( 'Dismiss' ) ?></a>
                </div>

            </div>
		<?php }

		/**
		 * Show update key wordpress
		 */
		public function update_key_notice() {
			$plugin = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->plugin_slug );
			?>
            <div class="villatheme-dashboard error">
                <div class="villatheme-content">
                    <p><?php echo esc_html( 'Hello! Would you like to receive automatic update? Please activate your copy of ' ) . '<strong>' . $plugin['Name'] . '</strong>' ?></p>
                    <p>
                        <a href="<?php echo esc_url( $this->setting_url ) ?>#update"
                           class="button button-primary"><?php echo esc_html( 'Activate' ) ?></a>
                        <a href="<?php echo esc_url( add_query_arg( array( $this->slug . '_hide' => 1 ) ) ) ?>"
                           class="button"><?php echo esc_html( 'Hide' ) ?></a>
                    </p>
                    <a target="_self"
                       href="<?php echo esc_url( add_query_arg( array( $this->slug . '_dismiss_notices' => 1 ) ) ); ?>"
                       class="button notice-dismiss vi-button-dismiss"><?php echo esc_html( 'Dismiss' ) ?></a>
                </div>

            </div>
		<?php }

		/**
		 * Add our self-hosted autoupdate plugin to the filter transient
		 *
		 * @param $transient
		 *
		 * @return object $ transient
		 */
		public function check_update( $transient ) {

			// Get the remote version
			$remote_version = $this->get_remote_version();

			if ( is_array( $remote_version ) ) {
				$version  = isset( $remote_version['version'] ) ? $remote_version['version'] : 0;
				$url      = isset( $remote_version['url'] ) ? $remote_version['url'] : '';
				$package  = isset( $remote_version['package'] ) ? $remote_version['package'] : '';
				$messages = isset( $remote_version['message'] ) ? $remote_version['message'] : array();
			} else {
				return $transient;
			}
			/*Update message*/
			if ( $messages ) {
				$messages = json_decode( $messages, true );
				update_option( $this->slug . '_messages', $messages );
			}
			// If a newer version is available, add the update info to update transient
			if ( version_compare( $this->current_version, $version, '<' ) ) {
				$obj                                       = new stdClass();
				$obj->slug                                 = $this->slug;
				$obj->plugin                               = $this->plugin_slug;
				$obj->new_version                          = $version;
				$obj->url                                  = $url;
				$obj->package                              = $package;
				$transient->response[ $this->plugin_slug ] = $obj;

			} elseif ( isset( $transient->response[ $this->plugin_slug ] ) ) {

				unset( $transient->response[ $this->plugin_slug ] );
			}


			return $transient;
		}


		/**
		 * Return the remote version
		 * @return string $remote_version
		 */
		public function get_remote_version() {
			global $wp_version;
			$result = get_transient( 'villatheme_item_' . $this->item_id );
			if ( $result ) {
				return $result;
			} else {
				$request = wp_remote_post( $this->update_path, array(
						'user-agent' => 'WordPress/' . $wp_version . '; ' . get_site_url(),
						'timeout'    => ( ( defined( 'DOING_CRON' ) && DOING_CRON ) ? 30 : 3 ),
						'body'       => array(
							'key' => $this->key,
							'id'  => $this->item_id
						)
					)
				);

				if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
					$request = json_decode( $request['body'], true );
					set_transient( 'villatheme_item_' . $this->item_id, $request, 86400 );

					return $request;

				}
			}

			return false;
		}


		/**
		 * Add our self-hosted description to the filter
		 *
		 * @param boolean $false
		 * @param array $action
		 * @param object $arg
		 *
		 * @return bool|object
		 */
		public function check_info( $false, $action, $arg ) {


			if ( ! isset( $arg->slug ) ) {
				return $false;
			}

			if ( $arg->slug === $this->slug ) {
				return true;
			}

			return $false;
		}

	}
}