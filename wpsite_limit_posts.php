<?php
/*
Plugin Name: WPsite Limit Posts
plugin URI: http://wpsite.net
Description: Limit the number of posts or custom post types that can be published based on role (i.e, author) or user.
version: 1.0.3
Author: WPSITE.net
Author URI: http://wpsite.net
License: GPL2
*/

/**
 * Global Definitions
 */

/* Plugin Name */

if (!defined('WPSITE_LIMIT_POSTS_PLUGIN_NAME'))
    define('WPSITE_LIMIT_POSTS_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));

/* Plugin directory */

if (!defined('WPSITE_LIMIT_POSTS_PLUGIN_DIR'))
    define('WPSITE_LIMIT_POSTS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . WPSITE_LIMIT_POSTS_PLUGIN_NAME);

/* Plugin url */

if (!defined('WPSITE_LIMIT_POSTS_PLUGIN_URL'))
    define('WPSITE_LIMIT_POSTS_PLUGIN_URL', WP_PLUGIN_URL . '/' . WPSITE_LIMIT_POSTS_PLUGIN_NAME);

/* Plugin verison */

if (!defined('WPSITE_LIMIT_POSTS_VERSION_NUM'))
    define('WPSITE_LIMIT_POSTS_VERSION_NUM', '1.0.3');


/**
 * Activatation / Deactivation
 */

register_activation_hook( __FILE__, array('WPsiteLimitPosts', 'register_activation'));

/**
 * Hooks / Filter
 */

add_action('init', array('WPsiteLimitPosts', 'wpsite_limit_post_register_post_status'));
add_action('init', array('WPsiteLimitPosts', 'load_textdoamin'));

add_action('admin_menu', array('WPsiteLimitPosts', 'wpsite_limit_posts_menu_page'));
add_action('wp_insert_post_data', array('WPsiteLimitPosts', 'wpsite_stop_publish_post'),'99', 2 );
add_action('admin_notices', array('WPsiteLimitPosts', 'wpsite_limit_posts_notice'));

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", array('WPsiteLimitPosts', 'wpsite_limit_posts_settings_link'));

foreach ( array( 'post', 'post-new' ) as $hook )
	add_action( "admin_footer-{$hook}.php", array('WPsiteLimitPosts' ,'extend_submitdiv_post_status' ) );


/**
 *  WPsiteLimitPosts main class
 *
 * @since 1.0.0
 * @using Wordpress 3.9.1
 */

class WPsiteLimitPosts {

	/* Properties */

	private static $version_setting_name = 'wpsite_limit_posts_verison';

	private static $text_domain = 'wpsite-limit-posts';

	private static $prefix = 'wpsite_limit_posts_';

	private static $settings_page = 'wpsite-limit-posts-admin-menu-settings';

	private static $default = array(
		'all'			=> 'capability',
		'all_limit'		=> array(),
		'user_limit'	=> array()
	);

	/**
	 * Hooks to 'init' and loads the text domain
	 *
	 * @since 1.0.0
	 */
	static function load_textdoamin() {
		load_plugin_textdomain(self::$text_domain, false, WPSITE_LIMIT_POSTS_PLUGIN_DIR . '/languages');
	}

	/**
	 * Hooks to 'init' and resgisters new post status type
	 *
	 * @since 1.0.0
	 */
	static function wpsite_limit_post_register_post_status() {
		register_post_status( 'limited', array(
			'label'                     => __( 'Limited', self::$text_domain),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop('Limited <span class="count">(%s)</span>',  'Limited <span class="count">(%s)</span>', self::$text_domain)
		));
	}

	/**
	 * Hooks to 'plugin_action_links_' filter
	 *
	 * @since 1.0.0
	 */
	static function wpsite_limit_posts_settings_link($links) {
		$settings_link = '<a href="options-general.php?page=' . self::$settings_page . '">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	/**
	 * Hooks to the 'wp_insert_post_data' action
	 *
	 * @since 1.0.0
	 */
	static function wpsite_stop_publish_post($data, $postarr) {

		$user_data = get_userdata($data['post_author']);
		$caps = $user_data->wp_capabilities;

		$settings = get_option('wpsite_limit_posts_settings');

		/* Default values */

		if ($settings === false)
			$settings = self::$default;

		global $wp_roles;

		if (!current_user_can('moderate_comments') && current_user_can('publish_posts')) {

			// Capabilities

			if (isset($settings['all']) && $settings['all'] == 'capability' && (int) $settings['all_limit'][implode(', ', $user_data->roles)] != -1) {

				if ($data['post_status'] == 'publish' && (int) $settings['all_limit'][implode(', ', $user_data->roles)] <= (int) count_user_posts($data['post_author']) && get_post_status($postarr['ID']) != 'publish') {
					$data['post_status'] = 'limited';
				}

			}

			// Users

			else if (isset($settings['all']) && $settings['all'] == 'user' && (int) $settings['user_limit'][$data['post_author']] != -1) {

				if ($data['post_status'] == 'publish' && (int) $settings['user_limit'][$data['post_author']] <= (int) count_user_posts($data['post_author']) && get_post_status($postarr['ID']) != 'publish') {
					$data['post_status'] = 'limited';
				}

			}

		}

		return $data;
	}

	/**
	 * Hooks to 'admin_notices'
	 *
	 * @since 1.0.0
	 */
	static function wpsite_limit_posts_notice() {

		global $pagenow;

		if ($pagenow == 'post.php' && isset($_GET['post'])) {

			$post = get_post($_GET['post']);

			if (isset($post) && $post->post_status == 'limited') {

				$author_data = get_userdata($post->post_author);

				if (isset($author_data) && get_current_user_id() != $post->post_author) {
					echo '<div class="error">
						<p>' . __("Author: $author_data->user_login is at his or her post limit.", self::$text_domain) . '</p>
					</div>';
				} else {
					echo '<div class="error">
						<p>' . __("You are at or you have exceeded your post limit.", self::$text_domain) . '</p>
					</div>';
				}
			}
		}
	}

	/**
	 * Hooks to 'init'
	 *
	 * @since 1.0.0
	 */
	static function register_activation() {

		/* Check if multisite, if so then save as site option */

		if (is_multisite()) {
			add_site_option(self::$version_setting_name, WPSITE_LIMIT_POSTS_VERSION_NUM);
		} else {
			add_option(self::$version_setting_name, WPSITE_LIMIT_POSTS_VERSION_NUM);
		}
	}

	/**
	 * Hooks to 'admin_menu'
	 *
	 * @since 1.0.0
	 */
	static function wpsite_limit_posts_menu_page() {

	    /* Cast the first sub menu to the tools menu */

	    $settings_page_load = add_submenu_page(
	    	'options-general.php', 													// parent slug
	    	__('WPsite Limit Posts', self::$text_domain), 						// Page title
	    	__('WPsite Limit Posts', self::$text_domain), 						// Menu name
	    	'manage_options', 											// Capabilities
	    	self::$settings_page, 										// slug
	    	array('WPsiteLimitPosts', 'wpsite_limit_posts_admin_settings')	// Callback function
	    );
	    add_action("admin_print_scripts-$settings_page_load", array('WPsiteLimitPosts', 'wpsite_limit_posts_include_admin_scripts'));
	}

	/**
	 * Hooks to 'admin_print_scripts-$page'
	 *
	 * @since 1.0.0
	 */
	static function wpsite_limit_posts_include_admin_scripts() {

		/* CSS */

		wp_register_style('wpsite_limit_posts_admin_css', WPSITE_LIMIT_POSTS_PLUGIN_URL . '/css/wpsite_limit_posts_admin.css');
		wp_enqueue_style('wpsite_limit_posts_admin_css');

		/* Javascript */

		wp_register_script('wpsite_limit_posts_admin_js', WPSITE_LIMIT_POSTS_PLUGIN_URL . '/js/wpsite_limit_posts_admin.js');
		wp_enqueue_script('wpsite_limit_posts_admin_js');
	}

	/**
	 * Displays the HTML for the 'wpsite-limit-posts-admin-menu-settings' admin page
	 *
	 * @since 1.0.0
	 */
	static function wpsite_limit_posts_admin_settings() {

		global $wp_roles;
		$settings = get_option('wpsite_limit_posts_settings');

		/* Default values */

		if ($settings === false)
			$settings = self::$default;

		/* Save data nd check nonce */

		if (isset($_POST['submit']) && check_admin_referer('wpsite_limit_posts_admin_settings')) {

			$settings = get_option('wpsite_limit_posts_settings');

			/* Default values */

			if ($settings === false)
				$settings = self::$default;

			$settings['all'] = isset($_POST['wpsite_limit_posts_settings_all_users']) ? $_POST['wpsite_limit_posts_settings_all_users'] : 'capability';

			$limited_roles = array();

			foreach ($wp_roles->roles as $role) {

				$role_name = strtolower($role['name']);

				if (isset($role['capabilities']) && isset($role['capabilities']['publish_posts']) && !isset($role['capabilities']['moderate_comments'])) {

					if (stripcslashes(sanitize_text_field($_POST['wpsite_limit_posts_settings_post_num_' . $role_name])) == '') {
						$settings['all_limit'][$role_name] = -1;
						$limited_roles[] = $role['name'];
					} else {
						$settings['all_limit'][$role_name] = isset($_POST['wpsite_limit_posts_settings_post_num_' . $role_name]) ? (int) stripcslashes(sanitize_text_field($_POST['wpsite_limit_posts_settings_post_num_' . $role_name])) : '-1';
						$limited_roles[] = $role['name'];
					}
				}
			}

			$all_users = get_users();
			$users = array();

			foreach ($all_users as $user) {
				if (user_can($user->ID, 'publish_posts') && !user_can($user->ID, 'moderate_comments')) {
					$users[] = $user;
				}
			}

			foreach ($users as $user) {

				if (stripcslashes(sanitize_text_field($_POST['wpsite_limit_posts_settings_user_' . $user->ID])) == '') {
					$settings['user_limit'][$user->ID] = -1;
				} else {
					$settings['user_limit'][$user->ID] = isset($_POST['wpsite_limit_posts_settings_user_' . $user->ID]) ? (int) stripcslashes(sanitize_text_field($_POST['wpsite_limit_posts_settings_user_' . $user->ID])) : '-1';
				}

			}

			update_option('wpsite_limit_posts_settings', $settings);
		}

		require_once('admin/dashboard.php');
	}

	/**
	 * Adds post status to the "submitdiv" Meta Box and post type WP List Table screens. Based on https://gist.github.com/franz-josef-kaiser/2930190
	 *
	 * @return void
	 */
	static function extend_submitdiv_post_status() {
		global $wp_post_statuses, $post, $post_type;

		// Get all non-builtin post status and add them as <option>
		$options = $display = '';
		foreach ( $wp_post_statuses as $status )
		{
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
			jQuery( document ).ready( function($)
			{
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
}
?>