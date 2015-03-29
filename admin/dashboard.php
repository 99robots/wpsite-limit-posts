<div class="wrap">

	<div class="wpsite_plugin_wrapper">

		<div class="wpsite_plugin_header">
				<!-- ** UPDATE THE UTM LINK BELOW ** -->
				<div class="announcement">
					<h2><?php _e('Check out the all new', self::$text_domain); ?> <strong><?php _e('WPsite.net', self::$text_domain); ?></strong> <?php _e('for more WordPress resources, plugins, and news.', self::$text_domain); ?></h2>
					<a  class="show-me" href="http://www.wpsite.net/?utm_source=limit-posts-plugin&amp;utm_medium=announce&amp;utm_campaign=top"><?php _e('Click Here', self::$text_domain); ?></a>
				</div>

				<header class="headercontent">
					<!-- ** UPDATE THE NAME ** -->
					<h1 class="logo"><?php _e('Limit Posts', self::$text_domain); ?></h1>
					<span class="slogan"><?php _e('by', self::$text_domain); ?> <a href="http://www.wpsite.net/?utm_source=topadmin&amp;utm_medium=announce&amp;utm_campaign=top"><?php _e('WPsite.net', self::$text_domain); ?></a></span>

					<!-- ** UPDATE THE 2 LINKS ** -->
					<div class="top-call-to-actions">
						<a class="tweet-about-plugin" href="https://twitter.com/intent/tweet?text=Neat%20and%20simple%20plugin%20for%20WordPress%20users.%20Check%20out%20the%20Limit%20Posts%20plugin%20by%20@WPsite%20-%20&amp;url=http%3A%2F%2Fwpsite.net%2Fplugins%2F&amp;via=wpsite"><span></span><?php _e('Tweet About WPsite', self::$text_domain); ?></a>
						<a class="leave-a-review" href="http://wordpress.org/support/view/plugin-reviews/wpsite-limit-posts#postform" target="_blank"><span></span> <?php _e('Leave A Review', self::$text_domain); ?></a>
					</div><!-- end .top-call-to-actions -->
				</header>
		</div> <!-- /wpsite_plugin_header -->

		<div id="wpsite_plugin_content">

			<div id="wpsite_plugin_settings">

				<form method="post">

					<table>
						<tbody>

							<!-- Checkbox for all users or individual -->

							<tr>
								<th class="wpsite_limit_posts_admin_table_th">
									<label><?php _e('Limit by', self::$text_domain); ?></label>
									<td class="wpsite_limit_posts_admin_table_td">
										<input name="wpsite_limit_posts_settings_all_users" type="radio" value="capability" <?php echo isset($settings['all']) && $settings['all'] == 'capability' ? 'checked="checked"' : ''; ?>><label><?php _e('Role', self::$text_domain); ?></label><br />

										<input name="wpsite_limit_posts_settings_all_users" type="radio" value="user" <?php echo isset($settings['all']) && $settings['all'] == 'user' ? 'checked="checked"' : ''; ?>><label><?php _e('User', self::$text_domain); ?></label>

										<input name="wpsite_limit_posts_settings_all_users" type="radio" value="post_type" <?php echo isset($settings['all']) && $settings['all'] == 'post_type' ? 'checked="checked"' : ''; ?>><label><?php _e('Post Type', self::$text_domain); ?></label>
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
												<input id="wpsite_limit_posts_settings_post_num_<?php echo $role_name; ?>" name="wpsite_limit_posts_settings_post_num_<?php echo $role_name; ?>" type="text" size="10" value="<?php echo isset($settings['all_limit'][$role_name]) ? esc_attr($settings['all_limit'][$role_name]) : ''; ?>"><br/>
												<em><?php _e("Default: -1 (i.e. umlimited)", self::$text_domain); ?></em>
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
											<input id="wpsite_limit_posts_settings_user_<?php echo $user->ID; ?>" name="wpsite_limit_posts_settings_user_<?php echo $user->ID; ?>" type="text" size="10" value="<?php echo isset($settings['user_limit'][$user->ID]) ? esc_attr($settings['user_limit'][$user->ID]) : ''; ?>"><br/>
											<em><?php _e("Default: -1 (i.e. umlimited)", self::$text_domain); ?></em>
										</td>
									</th>
								</tr><?php
							}

							?>

							<!-- List all Post Types -->

							<?php

							$all_post_types_public = get_post_types(array('public'=> true),'names');
							$all_post_types = array();
							$post_types = array();

							foreach ($all_post_types_public as $a){
								if ($a != 'attachment'){
									$all_post_types[] = $a;
								}
							}
							foreach ($all_post_types as $post_type) {
								$post_types[] = $post_type;
							}

							foreach ($post_types as $post_type) {
								?><tr class="wpsite_limit_posts_post_type">
								<th class="wpsite_limit_posts_admin_table_th">
									<label><?php _e($post_type, self::$text_domain); ?></label>
								<td class="wpsite_limit_posts_admin_table_td">
									<input id="wpsite_limit_posts_settings_<?php echo $post_type; ?>" name="wpsite_limit_posts_settings_<?php echo $post_type; ?>" type="text" size="10" value="<?php echo isset($settings['post_type_limit'][$post_type]) ? esc_attr($settings['post_type_limit'][$post_type]) : ''; ?>"><br/>
									<em><?php _e("Default: -1 (i.e. umlimited)", self::$text_domain); ?></em>
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

			</div> <!-- wpsite_plugin_settings -->

			<div id="wpsite_plugin_sidebar">
				<div class="wpsite_feed">
					<h3><?php _e('Must-Read Articles', self::$text_domain); ?></h3>
					<script src="http://feeds.feedburner.com/wpsite?format=sigpro" type="text/javascript" ></script><noscript><p><?php _e('Subscribe to WPsite Feed:', self::$text_domain); ?> <a href="http://feeds.feedburner.com/wpsite"></a><br/><?php _e('Powered by FeedBurner', self::$text_domain); ?></p> </noscript>
				</div>

				<div class="mktg-banner">
					<a target="_blank" href="http://www.wpsite.net/custom-wordpress-development/#utm_source=plugin-config&utm_medium=banner&utm_campaign=custom-development-banner"><img src="<?php echo WPSITE_LIMIT_POSTS_PLUGIN_URL . '/img/ad-custom-development.png' ?>"></a>
				</div>

				<div class="mktg-banner">
					<a target="_blank" href="http://www.wpsite.net/services/#utm_source=plugin-config&utm_medium=banner&utm_campaign=plugin-request-banner"><img src="<?php echo WPSITE_LIMIT_POSTS_PLUGIN_URL . '/img/ad-plugin-request.png' ?>"></a>
				</div>

				<div class="mktg-banner">
					<a target="_blank" href="http://www.wpsite.net/themes/#utm_source=plugin-config&utm_medium=banner&utm_campaign=themes-banner"><img src="<?php echo WPSITE_LIMIT_POSTS_PLUGIN_URL . '/img/ad-themes.png' ?>"></a>
				</div>

<!--
				<div class="mktg-banner">
					<a target="_blank" href="http://www.wpsite.net/services/#utm_source=plugin-config&utm_medium=banner&utm_campaign=need-support-banner"><img src="<?php echo WPSITE_LIMIT_POSTS_PLUGIN_URL . '/img/ad-need-support.png' ?>"></a>
				</div>
-->

			</div> <!-- wpsite_plugin_sidebar -->

		</div> <!-- /wpsite_plugin_content -->

	</div> 	<!-- /wpsite_plugin_wrapper -->

</div> 	<!-- /wrap -->
