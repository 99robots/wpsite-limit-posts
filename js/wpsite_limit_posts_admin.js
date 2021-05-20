jQuery(function ($) {
  
	$("input:radio[name=wpsite_limit_posts_settings_all_users]").on("change", function () {
			if ($(this).val() == "capability") {
				$(".wpsite_limit_posts_users").hide();
				$(".wpsite_limit_posts_roles").show();
			} else {
				$(".wpsite_limit_posts_users").show();
				$(".wpsite_limit_posts_roles").hide();
			}
		}
	);

	if ($("input:radio[name=wpsite_limit_posts_settings_all_users]:checked").val() == "capability") {
		$(".wpsite_limit_posts_users").hide();
		$(".wpsite_limit_posts_roles").show();
	} else {
		$(".wpsite_limit_posts_users").show();
		$(".wpsite_limit_posts_roles").hide();
	}
});
