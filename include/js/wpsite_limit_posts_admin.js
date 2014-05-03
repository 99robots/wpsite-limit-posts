/*
 * Created by: Kyle Benk
 * kylebenkapps.com
 *
 */
 
jQuery(document).ready(function($) {

	$("#wpsite_limit_posts_settings_all_users").change(function(){
		if ($(this).prop('checked')) {
			$(".wpsite_limit_posts_users").hide();
			$(".wpsite_limit_posts_roles").show();
		} else {
			$(".wpsite_limit_posts_users").show();
			$(".wpsite_limit_posts_roles").hide();
		}
	});

	if ($("#wpsite_limit_posts_settings_all_users").prop('checked')) {
		$(".wpsite_limit_posts_users").hide();
		$(".wpsite_limit_posts_roles").show();
	} else {
		$(".wpsite_limit_posts_users").show();
		$(".wpsite_limit_posts_roles").hide();
	}
	
});