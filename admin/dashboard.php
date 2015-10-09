<div class="nnr-wrap">

	<?php require_once('header.php'); ?>

	<div class="nnr-container">

		<div class="nnr-content">

			<h1 id="nnr-heading"><?php _e('Settings', self::$text_domain); ?></h1>

			<form method="post" class="form-horizontal">

				<!-- Limit by -->

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php _e('Limit by', self::$text_domain); ?></label>
					<div class="col-sm-9">
						<input name="wpsite_limit_posts_settings_all_users" type="radio" value="capability" <?php echo isset($settings['all']) && $settings['all'] == 'capability' ? 'checked="checked"' : ''; ?>><span><?php _e('Role', self::$text_domain); ?></span><br />

						<input name="wpsite_limit_posts_settings_all_users" type="radio" value="user" <?php echo isset($settings['all']) && $settings['all'] == 'user' ? 'checked="checked"' : ''; ?>><span><?php _e('User', self::$text_domain); ?></span>
					</div>
				</div>

				<!-- All users -->

				<?php
				$limited_roles = array();

				foreach ($wp_roles->roles as $role) {

					$role_name = strtolower($role['name']);

					if (isset($role['capabilities']) && isset($role['capabilities']['publish_posts']) && !isset($role['capabilities']['moderate_comments'])) {
						?>
						<div class="form-group wpsite_limit_posts_roles">
							<label class="col-sm-3 control-label"><?php _e($role['name'], self::$text_domain); ?></label>
							<div class="col-sm-9">
								<input id="wpsite_limit_posts_settings_post_num_<?php echo $role_name; ?>" name="wpsite_limit_posts_settings_post_num_<?php echo $role_name; ?>" type="text" class="form-control" value="<?php echo isset($settings['all_limit'][$role_name]) ? esc_attr($settings['all_limit'][$role_name]) : ''; ?>">
								<em class="help-block"><?php _e("Default: -1 (i.e. umlimited)", self::$text_domain); ?></em>
							</div>
						</div>
						<?php
						$limited_roles[] = $role['name'];
					}
				} ?>

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
					?>
					<div class="form-group wpsite_limit_posts_users">
						<label class="col-sm-3 control-label"><?php _e($user->user_nicename, self::$text_domain); ?></label>
						<div class="col-sm-9">
							<input id="wpsite_limit_posts_settings_user_<?php echo $user->ID; ?>" name="wpsite_limit_posts_settings_user_<?php echo $user->ID; ?>" type="text" class="form-control" value="<?php echo isset($settings['user_limit'][$user->ID]) ? esc_attr($settings['user_limit'][$user->ID]) : ''; ?>">
							<em class="help-block"><?php _e("Default: -1 (i.e. umlimited)", self::$text_domain); ?></em>
						</div>
					</div><?php
				} ?>

				<?php wp_nonce_field('wpsite_limit_posts_admin_settings'); ?>

				<p class="submit"><input type="submit" name="submit" id="submit" class="btn btn-info" value="<?php _e("Save Changes", self::$text_domain); ?>"></p>

			</form>

		</div>

		<?php require_once('sidebar.php'); ?>

	</div>

	<?php require_once('footer.php'); ?>

</div>
