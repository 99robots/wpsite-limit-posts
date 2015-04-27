/*
 * Created by: Kyle Benk
 * kylebenkapps.com
 *
 */

jQuery(document).ready(function($) {

	$("input:radio[name=wpsite_limit_posts_settings_all_users]").change(function(){
		if ($(this).val() == 'capability') {
			$(".wpsite_limit_posts_users").hide();
			$(".wpsite_limit_posts_post_type").hide();
			$(".wpsite_limit_posts_roles").show();
		} else if ($(this).val() == 'user'){
			$(".wpsite_limit_posts_users").show();
			$(".wpsite_limit_posts_post_type").hide();
			$(".wpsite_limit_posts_roles").hide();
		}
		else {
			$(".wpsite_limit_posts_users").hide();
			$(".wpsite_limit_posts_roles").hide();
			$(".wpsite_limit_posts_post_type").show();
		}
	});

	if ($("input:radio[name=wpsite_limit_posts_settings_all_users]:checked").val() == 'capability') {
		$(".wpsite_limit_posts_users").hide();
		$(".wpsite_limit_posts_roles").show();
		$(".wpsite_limit_posts_post_type").hide();
	} else if ($("input:radio[name=wpsite_limit_posts_settings_all_users]:checked").val() == 'user') {
		$(".wpsite_limit_posts_users").show();
		$(".wpsite_limit_posts_roles").hide();
		$(".wpsite_limit_posts_post_type").hide();
	}
	else {
		$(".wpsite_limit_posts_users").hide();
		$(".wpsite_limit_posts_roles").hide();
		$(".wpsite_limit_posts_post_type").show();
	}
});