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
					$limited_roles = array();
					
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
							$limited_roles[] = $role['name'];
						}
					}?>
					
					<!-- List all individual users -->
					
					<?php 
					
					$all_users = get_users();
					$users = array();
					
					foreach ($all_users as $user) {
						if (user_can($user->ID, 'publish_posts') && !user_can($user->ID, 'moderate_comments')) {
							$users[] = $user;
						}
					}
					
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
		<div class="wpsite_admin_panel_sidebar_img">
			<a target="_blank" href="http://wpsite.net"><img src="http://www.wpsite.net/wp-content/uploads/2011/10/logo-only-100h.png"></a>
		</div>
	</div>
</div>