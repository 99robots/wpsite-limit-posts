<div class="nnr-wrap">

	<?php require_once( 'header.php' ) ?>

	<div class="nnr-container">

		<div class="nnr-content">

			<h1 id="nnr-heading"><?php esc_html_e( 'Settings', 'wpsite-limit-posts' ) ?></h1>

			<form method="post" class="form-horizontal">

				<!-- Limit by -->

				<div class="form-group">
					<label class="col-sm-3 control-label"><?php esc_html_e( 'Limit by', 'wpsite-limit-posts' ) ?></label>
					<div class="col-sm-9">
						<input name="wpsite_limit_posts_settings_all_users" type="radio" value="capability" <?php echo isset( $settings['all'] ) && 'capability' === $settings['all'] ? 'checked="checked"' : ''; ?>><span><?php esc_html_e( 'Role', 'wpsite-limit-posts' ) ?></span><br />

						<input name="wpsite_limit_posts_settings_all_users" type="radio" value="user" <?php echo isset( $settings['all'] ) && 'user' === $settings['all'] ? 'checked="checked"' : ''; ?>><span><?php esc_html_e( 'User', 'wpsite-limit-posts' ) ?></span>
					</div>
				</div>

				<!-- All users -->

				<?php

				global $wp_roles;
				$limited_roles = array();

				foreach ( $wp_roles->roles as $role ) {

					$role_name = strtolower( $role['name'] );

					if ( isset( $role['capabilities'] ) && isset( $role['capabilities']['publish_posts'] ) && !isset($role['capabilities']['update_core']) && !isset($role['capabilities']['install_themes']) && !isset($role['capabilities']['install_plugins'])  ) {
					?>
					<div class="form-group wpsite_limit_posts_roles">
						<label class="col-sm-3 control-label"><?php echo $role['name'] ?></label>
						<div class="col-sm-9">
						<input id="wpsite_limit_posts_settings_post_num_<?php echo $role_name ?>" name="wpsite_limit_posts_settings_post_num_<?php echo $role_name ?>" type="text" class="form-control" value="<?php echo isset( $settings['all_limit'][ $role_name ] ) ? esc_attr( $settings['all_limit'][ $role_name ] ) : ''; ?>">
							<em class="help-block"><?php esc_html_e( 'Default: -1 (i.e. unlimited)', 'wpsite-limit-posts' ) ?></em>
						</div>
					</div>
					<?php
					$limited_roles[] = $role['name'];
					}
				}
				?>

				<!-- List all individual users -->
				<?php

				$users = array();
				$all_users = get_users();

				foreach ( $all_users as $user ) {
					if ( user_can( $user->ID, 'publish_posts' )) {
						$users[] = $user;
					}
				}

				foreach ( $users as $user ) {
				?>
				<div class="form-group wpsite_limit_posts_users">
					<label class="col-sm-3 control-label"><?php echo $user->user_nicename ?></label>
					<div class="col-sm-9">
						<input id="wpsite_limit_posts_settings_user_<?php echo $user->ID ?>" name="wpsite_limit_posts_settings_user_<?php echo $user->ID ?>" type="text" class="form-control" value="<?php echo isset( $settings['user_limit'][ $user->ID ] ) ? esc_attr( $settings['user_limit'][ $user->ID ] ) : ''; ?>">
						<em class="help-block"><?php esc_html_e( 'Default: -1 (i.e. unlimited)', 'wpsite-limit-posts' ) ?></em>
					</div>
				</div>
				<?php } ?>

				<?php wp_nonce_field( 'wpsite_limit_posts_admin_settings' ) ?>

				<p class="submit"><input type="submit" name="submit" id="submit" class="btn btn-info" value="<?php esc_html_e( 'Save Changes', 'wpsite-limit-posts' ) ?>"></p>

			</form>

		</div>

		<?php require_once( 'sidebar.php' ) ?>

	</div>

	<?php require_once( 'footer.php' ) ?>

</div>
