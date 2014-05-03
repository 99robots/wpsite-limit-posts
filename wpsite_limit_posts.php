<?php
/*
Plugin Name: WPsite Limit Posts Beta
plugin URI:
Description: Limit the number of posts that your editors and authors can write.
version: 0.9
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
    define('WPSITE_LIMIT_POSTS_VERSION_NUM', '0.9.0');
 
 
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
	
	private static $text_domain = 'wpsite-limit-posts';
	
	private static $prefix = 'wpsite_limit_posts_';
	
	private static $settings_page = 'wpsite-limit-posts-admin-menu-settings';
	
	private static $default = array(
		'all'			=> true,
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
			
		global $wp_roles;
		
		if (!current_user_can('moderate_comments') && current_user_can('publish_posts')) { 
		
			if (isset($settings['all']) && $settings['all']) {
				
				if ($data['post_status'] == 'publish' && (int) $settings['all_limit'][implode(', ', $user_data->roles)] <= (int) count_user_posts($data['post_author']) && get_post_status($postarr['ID']) != 'publish') {
					$data['post_status'] = 'pending';
				}
				
			} else if (isset($settings['all']) && !$settings['all']) {
				
				if ($data['post_status'] == 'publish' && (int) $settings['user_limit'][$data['post_author']] <= (int) count_user_posts($data['post_author']) && get_post_status($postarr['ID']) != 'publish') {
					$data['post_status'] = 'pending';
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
	    
	    $settings_page_load = add_submenu_page(
	    	'tools.php', 													// parent slug
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
		
		wp_register_style('wpsite_limit_posts_admin_css', WPSITE_LIMIT_POSTS_PLUGIN_URL . '/include/css/wpsite_limit_posts_admin.css');
		wp_enqueue_style('wpsite_limit_posts_admin_css');
	
		/* Javascript */
		
		wp_register_script('wpsite_limit_posts_admin_js', WPSITE_LIMIT_POSTS_PLUGIN_URL . '/include/js/wpsite_limit_posts_admin.js');
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
				
			$settings['all'] = isset($_POST['wpsite_limit_posts_settings_all_users']) && $_POST['wpsite_limit_posts_settings_all_users'] ? true : false;
				
			$limited_roles = '';
			
			foreach ($wp_roles->roles as $role) {
			
				$role_name = strtolower($role['name']);
				
				if (isset($role['capabilities']) && isset($role['capabilities']['publish_posts']) && !isset($role['capabilities']['moderate_comments'])) { 
					$settings['all_limit'][$role_name] = isset($_POST['wpsite_limit_posts_settings_post_num_' . $role_name]) ? (int) stripcslashes(sanitize_text_field($_POST['wpsite_limit_posts_settings_post_num_' . $role_name])) : null;
					$limited_roles .= $role_name . ',';
				}
			}
			
			$users = get_users(array(
				'role'  => trim($limited_roles, ',')
			));
			
			foreach ($users as $user) {
				$settings['user_limit'][$user->ID] = isset($_POST['wpsite_limit_posts_settings_user_' . $user->ID]) ? (int) stripcslashes(sanitize_text_field($_POST['wpsite_limit_posts_settings_user_' . $user->ID])) : null;
			}
			
			update_option('wpsite_limit_posts_settings', $settings);
		}
		
		?>
		
		<div class="wrap wpsite_admin_panel">
			<div class="wpsite_admin_panel_banner">
				<h1><?php _e('WPsite Limit Posts', self::$text_domain); ?></h1>
			</div>
			
			<div id="wpsite_admin_panel_settings" class="wpsite_admin_panel_content">
		
				<form method="post">
					
					<table>
						<tbody>
						
							<!-- Checkbox for all users or individual -->
							
							<tr>
								<th class="wpsite_limit_posts_admin_table_th">
									<label><?php _e('Limit All Users', self::$text_domain); ?></label>
									<td class="wpsite_limit_posts_admin_table_td">
										<input id="wpsite_limit_posts_settings_all_users" name="wpsite_limit_posts_settings_all_users" type="checkbox" <?php echo isset($settings['all']) && $settings['all'] ? 'checked="checked"' : ''; ?>>
									</td>
								</th>
							</tr>
							
							<!-- All users -->
						
							<?php
							$limited_roles = '';
							
							foreach ($wp_roles->roles as $role) {
								
								$role_name = strtolower($role['name']);
							
								if (isset($role['capabilities']) && isset($role['capabilities']['publish_posts']) && !isset($role['capabilities']['moderate_comments'])) {
									?>
									<tr class="wpsite_limit_posts_roles">
										<th class="wpsite_limit_posts_admin_table_th">
											<label><?php _e($role['name'], self::$text_domain); ?></label>
											<td class="wpsite_limit_posts_admin_table_td">
												<input id="wpsite_limit_posts_settings_post_num_<?php echo $role_name; ?>" name="wpsite_limit_posts_settings_post_num_<?php echo $role_name; ?>" type="text" size="10" value="<?php echo isset($settings['all_limit'][$role_name]) ? esc_attr($settings['all_limit'][$role_name]) : ''; ?>">
											</td>
										</th>
									</tr>
									<?php
									$limited_roles .= $role['name'] . ',';
								}
							}?>
							
							<!-- List all individual users -->
							
							<?php 
							
							$users = get_users(array(
								'role'  => trim($limited_roles, ',')
							));
							
							foreach ($users as $user) {
								?><tr class="wpsite_limit_posts_users">
									<th class="wpsite_limit_posts_admin_table_th">
										<label><?php _e($user->user_nicename, self::$text_domain); ?></label>
										<td class="wpsite_limit_posts_admin_table_td">
											<input id="wpsite_limit_posts_settings_user_<?php echo $user->ID; ?>" name="wpsite_limit_posts_settings_user_<?php echo $user->ID; ?>" type="text" size="10" value="<?php echo isset($settings['user_limit'][$user->ID]) ? esc_attr($settings['user_limit'][$user->ID]) : ''; ?>">
										</td>
									</th>
								</tr><?php
							}
							
							?>
						
						</tbody>
					</table>
					
				<?php wp_nonce_field('wpsite_limit_posts_admin_settings'); ?>
				
				<?php submit_button(); ?>
				
				</form>

			</div>
			
			<div id="wpsite_admin_panel_sidebar" class="wpsite_admin_panel_content">
				<img src="http://www.wpsite.net/wp-content/uploads/2011/10/logo-only-100h.png">
			</div>
		</div>
		
		
		<?php
	}
}
?>