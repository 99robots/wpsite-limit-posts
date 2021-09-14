=== Limit Posts by 99 Robots ===
Contributors: 99robots, charliepatel, DraftPress
Donate link:
Tags: limit posts, limit number of posts, limit author posts, custom post limits, post creation limits, cpt limits, limit pages, limit user, limits, post limit, posts per user, user post limit, page limit, publish limit
Requires at least: 4.9
Tested up to: 5.8.1
Stable tag: 2.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Limit the number of posts or custom post types that can be published based on role (i.e, author) or user.

== Description ==

Limit the number of posts that your users(non-admins with edit post capability) can publish. This plugin by https://99robots.com allows you to limit the number of posts that can be published based on role or user.

It allows for the following:

* Limit number of posts by role (non-admins with edit post capability)
* Limit number of posts by user (i.e., John Doe can be limited to 5 posts)
* Posts submitted after user has exceeded their limits will have a new post status of 'Limited'
* Limit number of posts by post type (coming soon...)

Also please check out our other [plugins](https://99robots.com/products/?utm_source=wprepo&utm_medium=content-resharer&utm_campaign=desc) :)

== Installation ==

You can install the Limit Posts plugin from your WordPress Dashboard or manually via FTP. For more details, visit http://99robots.com

= From WordPress Dashboard =

# Navigate to 'Plugins -> Add New' from your WordPress dashboard.
# Search for `99 Robots Limit Posts` and install it.
# Activate the plugin from Plugins menu.
# Configure the plugin's settings
# Add any widget to your widget zone from Appearance -> Widgets and configure the widget options.

= Manual Installation =

# Download the plugin file: `wpsite-limit-posts.zip`
# Unzip the file
# Upload the`wpsite-limit-posts` folder to your `/wp-content/plugins` directory (do not rename the folder)
# Activate the plugin from Plugins menu.
# Configure the plugin's settings  (see instructions for shortcode and php template tags)
# Add any widget to your widget zone from 'Appearance -> Widgets' and configure the widget options.

== Frequently Asked Questions ==

= What happens when a user has exceeded his / her limit? =
Rather than force the user to delete their post, such posts will be submitted for review using a new post status labeled 'Limited.' An Admin can then determine if the post should be published. This ensures no author's work is disregarded simply because they exceeded their post limit.

== Screenshots ==

1. Limit Posts Settings

== Changelog ==


= 2.1.2 = 2021-09-14
* Made compatible with WordPress 5.8.1
* Fix listing of all individual users

= 2.1.1 = 2021-05-17
* Made compatible with WordPress 5.7.2
* FIX - Updated Limited role, block all additional posts.
* FIX - Changed jQuery.fn.change() event shorthand.

= 2.1.0 = 2019-09-10
* Made compatible with WordPress 5.2.3
* NEW - Ability to limit posts for all roles with edit post capability except for Administrators.

= 2.0.2 = 2018-06-19
* Made compatible with WordPress 4.9.6

= 2.0.1 = 2017-06-11
* Made compatible with WordPress 4.8

= 2.0.0 = 2015-10-15
* ADDED: New settings page style

= 1.0.4 =
* Re-branded to 99 Robots

= 1.0.3 =
* Minor Updates

= 1.0.2 =
* Compatible with WordPress 4.1

= 1.0.1 =
* Supports WordPress 4.0

= 1.0 =
* Initial release
