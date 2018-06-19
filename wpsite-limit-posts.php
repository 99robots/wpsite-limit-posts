<?php
/**
 * Plugin Name:		Limit Posts
 * Plugin URI:		http://99robots.com
 * Description:		Limit the number of posts or custom post types that can be published based on role (i.e, author) or user.
 * Version:			2.0.2
 * Author:			99 Robots
 * Author URI:		http://99robots.com
 * License:			GPL2
 * License URI:		http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:		wpsite-limit-posts
 * Domain Path:		/languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 *  WPsiteLimitPosts main class
 *
 * @since 1.0.0
 * @using Wordpress 3.9.1
 */
class WPsite_Limit_Posts {

	/**
	 * WPsite_Limit_Posts version.
	 * @var string
	 */
	public $version = '2.0.2';

	/**
	 * The single instance of the class.
	 * @var WPsite_Limit_Posts
	 */
	protected static $_instance = null;

	/**
	 * Plugin url.
	 * @var string
	 */
	private $plugin_url = null;

	/**
	 * Plugin path.
	 * @var string
	 */
	private $plugin_dir = null;

	/**
	 * Setting page id.
	 * @var string
	 */
	private static $settings_page = 'wpsite-limit-posts-admin-menu-settings';

	/**
	 * Default settings.
	 * @var array
	 */
	private static $default = array(
		'all'			=> 'capability',
		'all_limit'		=> array(),
		'user_limit'	=> array(),
	);

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpsite-limit-posts' ), $this->version );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpsite-limit-posts' ), $this->version );
	}

	/**
	 * Main WPsite_Limit_Posts instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return WPsite_Limit_Posts
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) && ! ( self::$_instance instanceof WPsite_Limit_Posts ) ) {
			self::$_instance = new WPsite_Limit_Posts();
			self::$_instance->hooks();
		}

		return self::$_instance;
	}

	/**
	 * WPsite_Limit_Posts constructor.
	 */
	private function __construct() {

	}

	/**
	 * Add hooks to begin.
	 * @return void
	 */
	private function hooks() {

		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'init', array( $this, 'register_post_status' ) );
		add_action( 'wp_insert_post_data', array( $this, 'stop_publish_post' ), '99', 2 );

		if ( is_admin() ) {

			$plugin = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$plugin", array( $this, 'plugin_links' ) );

			add_action( 'admin_menu', array( $this, 'register_pages' ) );
			add_action( 'admin_notices', array( $this, 'posts_notice' ) );

			foreach ( array( 'post', 'post-new' ) as $hook ) {
				add_action( "admin_footer-{$hook}.php", array( $this, 'extend_submitdiv_post_status' ) );
			}
		}
	}

	/**
	 * Load the plugin text domain for translation.
	 * @return void
	 */
	public function load_plugin_textdomain() {

		$locale = apply_filters( 'plugin_locale', get_locale(), 'wpsite-limit-posts' );

		load_textdomain(
			'wpsite-limit-posts',
			WP_LANG_DIR . '/wpsite-limit-posts/wpsite-limit-posts-' . $locale . '.mo'
		);

		load_plugin_textdomain(
			'wpsite-limit-posts',
			false,
			$this->plugin_dir() . '/languages/'
		);
	}

	/**
	 * Hooks to 'plugin_action_links_' filter
	 *
	 * @since 1.0.0
	 */
	public function plugin_links( $links ) {

		$settings_link = '<a href="options-general.php?page=' . self::$settings_page . '">Settings</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Hooks to 'init' and resgisters new post status type
	 *
	 * @since 1.0.0
	 */
	public function register_post_status() {

		register_post_status( 'limited', array(
			'label'                     => esc_html__( 'Limited', 'wpsite-limit-posts' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Limited <span class="count">(%s)</span>',  'Limited <span class="count">(%s)</span>', 'wpsite-limit-posts' ),
		));
	}

	/**
	 * Hooks to 'admin_menu'
	 *
	 * @since 1.0.0
	 */
	public function register_pages() {

	    $settings_page_load = add_submenu_page(
	    	'options-general.php',
	    	esc_html__( 'Limit Posts', 'wpsite-limit-posts' ),
	    	esc_html__( 'Limit Posts', 'wpsite-limit-posts' ),
	    	'manage_options',
	    	self::$settings_page,
	    	array( $this, 'page_settings' )
	    );
	    add_action( "load-$settings_page_load", array( $this, 'admin_scripts' ) );
	}

	/**
	 * Displays the HTML for the 'wpsite-limit-posts-admin-menu-settings' admin page
	 *
	 * @since 1.0.0
	 */
	public function page_settings() {

		global $wp_roles;
		$settings = $this->get_settings();

		// Save data nd check nonce
		if ( isset( $_POST['submit'] ) && check_admin_referer( 'wpsite_limit_posts_admin_settings' ) ) {

			$limited_roles = array();
			$settings['all'] = isset( $_POST['wpsite_limit_posts_settings_all_users'] ) ? $_POST['wpsite_limit_posts_settings_all_users'] : 'capability';

			foreach ( $wp_roles->roles as $role ) {

				$role_name = strtolower( $role['name'] );

				if ( isset( $role['capabilities'] ) && isset( $role['capabilities']['publish_posts'] ) && ! isset( $role['capabilities']['moderate_comments'] ) ) {

					if ( '' === stripcslashes( sanitize_text_field( $_POST[ 'wpsite_limit_posts_settings_post_num_' . $role_name ] ) ) ) {
						$settings['all_limit'][ $role_name ] = -1;
						$limited_roles[] = $role['name'];
					} else {
						$settings['all_limit'][ $role_name ] = isset( $_POST[ 'wpsite_limit_posts_settings_post_num_' . $role_name ] ) ? (int) stripcslashes( sanitize_text_field( $_POST[ 'wpsite_limit_posts_settings_post_num_' . $role_name ] ) ) : '-1';
						$limited_roles[] = $role['name'];
					}
				}
			}

			$users = array();
			$all_users = get_users();

			foreach ( $all_users as $user ) {
				if ( user_can( $user->ID, 'publish_posts' ) && ! user_can( $user->ID, 'moderate_comments' ) ) {
					$users[] = $user;
				}
			}

			foreach ( $users as $user ) {

				if ( '' === stripcslashes( sanitize_text_field( $_POST[ 'wpsite_limit_posts_settings_user_' . $user->ID ] ) ) ) {
					$settings['user_limit'][ $user->ID ] = -1;
				} else {
					$settings['user_limit'][ $user->ID ] = isset( $_POST[ 'wpsite_limit_posts_settings_user_' . $user->ID ] ) ? (int) stripcslashes( sanitize_text_field( $_POST[ 'wpsite_limit_posts_settings_user_' . $user->ID ] ) ) : '-1';
				}
			}

			update_option( 'wpsite_limit_posts_settings', $settings );
		}

		require_once( 'admin/dashboard.php' );
	}

	/**
	 * Hooks to 'admin_print_scripts-$page'
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {

		// Styles
		wp_enqueue_style( 'wpsite_limit_posts_settings_css', wpsite_lps()->plugin_url() . 'css/settings.css' );
		wp_enqueue_style( 'wpsite_limit_posts_bootstrap_css', wpsite_lps()->plugin_url() . 'css/nnr-bootstrap.min.css' );

		// Scripts
		wp_enqueue_script( 'wpsite_limit_posts_admin_js', wpsite_lps()->plugin_url() . 'js/wpsite_limit_posts_admin.js' );
	}

	/**
	 * Hooks to 'admin_notices'
	 *
	 * @since 1.0.0
	 */
	public function posts_notice() {

		global $pagenow;

		if ( 'post.php' === $pagenow  && isset( $_GET['post'] ) ) {

			$post = get_post( $_GET['post'] );

			if ( isset( $post ) && 'limited' === $post->post_status ) {

				$author_data = get_userdata( $post->post_author );

				if ( isset( $author_data ) && get_current_user_id() !== $post->post_author ) {
					echo '<div class="error">
						<p>' . sprintf( esc_html__( 'Author: %s is at his or her post limit.', 'wpsite-limit-posts' ), $author_data->user_login ) . '</p>
					</div>';
				} else {
					echo '<div class="error">
						<p>' . esc_html__( 'You are at or you have exceeded your post limit.', 'wpsite-limit-posts' ) . '</p>
					</div>';
				}
			}
		}
	}

	/**
	 * Hooks to the 'wp_insert_post_data' action
	 *
	 * @since 1.0.0
	 */
	public function stop_publish_post( $data, $postarr ) {

		$user_data = get_userdata( $data['post_author'] );
		$caps = $user_data->wp_capabilities;
		$settings = $this->get_settings();

		if ( ! current_user_can( 'moderate_comments' ) && current_user_can( 'publish_posts' ) ) {

			if ( isset( $settings['all'] ) && 'capability' === $settings['all'] && -1 !== (int) $settings['all_limit'][ implode( ', ', $user_data->roles ) ] ) {

				// Capabilities
				if ( 'publish' === $data['post_status'] && (int) $settings['all_limit'][ implode( ', ', $user_data->roles ) ] <= (int) count_user_posts( $data['post_author'] ) && 'publish' !== get_post_status( $postarr['ID'] ) ) {
					$data['post_status'] = 'limited';
				}
			} else if ( isset( $settings['all'] ) && 'user' === $settings['all'] && -1 !== (int) $settings['user_limit'][ $data['post_author'] ] ) {

				// Users
				if ( 'publish' === $data['post_status'] && (int) $settings['user_limit'][ $data['post_author'] ] <= (int) count_user_posts( $data['post_author'] ) && 'publish' !== get_post_status( $postarr['ID'] ) ) {
					$data['post_status'] = 'limited';
				}
			}
		}

		return $data;
	}

	/**
	 * Adds post status to the "submitdiv" Meta Box and post type WP List Table screens. Based on https://gist.github.com/franz-josef-kaiser/2930190
	 *
	 * @return void
	 */
	public function extend_submitdiv_post_status() {
		global $wp_post_statuses, $post, $post_type;

		// Get all non-builtin post status and add them as <option>
		$options = $display = '';
		foreach ( $wp_post_statuses as $status ) {

			if ( ! $status->_builtin ) {

				// Match against the current posts status
				$selected = selected( $post->post_status, $status->name, false );

				// If we one of our custom post status is selected, remember it
				$selected AND $display = $status->label;

				// Build the options
				$options .= "<option{$selected} value='{$status->name}'>{$status->label}</option>";
			}
		}
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
				<?php
				// Add the selected post status label to the "Status: [Name] (Edit)"
				if ( ! empty( $display ) ) :
				?>
					$( '#post-status-display' ).html( '<?php echo $display; ?>' )
				<?php
				endif;

				// Add the options to the <select> element
				?>
				var select = $( '#post-status-select' ).find( 'select' );
				$( select ).append( "<?php echo $options; ?>" );
			} );
		</script>
		<?php
	}

	// Helpers -----------------------------------------------------------

	/**
	 * Get plugin directory.
	 * @return string
	 */
	public function plugin_dir() {

		if ( is_null( $this->plugin_dir ) ) {
			$this->plugin_dir = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/';
		}

		return $this->plugin_dir;
	}

	/**
	 * Get plugin uri.
	 * @return string
	 */
	public function plugin_url() {

		if ( is_null( $this->plugin_url ) ) {
			$this->plugin_url = untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/';
		}

		return $this->plugin_url;
	}

	/**
	 * Get settings.
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = get_option( 'wpsite_limit_posts_settings' );

		if ( false === $settings ) {
			$settings = self::$default;
		}

		return $settings;
	}

	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}
}

/**
 * Main instance of WPsite_Limit_Posts.
 *
 * Returns the main instance of WPsite_Limit_Posts to prevent the need to use globals.
 *
 * @return WPsite_Limit_Posts
 */
function wpsite_lps() {
	return WPsite_Limit_Posts::instance();
}
// Init the plugin.
wpsite_lps();
