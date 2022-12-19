<?php

namespace QuadLayers\WP_Notice_Plugin_Required;

/**
 * Class Load
 *
 * @package QuadLayers\WP_Notice_Plugin_Required
 */

class Load {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = array();

	/**
	 * Required Plugin slug.
	 *
	 * @var string
	 */
	protected $plugin_slug = '';

	/**
	 * Required Plugin name.
	 *
	 * @var string
	 */
	protected $plugin_name = '';

	/**
	 * Current Plugin name.
	 *
	 * @var string
	 */
	protected $current_plugin_name = '';

	private function __construct( array $plugin_data, string $current_plugin_name ) {
		$this->plugin_slug         = $plugin_data['slug'];
		$this->plugin_name         = $plugin_data['name'];
		$this->current_plugin_name = $current_plugin_name;
		add_action( 'admin_notices', array( $this, 'add_admin_notices' ) );
	}

	function add_admin_notices() {

		$screen = get_current_screen();

		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}

		if ( $this->is_plugin_activated() ) {
			return;
		}

		if ( $this->is_plugin_installed() ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			?>
			<div class="error">
				<p>
					<a href="<?php echo esc_url( $this->get_plugin_activate_link() ); ?>" class='button button-secondary'><?php printf( esc_html__( 'Activate %s', 'wp-notice-plugin-required' ), esc_html( $this->plugin_name ) ); ?></a>
					<?php printf( esc_html__( '%1$s not working because you need to activate the %2$s plugin.', 'wp-notice-plugin-required' ), esc_html( $this->current_plugin_name ), esc_html( $this->plugin_name ) ); ?>
				</p>
			</div>
			<?php
			return;
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}
		?>
		<div class="error">
			<p>
				<a href="<?php echo esc_url( $this->get_plugin_install_link() ); ?>" class='button button-secondary'><?php printf( esc_html__( 'Install %s', 'wp-notice-plugin-required' ), esc_html( $this->plugin_name ) ); ?></a>
				<?php printf( esc_html__( '%1$s not working because you need to install the %2$s plugin.', 'wp-notice-plugin-required' ), esc_html( $this->current_plugin_name ), esc_html( $this->plugin_name ) ); ?>
			</p>
		</div>
		<?php
	}

	public function is_plugin_installed() {
		$plugin_path       = $this->get_plugin_path();
		$installed_plugins = get_plugins();
		return isset( $installed_plugins[ $plugin_path ] );
	}

	public function is_plugin_activated() {
		$plugin_path = $this->get_plugin_path();
		return is_plugin_active( $plugin_path );
	}

	private function get_plugin_path() {
		return "{$this->plugin_slug}/{$this->plugin_slug}.php";
	}

	private function get_plugin_install_link() {
		return wp_nonce_url( self_admin_url( "update.php?action=install-plugin&plugin={$this->plugin_slug}" ), "install-plugin_{$this->plugin_slug}" );
	}

	private function get_plugin_activate_link() {
		$plugin_path = $this->get_plugin_path();
		return wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1', 'activate-plugin_' . $plugin_path );
	}

	public static function get_instance( array $plugin_data = array(), string $current_plugin_name = 'Current Plugin Name' ) {

		$plugin_slug = $plugin_data['slug'];

		if ( isset( self::$instance[ $plugin_slug ] ) ) {
			return self::$instance[ $plugin_slug ];
		}

		self::$instance[ $plugin_slug ] = new self( $plugin_data, $current_plugin_name );

		return self::$instance[ $plugin_slug ];
	}

}
