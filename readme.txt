=== Bainternet User Ranks ===
Contributors: bainternet,adsbycb
Donate link: http://en.bainternet.info/donations 
Tags: user ranking,user titles, post and comments ranking, forum like ranking, user karma, comments rank, posts ranks, article ranks, user publish ranks, user publish rank.
Requires at least: 2.9.2
Tested up to: 4.7.0
Stable tag: 1.5.2

Create and display user rank titles based on there post count, comment count or both.

== Description ==

Create and display user rank titles based on there post count, comment count or both.

This is aimed at multi Author,User blogs which you can create rank levels in your blog based on author post count, comment count or both. Its ranking system similar to a forum.

**features**

*	Add as many Titles as you want and the minimum point to reach that Title.
*	Set the point count for each post.
*	Set the point count for each comment.
*	Display Title, points or both.
*	Insert automagicaly.(NEW)
*	User rank ShortCode.(NEW)
*	Get top Ranked ShortCode.(NEW)
*	Get top Ranked Template Tag.(NEW)

 

any feedback or suggestions are welcome.

check out my [other plugins][1]

[1]: http://en.bainternet.info/category/plugins

== Installation ==

Simple steps:  
  
1. Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation.  
  
2. Then activate the Plugin from Plugins page.  
  
3. Go to Plugins option panel named "Bainternet User Ranks" under options.  
  
4. Setup your Titles and Points levels and maybe other settings.  
  
5. save!.
== Frequently Asked Questions ==

= How Can I Use the ShorCode? =

Simple just add `[user_rank]` in your post content and if you want to get the rank of another user (default is post author) then just set user_id to the id of the user who's rank you want eg: `[user_rank user_id="23"]`

= How Can I Use the Top ranked ShorCode? =

Once again very simple just add `[user_rank_top]` which will get you a list of top 5 ranked users, you can change the number , item wrapper and container eg: `[user_rank_top number="20" container="ol" item_wrapper="li"]`

= How Can I Style, Design the shortcodes output? =

Both Shortcodes use a simple templating system which takes tokens and replaces them with the user data eg:
`[user_rank]
<div class="user_ranks">
	<div class="user_name">[user-firstname] [user-lastname]</div>
	<div class="user_title"><strong>[title]</strong></div>
	<div class="user_points"><small>[points]</small></div>
</div>
[/user_rank]`

Just make sure you enter the template after the shortcode tag and add aclosing tag.

= Nice, what are the shortcode template tokens that I can Use? =

*	[title] - prints the user's rank title
*	[points] - prints the user's points
*	[user_login] - prints the user's login
*	[user_nicename] - prints the user's nicename
*	[user_email] - prints the user's email
*	[user_url] - prints the user's URL
*	[user_firstname] - prints the user's First name
*	[user_lastname] - prints the user's last name
*	[user_description] - prints the user's description/bio

= How Can I Use the Top ranked template tag? =


`<?php $baur_plugin = new baur_Plugin();
$top_users = $baur_plugin->get_top_users($number);
foreach($top_users as $u){
	$user_info = get_userdata($u['user_id']);
	echo "User: ".$user_info->user_login. " Points: " . $u['points]. " Title: ". $u['title']. "<br/>";
}?>`


= Nothing is happening, whats Wrong? =

Nothing you just need to call the plugin in your theme file something like this:

`<?php $baur_plugin = new baur_Plugin();
$user_rank = $baur_plugin->ba_get_user_points($user_id);
echo "Points: " . $user_rank;?>`

*And you must set $user_id*

= Why do i get just the points? =

You need to pass another parameter to the 'ba_get_user_points' function (Boolean , default false) to get an array that contains both Title and Points.
`<?php $baur_plugin = new baur_Plugin();
$user_rank = $baur_plugin->ba_get_user_points($user_id,true);
echo "title: ". $user_rank['title'] . "Points: " . $user_rank['points'];?>`
*Once again you must set $user_id*

= Hot can i get top ranked users? =

use `get_top_users` method with the number of top users you want , for example to get the top 5 use:
`<?php $baur_plugin = new baur_Plugin();
$top_users = $baur_plugin->get_top_users(5);
foreach ($top_users as $user){
	$user_info = get_userdata($user['user_id']);
	echo $user_info->user_login . " title: ". $user_rank['title'] . "Points: " . $user_rank['points'];
}
?>`

== Screenshots ==
1. admin panel of Bainternet User Ranks

== Changelog ==
1.5.2 Fixed `Missing argument 2 for wpdb::prepare(),`

1.5.1 Fixed activation error.

1.5.0 Fixed Warring after comment deletion.

1.4.0 Fixed minor bug

1.3.1 Fixed unexpected output on activation (due to incorrect encoding).

1.3.0 added Top ranked template tag, shortcodes, auto insert for posts.

1.2.0 Added top ranked user feature.

1.1.0 Fixed unexpected output on activation (due to incorrect encoding).

1.0.0 Big changes: the plugin now saves the user rank and title in the usermeta table so it makes less calls and calculations on the fly making it much faster.

0.2.2 quick bug fix plugin brake on user comment count.

0.2.1 remake from the ground up.  
  
0.1 inital release.