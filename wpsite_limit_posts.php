<?php
/*
Plugin Name: WPsite Limit Posts Beta
plugin URI: wpsite-limit-posts
Description: Limit the number of posts are certian type of user can create.
version: 1.0
Author: Kyle Benk
Author URI: http://kylebenkapps.com
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
    define('WPSITE_LIMIT_POSTS_VERSION_NUM', '1.0.0');
 
 
/** 
 * Activatation / Deactivation 
 */  

register_activation_hook( __FILE__, array('WPsiteLimitPosts', 'register_activation'));

/** 
 * Hooks / Filter 
 */

//add_action('init', array('WPsiteLimitPosts', 'wpsite_limit_post_register_post_status'));
add_action('init', array('WPsiteLimitPosts', 'load_textdoamin'));

add_action('admin_menu', array('WPsiteLimitPosts', 'wpsite_limit_posts_menu_page'));
add_action('admin_enqueue_scripts', array('WPsiteLimitPosts', 'wpsite_limit_posts_include_admin_scripts'));
add_action('wp_insert_post_data', array('WPsiteLimitPosts', 'wpsite_stop_publish_post'),'99', 2 );

$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", array('WPsiteLimitPosts', 'wpsite_limit_posts_settings_link'));


/** 
 *  WPsiteLimitPosts main class
 *
 * @since 1.0.0
 * @using Wordpress 3.8
 */

class WPsiteLimitPosts {

	/* Properties */
	
	private static $version_setting_name = 'wpsite_limit_posts_verison';
	
	private static $jquery_latest = 'http://code.jquery.com/jquery-latest.min.js';
	
	private static $text_domain = 'wpsite-limit-posts';
	
	private static $prefix = 'wpsite_limit_posts_';
	
	private static $settings_page = 'wpsite-limit-posts-admin-menu-settings';
	
	private static $default = array(
		'roles'	=> array()
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
		$settings_link = '<a href="tools.php?page=' . self::$settings_page . '">Settings</a>'; 
		array_unshift($links, $settings_link); 
		return $links; 
	}
	
	/**
	 * Hooks to the 'publish_post' action 
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
		
		// Editor
		
		if (array_key_exists('editor', $caps)) {
			/*
error_log('Editor');
			error_log('Editor can post: ' . (int) $settings['roles']['Editor']);
			error_log('current posts: ' . count_user_posts($data['post_author']));
*/
			if ($data['post_status'] == 'publish' && (int) $settings['roles']['Editor'] <= (int) count_user_posts($data['post_author']) && get_post_status($postarr['ID']) != 'publish') {
			
				$data['post_status'] = 'pending';
			}
		} 
		
		// Author
		
		else if (array_key_exists('author', $caps)) {
			/*
error_log('Author');
			error_log('Authors can post: ' . (int) $settings['roles']['Author']);
			error_log('current posts: ' . count_user_posts($data['post_author']));
*/
			if ($data['post_status'] == 'publish' && (int) $settings['roles']['Author'] <= (int) count_user_posts($data['post_author']) && get_post_status($postarr['ID']) != 'publish') {
				$data['post_status'] = 'pending';
			}
		}
		
		// Contributor
		
		else if (array_key_exists('contributor', $caps)) {
			error_log('Contributor');
		}
		
		return $data;
	}
	
	/**
	 * Hooks to 'admin_notices' 
	 * 
	 * @since 1.0.0
	 */
	static function wpsite_limit_posts_notice() {
		?>
		<div class="error">
		    <p><?php _e('You are at your post limit.  You are not allowed to publish anymore posts.', self::$text_domain); ?></p>
		</div>
		<?php
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
	    
	    add_submenu_page(
	    	'tools.php', 													// parent slug
	    	__('WPsite Limit Posts', self::$text_domain), 						// Page title
	    	__('WPsite Limit Posts', self::$text_domain), 						// Menu name
	    	'manage_options', 											// Capabilities
	    	self::$settings_page, 										// slug
	    	array('WPsiteLimitPosts', 'wpsite_limit_posts_admin_settings')	// Callback function
	    );
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
				
			$roles = $wp_roles->get_names();
									
			foreach ($roles as $role) {
				$settings['roles'][$role] = isset($_POST['wpsite_limit_posts_settings_post_num_' . $role]) ? (int) stripcslashes(sanitize_text_field($_POST['wpsite_limit_posts_settings_post_num_' . $role])) : null;
			}
			
			update_option('wpsite_limit_posts_settings', $settings);
		}
		
		?>
		
		<h1><?php _e('WPsite Limit Posts', self::$text_domain); ?></h1>
		
		<form method="post">
			
			<table>
				<tbody>
				
					<?php
					$roles = $wp_roles->get_names();
					
					foreach ($roles as $role) { 
						
						if (!is_multisite() && $role != 'Administrator' && $role != 'Subscriber') { ?>
							<tr>
								<th class="wpsite_limit_posts_admin_table_th">
									<label><?php _e($role, self::$text_domain); ?></label>
									<td class="wpsite_limit_posts_admin_table_td">
										<input id="wpsite_limit_posts_settings_post_num_<?php echo $role; ?>" name="wpsite_limit_posts_settings_post_num_<?php echo $role; ?>" type="text" size="10" value="<?php echo isset($settings['roles'][$role]) ? esc_attr($settings['roles'][$role]) : ''; ?>">
									</td>
								</th>
							</tr>
						<?php }else if (is_multisite() && $role != 'Super Admin' && $role != 'Subscriber') { ?>
							<tr>
								<th class="wpsite_limit_posts_admin_table_th">
									<label><?php _e($role, self::$text_domain); ?></label>
									<td class="wpsite_limit_posts_admin_table_td">
										<input id="wpsite_limit_posts_settings_post_num_<?php echo $role; ?>" name="wpsite_limit_posts_settings_post_num_<?php echo $role; ?>" type="text" size="10" value="<?php echo isset($settings['roles'][$role]) ? esc_attr($settings['roles'][$role]) : ''; ?>">
									</td>
								</th>
							</tr>
						<?php }
					} ?>
				
				</tbody>
			</table>
			
		<?php wp_nonce_field('wpsite_limit_posts_admin_settings'); ?>
		
		<?php submit_button(); ?>
		
		</form>
		
		<?php
	}
	
	/**
	 * Hooks to 'admin_enqueue_scripts' 
	 * 
	 * @since 1.0.0
	 */
	static function wpsite_limit_posts_include_admin_scripts() {
		
		/* Include Admin scripts */
		
		if (isset($_GET['page']) && ($_GET['page'] == self::$settings_page)) {
		
			/* CSS */
			
			wp_register_style('wpsite_limit_posts_admin_css', WPSITE_LIMIT_POSTS_PLUGIN_URL . '/include/css/wpsite_limit_posts.css');
			wp_enqueue_style('wpsite_limit_posts_admin_css');
		
			/* Javascript */
			
			wp_register_script('wpsite_limit_posts_admin_js', WPSITE_LIMIT_POSTS_PLUGIN_URL . '/include/js/wpsite_limit_posts.js');
			wp_enqueue_script('wpsite_limit_posts_admin_js');	
		}
	}
}

?>